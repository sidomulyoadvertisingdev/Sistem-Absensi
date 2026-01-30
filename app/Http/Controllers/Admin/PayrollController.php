<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Lembur;
use App\Models\SalaryDeductionRule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class PayrollController extends Controller
{
    /**
     * =================================================
     * HITUNG GAJI (SUMBER DATA TUNGGAL)
     * Dipakai oleh: show(), pay(), slip pdf
     * =================================================
     */
    private function hitungGaji(User $user, Carbon $date): array
    {
        $salary = $user->salary;

        $bulan = $date->month;
        $tahun = $date->year;

        /* ================= ABSENSI ================= */
        $absensis = Absensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        $hariHadir      = $absensis->where('status', 'hadir')->count();
        $hariTelat      = $absensis->where('status', 'terlambat')->count();
        $menitTerlambat = $absensis->where('status', 'terlambat')->sum('menit_terlambat');

        /* ================= LEMBUR ================= */
        $totalJamLembur = Lembur::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get()
            ->sum(fn ($l) =>
                Carbon::parse($l->jam_mulai)
                    ->diffInMinutes(Carbon::parse($l->jam_selesai)) / 60
            );

        $totalLembur = $totalJamLembur * ($salary->lembur_per_jam ?? 0);

        /* ================= BONUS JOB ================= */
        $jobBonus = DB::table('job_todo_user')
            ->join('job_todos', 'job_todos.id', '=', 'job_todo_user.job_todo_id')
            ->where('job_todo_user.user_id', $user->id)
            ->where('job_todo_user.status', 'completed')
            ->whereMonth('job_todo_user.completed_at', $bulan)
            ->whereYear('job_todo_user.completed_at', $tahun)
            ->select('job_todos.title', 'job_todos.bonus')
            ->get();

        $totalBonusJob = $jobBonus->sum('bonus');

        /* ================= GAJI ================= */
        $hariKerjaStandar = 26;
        $presensi         = $hariHadir + $hariTelat;

        $gajiPerHari  = $salary->gaji_pokok / $hariKerjaStandar;
        $gajiPokokFix = $gajiPerHari * $presensi;

        $salaryKotor =
            $gajiPokokFix +
            $salary->tunjangan_umum +
            $salary->tunjangan_transport +
            $salary->tunjangan_thr +
            $salary->tunjangan_kesehatan +
            $totalLembur +
            $totalBonusJob;

        /* ================= POTONGAN ================= */
        $rules = SalaryDeductionRule::where('aktif', true)->get();

        $totalPotongan        = 0;
        $potonganTelatNominal = 0; // 🔥 WAJIB ADA

        foreach ($rules as $rule) {

            if (!$rule->isApplicableForPenempatan($user->penempatan)) {
                continue;
            }

            $kena = match ($rule->condition_type) {
                'terlambat' => $hariTelat >= $rule->condition_value,
                default     => true,
            };

            if (!$kena) continue;

            $basis = $rule->condition_type === 'terlambat'
                ? $salary->gaji_pokok
                : $salaryKotor;

            $nilai = $rule->calculate($basis);

            // 🔥 SIMPAN POTONGAN KHUSUS TELAT
            if ($rule->condition_type === 'terlambat') {
                $potonganTelatNominal += $nilai;
            }

            $totalPotongan += $nilai;
        }

        $totalGaji = max($salaryKotor - $totalPotongan, 0);

        return compact(
            'absensis',
            'jobBonus',
            'hariHadir',
            'hariTelat',
            'menitTerlambat',
            'totalJamLembur',
            'totalLembur',
            'totalBonusJob',
            'gajiPerHari',
            'gajiPokokFix',
            'salaryKotor',
            'potonganTelatNominal', // ✅ FIX PDF
            'totalPotongan',
            'totalGaji'
        );
    }

    /**
     * =================================================
     * DETAIL GAJI BULANAN
     * =================================================
     */
    public function show(Request $request, User $user)
    {
        abort_if(!$user->isKaryawan(), 403);

        $salary = $user->salary;
        abort_if(!$salary || !$salary->aktif, 404);

        $bulanYm = $request->bulan ?? now()->format('Y-m');
        $date    = Carbon::createFromFormat('Y-m', $bulanYm);
        $periode = $date->translatedFormat('F Y');

        $isPaid = $salary->is_paid && $salary->payroll_period === $bulanYm;

        $data = $this->hitungGaji($user, $date);

        return view('admin.gaji.detail', array_merge($data, [
            'user'    => $user,
            'salary'  => $salary,
            'isPaid'  => $isPaid,
            'date'    => $date,
            'periode' => $periode,
            'bulan'   => $bulanYm,
        ]));
    }

    /**
     * =================================================
     * BAYAR GAJI BULANAN
     * =================================================
     */
    public function pay(Request $request, User $user)
    {
        abort_if(!$user->isKaryawan(), 403);

        $salary = $user->salary;
        abort_if(!$salary || !$salary->aktif, 404);

        $bulanYm = $request->bulan ?? now()->format('Y-m');

        if ($salary->is_paid && $salary->payroll_period === $bulanYm) {
            return back()->with('error', 'Gaji bulan ini sudah dibayar.');
        }

        $date    = Carbon::createFromFormat('Y-m', $bulanYm);
        $periode = $date->translatedFormat('F Y');

        $data = $this->hitungGaji($user, $date);

        DB::transaction(function () use ($user, $salary, $date, $bulanYm) {

            $salary->update([
                'is_paid'        => true,
                'payroll_period' => $bulanYm,
                'paid_at'        => now(),
                'paid_by'        => auth()->id(),
            ]);

            Absensi::where('user_id', $user->id)
                ->whereMonth('tanggal', $date->month)
                ->whereYear('tanggal', $date->year)
                ->update(['locked' => true]);
        });

        $pdf = Pdf::loadView('admin.gaji.slip-pdf', array_merge($data, [
            'user'    => $user,
            'salary'  => $salary,
            'periode' => $periode,
        ]));

        Mail::send('emails.slip-gaji', compact('user', 'periode'), function ($message) use ($user, $pdf, $periode) {
            $message->to($user->email)
                ->subject("Slip Gaji {$periode}")
                ->attachData($pdf->output(), "Slip-Gaji-{$periode}.pdf");
        });

        return redirect()
            ->route('admin.gaji')
            ->with('success', 'Gaji berhasil dibayar dan slip dikirim.');
    }
}
