<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSalary;
use App\Models\Lembur;
use App\Models\Absensi;
use App\Models\WorkSchedule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserSalaryController extends Controller
{
    /**
     * ===============================
     * LIST GAJI KARYAWAN (ADMIN)
     * ===============================
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
     * ===============================
     * FORM EDIT GAJI USER
     * ===============================
     */
    public function edit(User $user)
    {
        abort_if(!$user->isKaryawan(), 403);

        return view('admin.gaji.edit', compact('user'));
    }

    /**
     * ===============================
     * SIMPAN / UPDATE MASTER GAJI
     * ===============================
     */
    public function update(Request $request, User $user)
    {
        abort_if(!$user->isKaryawan(), 403);

        $request->validate([
            'gaji_pokok'           => 'required|numeric|min:0',
            'tunjangan_umum'       => 'nullable|numeric|min:0',
            'tunjangan_transport'  => 'nullable|numeric|min:0',
            'tunjangan_thr'        => 'nullable|numeric|min:0',
            'tunjangan_kesehatan'  => 'nullable|numeric|min:0',
            'lembur_per_jam'       => 'nullable|numeric|min:0',
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
            ->with('success', 'Data gaji berhasil disimpan');
    }

    /**
     * ===============================
     * CETAK SLIP GAJI (PDF)
     * ===============================
     */
    public function slipPdf(Request $request, User $user)
    {
        abort_if(!$user->isKaryawan(), 403);

        $salary = $user->salary;
        abort_if(!$salary || !$salary->aktif, 404);

        $date  = Carbon::createFromFormat('Y-m', $request->bulan ?? now()->format('Y-m'));
        $bulan = $date->month;
        $tahun = $date->year;

        /* ================= ABSENSI ================= */
        $absensis = Absensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        $hadir     = $absensis->where('status', 'hadir')->count();
        $terlambat = $absensis->where('status', 'terlambat')->count();
        $presensi  = $hadir + $terlambat;

        /* ================= TERLAMBAT ================= */
        $menitTerlambat = 0;
        $potonganPerMenit = 1000;

        foreach ($absensis->where('status', 'terlambat') as $absen) {

            if (!$absen->jam_masuk) continue;

            $hari = strtolower(
                Carbon::parse($absen->tanggal)->locale('id')->isoFormat('dddd')
            );

            $jadwal = WorkSchedule::where('user_id', $user->id)
                ->where('hari', $hari)
                ->where('aktif', true)
                ->first();

            if (!$jadwal || !$jadwal->jam_masuk) continue;

            $batasMasuk = Carbon::parse($absen->tanggal)
                ->setTimeFromTimeString($jadwal->jam_masuk);

            if ($jadwal->toleransi_masuk) {
                $batasMasuk->addMinutes($jadwal->toleransi_masuk);
            }

            $jamMasukReal = Carbon::parse($absen->tanggal)
                ->setTimeFromTimeString($absen->jam_masuk);

            if ($jamMasukReal->gt($batasMasuk)) {
                $menitTerlambat += $batasMasuk->diffInMinutes($jamMasukReal);
            }
        }

        $potonganTelatNominal = $menitTerlambat * $potonganPerMenit;

        /* ================= GAJI ================= */
        $hariKerja = 22;
        $gajiPerHari  = $salary->gaji_pokok / $hariKerja;
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

        /* ================= BONUS JOB TODO ================= */
        $jobBonus = DB::table('job_todo_user')
            ->join('job_todos', 'job_todos.id', '=', 'job_todo_user.job_todo_id')
            ->where('job_todo_user.user_id', $user->id)
            ->where('job_todo_user.status', 'completed')
            ->whereMonth('job_todo_user.completed_at', $bulan)
            ->whereYear('job_todo_user.completed_at', $tahun)
            ->select('job_todos.title', 'job_todos.bonus')
            ->get();

        $totalBonusJob = $jobBonus->sum('bonus');

        /* ================= SALARY KOTOR ================= */
        $salaryKotor =
            $gajiPokokFix +
            $salary->tunjangan_umum +
            $salary->tunjangan_transport +
            $salary->tunjangan_thr +
            $salary->tunjangan_kesehatan +
            $totalLembur +
            $totalBonusJob;

        /* ================= POTONGAN ATURAN ================= */
        $potonganAturan = 0;
        $rules = $user->activeSalaryDeductionRules();

        foreach ($rules as $rule) {

            $kena = match ($rule->condition_type) {
                'terlambat'  => $terlambat >= $rule->condition_value,
                'off_day'    => true,
                'pelanggaran'=> true,
                default      => false,
            };

            if (!$kena) continue;

            $basis = match ($rule->base_amount) {
                'gaji_pokok'   => $gajiPokokFix,
                'salary_kotor' => $salaryKotor,
                'total_gaji'   => max($salaryKotor - $potonganTelatNominal, 0),
                default        => $salaryKotor,
            };

            $potonganAturan += $rule->calculate($basis);
        }

        $totalPotongan = $potonganTelatNominal + $potonganAturan;
        $totalGaji     = max($salaryKotor - $totalPotongan, 0);

        return Pdf::loadView('admin.gaji.slip-pdf', compact(
            'user',
            'salary',
            'gajiPerHari',
            'gajiPokokFix',
            'totalJamLembur',
            'totalLembur',
            'jobBonus',
            'totalBonusJob',
            'menitTerlambat',
            'potonganTelatNominal',
            'potonganAturan',
            'salaryKotor',
            'totalGaji'
        ))->stream(
            'Slip-Gaji-' . $user->name . '-' . $date->format('m-Y') . '.pdf'
        );
    }
}
