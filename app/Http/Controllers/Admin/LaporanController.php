<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Lembur;
use App\Models\WorkSchedule;
use App\Models\SalaryDeductionRule;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    /**
     * ===============================
     * TAMPILAN LAPORAN (WEB)
     * ===============================
     */
    public function index(Request $request)
    {
        $data = $this->buildLaporan($request);
        return view('admin.laporan.index', $data);
    }

    /**
     * ===============================
     * EXPORT PDF LAPORAN GAJI
     * ===============================
     */
    public function exportPdf(Request $request)
    {
        $data = $this->buildLaporan($request);

        $pdf = Pdf::loadView(
            'admin.laporan.gaji-pdf',
            $data
        )->setPaper('a4', 'landscape');

        return $pdf->stream(
            'Laporan-Gaji-' . $data['bulan'] . '-' . $data['tahun'] . '.pdf'
        );
    }

    /**
     * ===============================
     * CORE LOGIC LAPORAN
     * ===============================
     */
    private function buildLaporan(Request $request): array
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $jumlahHariBulan  = Carbon::create($tahun, $bulan)->daysInMonth;
        $hariKerjaStandar = 22;
        $potonganPerMenit = 1000;

        $users = User::with('salary')
            ->where('role', User::ROLE_KARYAWAN)
            ->orderBy('name')
            ->get();

        // 🔥 ATURAN POTONGAN AKTIF
        $rules = SalaryDeductionRule::where('aktif', true)->get();

        $laporan = [];
        $no = 1;

        foreach ($users as $user) {

            if (!$user->salary || !$user->salary->aktif) {
                continue;
            }

            $salary = $user->salary;

            /* ================= ABSENSI ================= */
            $absensis = Absensi::where('user_id', $user->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->get();

            $hadir     = $absensis->where('status', 'hadir')->count();
            $terlambat = $absensis->where('status', 'terlambat')->count();

            $presensi = $hadir + $terlambat;
            $offDay   = max($jumlahHariBulan - $presensi, 0);

            /* ================= MENIT TERLAMBAT ================= */
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

            /* ================= LEMBUR ================= */
            $totalMenitLembur = 0;

            $lemburs = Lembur::where('user_id', $user->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('status', 'approved')
                ->get();

            foreach ($lemburs as $l) {
                $mulai   = Carbon::parse($l->tanggal)->setTimeFromTimeString($l->jam_mulai);
                $selesai = Carbon::parse($l->tanggal)->setTimeFromTimeString($l->jam_selesai);

                if ($selesai->lessThan($mulai)) {
                    $selesai->addDay();
                }

                $totalMenitLembur += $mulai->diffInMinutes($selesai);
            }

            $jamLembur  = $totalMenitLembur / 60;
            $uangLembur = $jamLembur * ($salary->lembur_per_jam ?? 0);

            /* ================= GAJI ================= */
            $gajiPerHari  = $salary->gaji_pokok / $hariKerjaStandar;
            $gajiPokokFix = $gajiPerHari * $presensi;

            $salaryKotor =
                $gajiPokokFix +
                $salary->tunjangan_umum +
                $salary->tunjangan_transport +
                $salary->tunjangan_thr +
                $salary->tunjangan_kesehatan +
                $uangLembur;

            /* ================= POTONGAN ================= */
            $potonganTelatNominal = $menitTerlambat * $potonganPerMenit;
            $potonganAturan = 0;

            foreach ($rules as $rule) {

                // 🔥 FILTER PENEMPATAN
                if (!empty($rule->penempatan)) {

                    $penempatanRule = is_array($rule->penempatan)
                        ? $rule->penempatan
                        : json_decode($rule->penempatan, true);

                    if (!in_array($user->penempatan, $penempatanRule)) {
                        continue;
                    }
                }

                // 🔥 CEK KONDISI
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

                // 🔥 BASIS HITUNG
                $basis = match ($rule->base_amount) {
                    'gaji_pokok'   => $gajiPokokFix,
                    'salary_kotor' => $salaryKotor,
                    'total_gaji'   => max($salaryKotor - $potonganTelatNominal, 0),
                    default        => $salaryKotor,
                };

                // 🔥 HITUNG POTONGAN
                if ($rule->type === 'percentage') {
                    $potonganAturan += ($basis * $rule->value) / 100;
                } else {
                    $potonganAturan += $rule->value;
                }
            }

            $totalPotongan = $potonganTelatNominal + $potonganAturan;
            $totalGaji     = max($salaryKotor - $totalPotongan, 0);

            /* ================= DATA ROW ================= */
            $laporan[] = [
                'no'                     => $no++,
                'toko'                   => $user->penempatan ?? '-',
                'nama'                   => $user->name,
                'jumlah_hari'            => $jumlahHariBulan,
                'off_day'                => $offDay,
                'presensi_masuk'         => $presensi,
                'gaji_pokok'             => $salary->gaji_pokok,
                'tunjangan_umum'         => $salary->tunjangan_umum,
                'tunjangan_transport'    => $salary->tunjangan_transport,
                'tunjangan_hari_raya'    => $salary->tunjangan_thr,
                'tunjangan_kesehatan'    => $salary->tunjangan_kesehatan,
                'hitungan_per_hari'      => $gajiPerHari,
                'kerja_tidak_jam'        => $terlambat,
                'lembur_poin_lain'       => $uangLembur,
                'potongan_n_telat'       => $menitTerlambat,
                'nominal_potongan_telat' => $potonganTelatNominal,
                'salary'                 => $salaryKotor,
                'total_gaji'             => $totalGaji,
            ];
        }

        return compact('laporan', 'bulan', 'tahun');
    }
}
