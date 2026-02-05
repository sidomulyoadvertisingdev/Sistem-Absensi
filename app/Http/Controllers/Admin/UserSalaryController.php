<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSalary;
use App\Models\Absensi;
use App\Models\Lembur;
use App\Models\SalaryDeductionRule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserSalaryController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->bulan ?? now()->format('Y-m');

        $users = User::with('salary')
            ->where('role', User::ROLE_KARYAWAN)
            ->orderBy('name')
            ->get();

        return view('admin.gaji.index', compact('users', 'bulan'));
    }

    public function edit(User $user)
    {
        abort_if(!$user->isKaryawan(), 403);

        return view('admin.gaji.edit', compact('user'));
    }

    /*
    ===============================================
    UPDATE MASTER GAJI USER (TIDAK DIUBAH)
    ===============================================
    */
    public function update(Request $request, User $user)
    {
        abort_if(!$user->isKaryawan(), 403);

        $request->validate([
            'gaji_pokok'          => 'required|numeric|min:0',
            'gaji_harian'         => 'nullable|numeric|min:0',

            'tunjangan_umum'      => 'nullable|numeric|min:0',
            'tunjangan_transport' => 'nullable|numeric|min:0',
            'tunjangan_thr'       => 'nullable|numeric|min:0',
            'tunjangan_kesehatan' => 'nullable|numeric|min:0',

            'lembur_per_jam'      => 'nullable|numeric|min:0',

            'gaji_harian_mode'    => 'nullable|in:manual,pokok,pokok_plus_tunjangan',
        ]);

        $hariKerjaStandar = 26;

        $gajiPokok = (float) $request->gaji_pokok;

        $tunjanganTotal =
            (float) ($request->tunjangan_umum ?? 0) +
            (float) ($request->tunjangan_transport ?? 0) +
            (float) ($request->tunjangan_thr ?? 0) +
            (float) ($request->tunjangan_kesehatan ?? 0);

        $mode = $request->gaji_harian_mode ?? 'manual';

        if ($mode === 'pokok') {

            $gajiHarian = $gajiPokok / $hariKerjaStandar;

        } elseif ($mode === 'pokok_plus_tunjangan') {

            $gajiHarian = ($gajiPokok + $tunjanganTotal) / $hariKerjaStandar;

        } else {

            $gajiHarian = (float) ($request->gaji_harian ?? 0);
        }

        UserSalary::updateOrCreate(
            ['user_id' => $user->id],
            [

                'gaji_pokok'  => $gajiPokok,
                'gaji_harian' => round($gajiHarian),

                'gaji_harian_mode'     => $mode,
                'auto_generate_harian' => $mode !== 'manual',

                'tunjangan_umum'      => (float) ($request->tunjangan_umum ?? 0),
                'tunjangan_transport' => (float) ($request->tunjangan_transport ?? 0),
                'tunjangan_thr'       => (float) ($request->tunjangan_thr ?? 0),
                'tunjangan_kesehatan' => (float) ($request->tunjangan_kesehatan ?? 0),

                'lembur_per_jam' => (float) ($request->lembur_per_jam ?? 0),

                'include_tunjangan' => $request->boolean('include_tunjangan'),
                'aktif'             => $request->boolean('aktif'),
            ]
        );

        return redirect()
            ->route('admin.gaji')
            ->with('success', 'Master gaji berhasil disimpan.');
    }

    /*
    ===============================================
    SLIP GAJI — SYNC DENGAN LAPORAN
    ===============================================
    */
    public function slipPdf(Request $request, User $user)
    {
        abort_if(!$user->isKaryawan(), 403);

        $salary = $user->salary;
        abort_if(!$salary || !$salary->aktif, 404);

        $bulanYm = $request->bulan ?? now()->format('Y-m');
        $date = Carbon::createFromFormat('Y-m', $bulanYm);

        $bulan = $date->month;
        $tahun = $date->year;
        $periode = $date->translatedFormat('F Y');

        $hariKerjaStandar = 26;

        /*
        ================= ABSENSI
        */
        $absensis = Absensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        $hariHadir = $absensis->where('status', 'hadir')->count();
        $hariTelat = $absensis->where('status', 'terlambat')->count();

        $presensi = $hariHadir + $hariTelat;

        $hariNormal   = min($presensi, $hariKerjaStandar);
        $hariTambahan = max($presensi - $hariKerjaStandar, 0);

        $menitTerlambat = (int)
            $absensis->where('status', 'terlambat')
                ->sum('menit_terlambat');

        /*
        ================= GAJI (SAMA LAPORAN)
        */
        $gajiPerHari = (float) $salary->gaji_harian;

        $tunjangan = [
            $salary->tunjangan_umum,
            $salary->tunjangan_transport,
            $salary->tunjangan_thr,
            $salary->tunjangan_kesehatan,
        ];

        $totalTunjangan = array_sum($tunjangan);

        $nilaiHariTambahan = $gajiPerHari;

        if ($salary->include_tunjangan) {
            $nilaiHariTambahan += ($totalTunjangan / $hariKerjaStandar);
        }

        $gajiNormal   = $gajiPerHari * $hariNormal;
        $gajiTambahan = $nilaiHariTambahan * $hariTambahan;

        /*
        ================= LEMBUR
        */
        $totalJamLembur = Lembur::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->where('status', 'approved')
            ->get()
            ->sum(fn ($l) =>
                Carbon::parse($l->jam_mulai)
                    ->diffInMinutes(Carbon::parse($l->jam_selesai)) / 60
            );

        $totalLembur = $totalJamLembur * (float) $salary->lembur_per_jam;

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
        ================= SALARY KOTOR
        */
        $salaryKotor =
            $gajiNormal +
            $gajiTambahan +
            $totalLembur +
            $totalBonusJob;

        if ($salary->include_tunjangan) {
            $salaryKotor += $totalTunjangan;
        }

        /*
        ================= POTONGAN RULE
        */
        $rules = SalaryDeductionRule::where('aktif', true)->get();

        $totalPotongan = 0;
        $potonganTelatNominal = 0;

        foreach ($rules as $rule) {

            if (!$rule->isApplicableForPenempatan($user->penempatan)) continue;

            $base = match ($rule->base_source) {
                'gaji_pokok' => (float) $salary->gaji_pokok,
                'tunjangan'  => $totalTunjangan,
                default      => $salaryKotor,
            };

            if ($base <= 0) continue;

            if ($rule->condition_type === 'terlambat') {

                if ($hariTelat < ($rule->condition_value ?? 1)) continue;

                if ($rule->max_minutes &&
                    $menitTerlambat < $rule->max_minutes) continue;

                $nilai = $rule->calculate($base);

                $potonganTelatNominal += $nilai;
                $totalPotongan += $nilai;
            }
        }

        /*
        ================= FINAL
        */
        $totalGaji = max($salaryKotor - $totalPotongan, 0);

        return Pdf::loadView('admin.gaji.slip-pdf', compact(
            'user',
            'salary',
            'periode',

            'hariHadir',
            'hariTelat',
            'menitTerlambat',

            'gajiPerHari',
            'gajiNormal',
            'gajiTambahan',

            'totalJamLembur',
            'totalLembur',

            'jobBonus',
            'totalBonusJob',

            'totalTunjangan',

            'potonganTelatNominal',
            'totalPotongan',

            'salaryKotor',
            'totalGaji'
        ))->stream(
            'Slip-Gaji-' . $user->name . '-' . $date->format('m-Y') . '.pdf'
        );
    }
}
