<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lembur;
use App\Models\Absensi;
use App\Models\SalaryDeductionRule;
use App\Services\EarlyLeaveSalaryService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GajiController extends Controller
{
    /**
     * ==================================================
     * GAJI USER LOGIN (MOBILE)
     * ==================================================
     */
    public function index(Request $request)
    {
        $user   = $request->user();
        $salary = $user->salary;

        if (!$salary || !$salary->aktif) {
            return response()->json([
                'data' => null,
                'message' => 'Data gaji belum tersedia'
            ], 200);
        }

        /* ==================================================
         * BULAN (OPTIONAL: ?bulan=YYYY-MM)
         * ================================================== */
        $bulanYm = $request->query('bulan');
        $date = Carbon::now();

        if (!empty($bulanYm)) {
            try {
                $date = Carbon::createFromFormat('Y-m', $bulanYm);
            } catch (\Exception $e) {
                $date = Carbon::now();
            }
        }

        $bulan = $date->month;
        $tahun = $date->year;
        $hariKerjaStandar = 26;

        /* ==================================================
         * ABSENSI (SELARAS PAYROLL)
         * ================================================== */
        $absensis = Absensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        $hariHadir = $absensis->where('status', 'hadir')->count();
        $hariTelat = $absensis->where('status', 'terlambat')->count();

        $presensi = $hariHadir + $hariTelat;
        $hariKerjaMasuk = $presensi;

        $hariNormal   = min($presensi, $hariKerjaStandar);
        $hariTambahan = max($presensi - $hariKerjaStandar, 0);
        $offDay       = max($hariKerjaStandar - $hariNormal, 0);

        $menitTerlambat = (int) $absensis
            ->where('status', 'terlambat')
            ->sum('menit_terlambat');

        /* ==================================================
         * TUNJANGAN (SELARAS PAYROLL)
         * ================================================== */
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

        $totalTunjangan = $tunjanganPayroll;

        /* ==================================================
         * GAJI HARIAN (SELARAS PAYROLL)
         * ================================================== */
        $gajiPerHari = (float) $salary->getGajiHarian($hariKerjaStandar);

        $earlyLeaveSalary = app(EarlyLeaveSalaryService::class)->calculate(
            user: $user,
            absensis: $absensis,
            gajiPerHari: $gajiPerHari,
            bulan: $bulan,
            tahun: $tahun,
            hariKerjaStandar: $hariKerjaStandar
        );

        $gajiNormal = (float) $earlyLeaveSalary['gaji_normal'];
        $gajiTambahan = (float) $earlyLeaveSalary['gaji_tambahan'];
        $gajiBruto = (float) $earlyLeaveSalary['gaji_bruto'];
        $hariKerjaSetara = (float) $earlyLeaveSalary['hari_kerja_setara'];
        $hariNormalSetara = (float) $earlyLeaveSalary['hari_normal_setara'];
        $hariTambahanSetara = (float) $earlyLeaveSalary['hari_tambahan_setara'];
        $jumlahIzinPulangAwal = (int) $earlyLeaveSalary['jumlah_izin_pulang_awal'];
        $potonganIzinPulangAwal = (float) $earlyLeaveSalary['potongan_izin_pulang_awal'];

        /* ==================================================
         * LEMBUR (SELARAS PAYROLL)
         * ================================================== */
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

        /* ==================================================
         * BONUS JOB (SELARAS PAYROLL)
         * ================================================== */
        $jobBonus = DB::table('job_todo_user')
            ->join('job_todos', 'job_todos.id', '=', 'job_todo_user.job_todo_id')
            ->where('job_todo_user.user_id', $user->id)
            ->where('job_todo_user.status', 'completed')
            ->whereMonth('job_todo_user.completed_at', $bulan)
            ->whereYear('job_todo_user.completed_at', $tahun)
            ->select('job_todos.title', 'job_todos.bonus')
            ->get();

        $totalBonusJob = $jobBonus->sum('bonus');

        /* ==================================================
         * SALARY KOTOR (SELARAS PAYROLL)
         * ================================================== */
        $salaryKotor =
            $gajiNormal +
            $gajiTambahan +
            $uangLembur +
            $totalBonusJob +
            $tunjanganPayroll;

        /* ==================================================
         * POTONGAN ATURAN (SELARAS PAYROLL)
         * ================================================== */
        $rules = SalaryDeductionRule::where('aktif', true)->get();

        $totalPotongan = 0;
        $potonganTelatNominal = 0;
        $potonganTrainingNominal = 0;

        foreach ($rules as $rule) {

            if (!$rule->isApplicableForPenempatan($user->penempatan)) {
                continue;
            }

            $base = match ($rule->base_source) {
                'gaji_pokok' =>
                    (float) ($salary->gaji_pokok ?? 0),
                'tunjangan' =>
                    collect($rule->tunjangan_items ?? [])
                        ->sum(fn ($item) => $tunjanganArray[$item] ?? 0),
                default =>
                    $salaryKotor,
            };

            if ($base <= 0) {
                continue;
            }

            if ($rule->condition_type === 'terlambat') {

                $trigger = $rule->condition_value ?? 1;

                if ($hariTelat < $trigger) {
                    continue;
                }

                if ($rule->max_minutes &&
                    $menitTerlambat < $rule->max_minutes) {
                    continue;
                }

                $nilai = $rule->calculate($base);

                $potonganTelatNominal += $nilai;
                $totalPotongan += $nilai;
            }

            if ($rule->condition_type === 'off_day') {

                if ($offDay <= 0) {
                    continue;
                }

                $nilai = $rule->calculate($base);
                $totalPotongan += $nilai;
            }
        }

        $trainingInfo = $salary->calculateTrainingDeduction($salaryKotor, $date);
        $potonganTrainingNominal = (float) ($trainingInfo['deduction_nominal'] ?? 0);
        $totalPotongan += $potonganTrainingNominal;

        /* ==================================================
         * TOTAL GAJI BERSIH (SELARAS PAYROLL)
         * ================================================== */
        $totalGaji = max($salaryKotor - $totalPotongan, 0);

        return response()->json([
            'data' => [
                'bulan'                 => $date->translatedFormat('F Y'),
                'periode'               => $date->format('Y-m'),

                'presensi'              => $hariKerjaMasuk,
                'hari_hadir'            => $hariHadir,
                'hari_telat'            => $hariTelat,
                'hari_kerja_setara'     => round($hariKerjaSetara, 2),
                'hari_normal_setara'    => round($hariNormalSetara, 2),
                'hari_tambahan_setara'  => round($hariTambahanSetara, 2),
                'hari_normal'           => $hariNormal,
                'hari_tambahan'         => $hariTambahan,
                'off_day'               => $offDay,
                'menit_terlambat'        => $menitTerlambat,

                'gaji_pokok'            => (float) ($salary->gaji_pokok ?? 0),
                'gaji_per_hari'         => round($gajiPerHari, 2),
                'gaji_normal'           => round($gajiNormal),
                'gaji_tambahan'         => round($gajiTambahan),
                'gaji_bruto'            => round($gajiBruto),
                'gaji_dasar'            => round($gajiBruto),
                // compat lama
                'gaji_pokok_fix'        => round($gajiBruto),
                'izin_pulang_awal_count'=> $jumlahIzinPulangAwal,
                'potongan_izin_pulang_awal' => round($potonganIzinPulangAwal),

                'tunjangan_umum'        => (float) ($salary->tunjangan_umum ?? 0),
                'tunjangan_transport'   => (float) ($salary->tunjangan_transport ?? 0),
                'tunjangan_thr'         => (float) ($salary->tunjangan_thr ?? 0),
                'tunjangan_kesehatan'   => (float) ($salary->tunjangan_kesehatan ?? 0),
                'total_tunjangan_master'=> round($totalTunjanganMaster),
                'include_tunjangan'     => (bool) $salary->include_tunjangan,
                'tunjangan_payroll'     => round($tunjanganPayroll),
                'total_tunjangan'       => round($totalTunjangan),

                'jam_lembur'            => round($totalJamLembur, 1),
                'uang_lembur'           => round($uangLembur),
                'total_lembur'          => round($uangLembur),
                'bonus_job'             => round($totalBonusJob),

                'salary_kotor'          => round($salaryKotor),

                'potongan_telat'        => round($potonganTelatNominal),
                'potongan_training'     => round($potonganTrainingNominal),
                'potongan_aturan'       => round(max($totalPotongan - $potonganTelatNominal - $potonganTrainingNominal, 0)),
                'total_potongan'        => round($totalPotongan),

                'total_gaji'            => round($totalGaji),

                // detail opsional
                'training_info'         => $trainingInfo,
                'bonus_job_items'       => $jobBonus,
            ]
        ], 200);
    }
}

