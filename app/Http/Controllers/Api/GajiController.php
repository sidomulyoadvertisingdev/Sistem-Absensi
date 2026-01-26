<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lembur;
use App\Models\Absensi;
use App\Models\WorkSchedule;
use App\Models\SalaryDeductionRule;
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
         * BULAN & KONSTANTA
         * ================================================== */
        $now   = Carbon::now();
        $bulan = $now->month;
        $tahun = $now->year;

        $jumlahHariBulan  = $now->daysInMonth;
        $hariKerjaStandar = 22;
        $potonganPerMenit = 1000;

        /* ==================================================
         * ABSENSI
         * ================================================== */
        $absensis = Absensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        $hadir     = $absensis->where('status', 'hadir')->count();
        $terlambat = $absensis->where('status', 'terlambat')->count();

        $presensi = $hadir + $terlambat;
        $offDay   = max($jumlahHariBulan - $presensi, 0);

        /* ==================================================
         * MENIT TERLAMBAT
         * ================================================== */
        $menitTerlambat = 0;

        foreach ($absensis->where('status', 'terlambat') as $absen) {

            if (!$absen->jam_masuk) continue;

            $hari = strtolower(
                Carbon::parse($absen->tanggal)
                    ->locale('id')
                    ->isoFormat('dddd')
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

        /* ==================================================
         * GAJI POKOK PROPORSIONAL
         * ================================================== */
        $gajiPerHari  = $salary->gaji_pokok / $hariKerjaStandar;
        $gajiPokokFix = round($gajiPerHari * $presensi);

        /* ==================================================
         * LEMBUR
         * ================================================== */
        $totalMenitLembur = 0;

        $lemburs = Lembur::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->where('status', 'approved')
            ->get();

        foreach ($lemburs as $l) {
            if (!$l->jam_mulai || !$l->jam_selesai) continue;

            $mulai   = Carbon::parse($l->tanggal)->setTimeFromTimeString($l->jam_mulai);
            $selesai = Carbon::parse($l->tanggal)->setTimeFromTimeString($l->jam_selesai);

            if ($selesai->lessThan($mulai)) {
                $selesai->addDay();
            }

            $totalMenitLembur += $mulai->diffInMinutes($selesai);
        }

        $jamLembur  = round($totalMenitLembur / 60, 1);
        $uangLembur = round($jamLembur * ($salary->lembur_per_jam ?? 0));

        /* ==================================================
         * BONUS JOB TODO (WAJIB completed_at TERISI)
         * ================================================== */
        $totalBonusJob = DB::table('job_todo_user')
            ->join('job_todos', 'job_todos.id', '=', 'job_todo_user.job_todo_id')
            ->where('job_todo_user.user_id', $user->id)
            ->where('job_todo_user.status', 'completed')
            ->whereNotNull('job_todo_user.completed_at')
            ->whereMonth('job_todo_user.completed_at', $bulan)
            ->whereYear('job_todo_user.completed_at', $tahun)
            ->sum('job_todos.bonus');

        /* ==================================================
         * SALARY KOTOR
         * ================================================== */
        $salaryKotor =
            $gajiPokokFix +
            $salary->tunjangan_umum +
            $salary->tunjangan_transport +
            $salary->tunjangan_thr +
            $salary->tunjangan_kesehatan +
            $uangLembur +
            $totalBonusJob;

        /* ==================================================
         * POTONGAN ATURAN (ADMIN)
         * ================================================== */
        $potonganAturan = 0;

        $rules = SalaryDeductionRule::where('aktif', true)->get();

        foreach ($rules as $rule) {

            // Filter penempatan
            if (!empty($rule->penempatan)) {
                $penempatanRule = is_array($rule->penempatan)
                    ? $rule->penempatan
                    : json_decode($rule->penempatan, true);

                if (!in_array($user->penempatan, $penempatanRule)) {
                    continue;
                }
            }

            // Cek kondisi
            $kena = false;

            if ($rule->condition_type === 'terlambat' && $terlambat >= $rule->condition_value) {
                $kena = true;
            }

            if ($rule->condition_type === 'off_day' && $offDay >= $rule->condition_value) {
                $kena = true;
            }

            if ($rule->condition_type === 'pelanggaran') {
                $kena = true;
            }

            if (!$kena) continue;

            $basis = match ($rule->base_amount) {
                'gaji_pokok'   => $gajiPokokFix,
                'salary_kotor' => $salaryKotor,
                'total_gaji'   => max($salaryKotor - $potonganTelatNominal, 0),
                default        => $salaryKotor,
            };

            $potonganAturan +=
                $rule->type === 'percentage'
                    ? ($basis * $rule->value / 100)
                    : $rule->value;
        }

        /* ==================================================
         * TOTAL GAJI BERSIH
         * ================================================== */
        $totalPotongan = $potonganTelatNominal + $potonganAturan;
        $totalGaji     = max($salaryKotor - $totalPotongan, 0);

        return response()->json([
            'data' => [
                'bulan'               => $now->translatedFormat('F Y'),

                'presensi'            => $presensi,
                'off_day'             => $offDay,

                'gaji_pokok_fix'      => $gajiPokokFix,
                'tunjangan_umum'      => $salary->tunjangan_umum,
                'tunjangan_transport' => $salary->tunjangan_transport,
                'tunjangan_thr'       => $salary->tunjangan_thr,
                'tunjangan_kesehatan' => $salary->tunjangan_kesehatan,

                'jam_lembur'          => $jamLembur,
                'uang_lembur'         => $uangLembur,
                'bonus_job'           => round($totalBonusJob),

                'potongan_telat'      => $potonganTelatNominal,
                'potongan_aturan'     => round($potonganAturan),

                'salary_kotor'        => round($salaryKotor),
                'total_gaji'          => round($totalGaji),
            ]
        ], 200);
    }
}
