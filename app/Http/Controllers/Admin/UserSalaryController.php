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
    /**
     * =================================================
     * INDEX GAJI KARYAWAN
     * =================================================
     */
    public function index(Request $request)
    {
        $bulan = $request->bulan ?? now()->format('Y-m');

        $users = User::with('salary')
            ->where('role', User::ROLE_KARYAWAN)
            ->orderBy('name')
            ->get();

        return view('admin.gaji.index', compact('users', 'bulan'));
    }

    /**
     * =================================================
     * FORM EDIT GAJI
     * =================================================
     */
    public function edit(User $user)
    {
        abort_if(!$user->isKaryawan(), 403);

        return view('admin.gaji.edit', compact('user'));
    }

    /**
     * =================================================
     * SIMPAN / UPDATE MASTER GAJI
     * =================================================
     */
    public function update(Request $request, User $user)
    {
        abort_if(!$user->isKaryawan(), 403);

        $request->validate([
            'gaji_pokok'          => 'required|numeric|min:0',
            'tunjangan_umum'      => 'nullable|numeric|min:0',
            'tunjangan_transport' => 'nullable|numeric|min:0',
            'tunjangan_thr'       => 'nullable|numeric|min:0',
            'tunjangan_kesehatan' => 'nullable|numeric|min:0',
            'lembur_per_jam'      => 'nullable|numeric|min:0',
        ]);

        UserSalary::updateOrCreate(
            ['user_id' => $user->id],
            [
                'gaji_pokok'          => $request->gaji_pokok,
                'tunjangan_umum'      => $request->tunjangan_umum ?? 0,
                'tunjangan_transport' => $request->tunjangan_transport ?? 0,
                'tunjangan_thr'       => $request->tunjangan_thr ?? 0,
                'tunjangan_kesehatan' => $request->tunjangan_kesehatan ?? 0,
                'lembur_per_jam'      => $request->lembur_per_jam ?? 0,
                'aktif'               => $request->boolean('aktif'),
            ]
        );

        return redirect()
            ->route('admin.gaji')
            ->with('success', 'Data gaji berhasil disimpan.');
    }

    /**
     * =================================================
     * SLIP GAJI PDF
     * (DIPAKAI OLEH INDEX & DETAIL)
     * =================================================
     */
    public function slipPdf(Request $request, User $user)
    {
        abort_if(!$user->isKaryawan(), 403);

        $salary = $user->salary;
        abort_if(!$salary || !$salary->aktif, 404);

        $bulanYm = $request->bulan ?? now()->format('Y-m');
        $date    = Carbon::createFromFormat('Y-m', $bulanYm);
        $bulan   = $date->month;
        $tahun   = $date->year;
        $periode = $date->translatedFormat('F Y');

        $hariKerjaStandar = 26;

        /* ================= ABSENSI ================= */
        $absensis = Absensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        $hariHadir      = $absensis->where('status', 'hadir')->count();
        $hariTelat      = $absensis->where('status', 'terlambat')->count();
        $menitTerlambat = $absensis
            ->where('status', 'terlambat')
            ->sum('menit_terlambat');

        $presensi = $hariHadir + $hariTelat;

        /* ================= GAJI POKOK ================= */
        $gajiPerHari  = $salary->gaji_pokok / $hariKerjaStandar;
        $gajiPokokFix = $gajiPerHari * $presensi;

        /* ================= LEMBUR ================= */
        $totalJamLembur = Lembur::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->where('status', 'approved')
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

        /* ================= GAJI KOTOR ================= */
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
        $potonganTelatNominal = 0;

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

            if ($rule->condition_type === 'terlambat') {
                $potonganTelatNominal += $nilai;
            }

            $totalPotongan += $nilai;
        }

        $totalGaji = max($salaryKotor - $totalPotongan, 0);

        return Pdf::loadView('admin.gaji.slip-pdf', compact(
            'user',
            'salary',
            'periode',
            'gajiPerHari',
            'gajiPokokFix',
            'hariHadir',
            'hariTelat',
            'menitTerlambat',
            'totalJamLembur',
            'totalLembur',
            'jobBonus',
            'totalBonusJob',
            'potonganTelatNominal',
            'totalPotongan',
            'salaryKotor',
            'totalGaji'
        ))->stream(
            'Slip-Gaji-' . $user->name . '-' . $date->format('m-Y') . '.pdf'
        );
    }
}
