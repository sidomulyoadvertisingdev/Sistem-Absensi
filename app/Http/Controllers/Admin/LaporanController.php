<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Lembur;
use App\Models\SalaryDeductionRule;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.laporan.index', $this->buildLaporan($request));
    }

    public function exportPdf(Request $request)
    {
        $data = $this->buildLaporan($request);

        return Pdf::loadView('admin.laporan.gaji-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->stream("Laporan-Gaji-{$data['bulan']}-{$data['tahun']}.pdf");
    }

    /**
     * ======================================
     * INTI LAPORAN GAJI (SUMBER DATA TUNGGAL)
     * ======================================
     */
    private function buildLaporan(Request $request): array
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $hariKerjaStandar = 26;

        $users = User::with('salary')
            ->where('role', User::ROLE_KARYAWAN)
            ->orderBy('name')
            ->get();

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

            $hariHadir = $absensis->where('status', 'hadir')->count();
            $hariTelat = $absensis->where('status', 'terlambat_masuk')->count();
            $presensi  = $hariHadir + $hariTelat;

            $offDay = max($hariKerjaStandar - $presensi, 0);

            /**
             * ==================================================
             * 🔥 MENIT TERLAMBAT (SUM DARI DATABASE)
             * ==================================================
             */
            $menitTerlambat = $absensis
                ->where('status', 'terlambat_masuk')
                ->sum('menit_terlambat');

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

            $uangLembur = $totalJamLembur * ($salary->lembur_per_jam ?? 0);

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
            $totalPotongan      = 0;
            $potonganTelatTotal = 0;

            foreach ($rules as $rule) {

                if (!$rule->isApplicableForPenempatan($user->penempatan)) {
                    continue;
                }

                $kena = match ($rule->condition_type) {
                    'terlambat'   => $hariTelat >= $rule->condition_value,
                    'off_day'     => $offDay >= $rule->condition_value,
                    'pelanggaran' => true,
                    default       => false,
                };

                if (!$kena) {
                    continue;
                }

                // 🔥 TERLAMBAT → GAJI POKOK BULANAN
                $basis = $rule->condition_type === 'terlambat'
                    ? $salary->gaji_pokok
                    : match ($rule->base_amount) {
                        'gaji_pokok'   => $gajiPokokFix,
                        'salary_kotor' => $salaryKotor,
                        'total_gaji'   => max($salaryKotor - $totalPotongan, 0),
                    };

                $nilai = $rule->calculate($basis);

                if ($rule->condition_type === 'terlambat') {
                    $potonganTelatTotal = $nilai;
                }

                $totalPotongan += $nilai;
            }

            $totalGaji = max($salaryKotor - $totalPotongan, 0);

            /* ================= DATA LAPORAN ================= */
            $laporan[] = [
                'no'                  => $no++,
                'toko'                => $user->penempatan ?? '-',
                'nama'                => $user->name,

                'hari_hadir'          => $presensi,
                'hari_telat'          => $hariTelat,
                'off_day'             => $offDay,
                'menit_telat'         => $menitTerlambat,

                'gaji_pokok'          => $salary->gaji_pokok,
                'gaji_per_hari'       => $gajiPerHari,
                'tunjangan_umum'      => $salary->tunjangan_umum,
                'tunjangan_transport' => $salary->tunjangan_transport,
                'tunjangan_thr'       => $salary->tunjangan_thr,
                'tunjangan_kesehatan' => $salary->tunjangan_kesehatan,
                'lembur'              => $uangLembur,

                'potongan_telat'      => $potonganTelatTotal,
                'salary_kotor'        => $salaryKotor,
                'total_gaji'          => $totalGaji,
            ];
        }

        return compact('laporan', 'bulan', 'tahun');
    }
}
