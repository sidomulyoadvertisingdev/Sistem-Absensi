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
     * HITUNG GAJI (SUMBER DATA TUNGGAL – IDENTIK LAPORAN)
     * =================================================
     */
    private function hitungGaji(User $user, Carbon $date): array
    {
        $salary = $user->salary;

        $bulan = $date->month;
        $tahun = $date->year;
        $hariKerjaStandar = 26;

        /* ================= ABSENSI ================= */
        $absensis = Absensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        $hariHadir = $absensis->where('status', 'hadir')->count();
        $hariTelat = $absensis->where('status', 'terlambat')->count();
        $presensi  = $hariHadir + $hariTelat;
        $offDay    = max($hariKerjaStandar - $presensi, 0);

        $menitTerlambat = $absensis
            ->where('status', 'terlambat')
            ->sum('menit_terlambat');

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

        $uangLembur = $totalJamLembur * ($salary->lembur_per_jam ?? 0);

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
        $gajiPokokMaster = $salary->gaji_pokok ?? 0;
        $gajiPerHari     = $gajiPokokMaster / $hariKerjaStandar;
        $gajiProrata     = $gajiPerHari * $presensi;

        $tunjangan = [
            'tunjangan_umum'       => $salary->tunjangan_umum ?? 0,
            'tunjangan_transport'  => $salary->tunjangan_transport ?? 0,
            'tunjangan_thr'        => $salary->tunjangan_thr ?? 0,
            'tunjangan_kesehatan'  => $salary->tunjangan_kesehatan ?? 0,
        ];

        $totalTunjangan = array_sum($tunjangan);

        /* ================= SALARY KOTOR (MASTER) ================= */
        $salaryKotor =
            $gajiPokokMaster +
            $totalTunjangan +
            $uangLembur +
            $totalBonusJob;

        /* ================= POTONGAN (CLONE LAPORAN) ================= */
        $rules = SalaryDeductionRule::where('aktif', true)->get();

        $totalPotongan = 0;
        $potonganTelatNominal = 0;

        foreach ($rules as $rule) {

            if (!$rule->isApplicableForPenempatan($user->penempatan)) {
                continue;
            }

            $kena = match ($rule->condition_type) {
                'terlambat'   => $hariTelat >= ($rule->condition_value ?? 1),
                'off_day'     => $offDay >= ($rule->condition_value ?? 1),
                'pelanggaran' => true,
                default       => false,
            };

            if (!$kena) continue;

            if ($rule->isFromGajiPokok()) {
                $nilai = $rule->calculate($gajiPokokMaster);
            }
            elseif ($rule->isFromTunjangan()) {
                $nilai = $rule->calculateFromTunjangan($tunjangan);
            }
            else {
                $nilai = $rule->calculate(
                    max($salaryKotor - $totalPotongan, 0)
                );
            }

            if ($rule->condition_type === 'terlambat') {
                $potonganTelatNominal += $nilai;
            }

            $totalPotongan += $nilai;
        }

        /* ================= GAJI DITERIMA ================= */
        $totalGaji =
            $gajiProrata +
            $totalTunjangan +
            $uangLembur +
            $totalBonusJob -
            $totalPotongan;

        $totalGaji = max($totalGaji, 0);

        return compact(
            'absensis',
            'jobBonus',
            'hariHadir',
            'hariTelat',
            'offDay',
            'menitTerlambat',
            'totalJamLembur',
            'uangLembur',
            'totalBonusJob',
            'gajiPerHari',
            'gajiProrata',
            'salaryKotor',
            'potonganTelatNominal',
            'totalPotongan',
            'totalGaji'
        );
    }

    /**
     * =================================================
     * DETAIL GAJI
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
            'periode' => $periode,
            'bulan'   => $bulanYm,
        ]));
    }

    /**
     * =================================================
     * BAYAR GAJI
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
