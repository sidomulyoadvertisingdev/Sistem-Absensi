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
    /*
    |--------------------------------------------------------------------------
    | CORE PAYROLL ENGINE — IDENTIK LAPORAN
    |--------------------------------------------------------------------------
    */

    private function hitungGaji(User $user, Carbon $date): array
    {
        $salary = $user->salary;

        $bulan = $date->month;
        $tahun = $date->year;
        $hariKerjaStandar = 26;

        /*
        ================= ABSENSI (SAMA LAPORAN)
        */

        $absensis = Absensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        $hariHadir = $absensis->where('status', 'hadir')->count();
        $hariTelat = $absensis->where('status', 'terlambat')->count();

        $presensi = $hariHadir + $hariTelat;

        $hariNormal     = min($presensi, $hariKerjaStandar);
        $hariTambahan   = max($presensi - $hariKerjaStandar, 0);
        $offDay         = max($hariKerjaStandar - $hariNormal, 0);

        $menitTerlambat = (int) $absensis
            ->where('status', 'terlambat')
            ->sum('menit_terlambat');

        /*
        ================= TUNJANGAN (SAMA LAPORAN)
        */

        $tunjanganArray = [
            'tunjangan_umum'      => (float) ($salary->tunjangan_umum ?? 0),
            'tunjangan_transport' => (float) ($salary->tunjangan_transport ?? 0),
            'tunjangan_thr'       => (float) ($salary->tunjangan_thr ?? 0),
            'tunjangan_kesehatan' => (float) ($salary->tunjangan_kesehatan ?? 0),
        ];

        $totalTunjanganMaster = array_sum($tunjanganArray);

        $tunjanganPayroll = $salary->include_tunjangan
            ? $totalTunjanganMaster
            : 0;

        // kompatibilitas blade lama
        $totalTunjangan = $tunjanganPayroll;

        /*
        ================= GAJI HARIAN (SAMA LAPORAN)
        */

        $gajiPerHari = (float) ($salary->gaji_harian ?? 0);

        $nilaiHariTambahan = $gajiPerHari;

        if ($salary->include_tunjangan) {
            $nilaiHariTambahan += ($totalTunjanganMaster / $hariKerjaStandar);
        }

        $gajiNormal   = $gajiPerHari * $hariNormal;
        $gajiTambahan = $nilaiHariTambahan * $hariTambahan;

        // kompatibilitas lama
        $gajiBruto = $gajiNormal + $gajiTambahan;

        /*
        ================= LEMBUR
        */

        $totalJamLembur = Lembur::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get()
            ->sum(fn ($l) =>
                Carbon::parse($l->jam_mulai)
                    ->diffInMinutes(Carbon::parse($l->jam_selesai)) / 60
            );

        $uangLembur = $totalJamLembur * (float) ($salary->lembur_per_jam ?? 0);

        /*
        ================= BONUS JOB
        */

        $jobBonus = DB::table('job_todo_user')
            ->join('job_todos', 'job_todos.id', '=', 'job_todo_user.job_todo_id')
            ->where('job_todo_user.user_id', $user->id)
            ->where('job_todo_user.status', 'completed')
            ->whereMonth('job_todo_user.completed_at', $bulan)
            ->whereYear('job_todo_user.completed_at', $tahun)
            ->select('job_todos.title', 'job_todos.bonus')
            ->get();

        $totalBonusJob = $jobBonus->sum('bonus');

        /*
        ================= SALARY KOTOR (IDENTIK LAPORAN)
        */

        $salaryKotor =
            $gajiNormal +
            $gajiTambahan +
            $uangLembur +
            $totalBonusJob +
            $tunjanganPayroll;

        /*
        ================= RULE ENGINE POTONGAN
        */

        $rules = SalaryDeductionRule::where('aktif', true)->get();

        $totalPotongan = 0;
        $potonganTelatNominal = 0;

        foreach ($rules as $rule) {

            if (!$rule->isApplicableForPenempatan($user->penempatan)) continue;

            $base = match ($rule->base_source) {

                'gaji_pokok' =>
                    (float) ($salary->gaji_pokok ?? 0),

                'tunjangan' =>
                    collect($rule->tunjangan_items ?? [])
                        ->sum(fn ($item) => $tunjanganArray[$item] ?? 0),

                default =>
                    $salaryKotor,
            };

            if ($base <= 0) continue;

            if ($rule->condition_type === 'terlambat') {

                $trigger = $rule->condition_value ?? 1;

                if ($hariTelat < $trigger) continue;

                if ($rule->max_minutes &&
                    $menitTerlambat < $rule->max_minutes) continue;

                $nilai = $rule->calculate($base);

                $potonganTelatNominal += $nilai;
                $totalPotongan += $nilai;
            }

            if ($rule->condition_type === 'off_day') {

                if ($offDay <= 0) continue;

                $nilai = $rule->calculate($base);
                $totalPotongan += $nilai;
            }
        }

        /*
        ================= FINAL
        */

        $totalGaji = max($salaryKotor - $totalPotongan, 0);

        return compact(
            'absensis',
            'jobBonus',

            'hariHadir',
            'hariTelat',
            'hariNormal',
            'hariTambahan',
            'offDay',
            'menitTerlambat',

            'gajiPerHari',
            'gajiNormal',
            'gajiTambahan',
            'gajiBruto',

            'totalJamLembur',
            'uangLembur',

            'totalBonusJob',

            'totalTunjanganMaster',
            'tunjanganPayroll',
            'totalTunjangan',

            'salaryKotor',

            'potonganTelatNominal',
            'totalPotongan',

            'totalGaji'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL GAJI
    |--------------------------------------------------------------------------
    */

    public function show(Request $request, User $user)
    {
        abort_if(!$user->isKaryawan(), 403);

        $salary = $user->salary;
        abort_if(!$salary || !$salary->aktif, 404);

        $bulanYm = $request->bulan ?? now()->format('Y-m');
        $date    = Carbon::createFromFormat('Y-m', $bulanYm);
        $periode = $date->translatedFormat('F Y');

        $isPaid = $salary->isPaidFor($bulanYm);

        $data = $this->hitungGaji($user, $date);

        return view('admin.gaji.detail', array_merge($data, [
            'user'    => $user,
            'salary'  => $salary,
            'periode' => $periode,
            'bulan'   => $bulanYm,
            'isPaid'  => $isPaid,
        ]));
    }

    /*
    |--------------------------------------------------------------------------
    | BAYAR GAJI + EMAIL SLIP
    |--------------------------------------------------------------------------
    */

    public function pay(Request $request, User $user)
    {
        abort_if(!$user->isKaryawan(), 403);

        $salary = $user->salary;
        abort_if(!$salary || !$salary->aktif, 404);

        $bulanYm = $request->bulan ?? now()->format('Y-m');

        if ($salary->isPaidFor($bulanYm)) {
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

        Mail::send(
            'emails.slip-gaji',
            compact('user', 'periode'),
            function ($m) use ($user, $pdf, $periode) {
                $m->to($user->email)
                    ->subject("Slip Gaji {$periode}")
                    ->attachData($pdf->output(), "Slip-Gaji-{$periode}.pdf");
            }
        );

        return redirect()
            ->route('admin.gaji')
            ->with('success', 'Gaji berhasil dibayar & slip dikirim.');
    }
}
