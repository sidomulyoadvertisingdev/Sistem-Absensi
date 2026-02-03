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
use Illuminate\Support\Facades\DB;

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
     * INTI LAPORAN GAJI (FINAL + JOB TODO)
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
            $hariTelat = $absensis->where('status', 'terlambat')->count();
            $presensi  = $hariHadir + $hariTelat;
            $offDay    = max($hariKerjaStandar - $presensi, 0);

            $menitTerlambat = $absensis
                ->where('status', 'terlambat')
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

            /* ================= BONUS JOB TODO ================= */
            $bonusJob = DB::table('job_todo_user')
                ->join('job_todos', 'job_todos.id', '=', 'job_todo_user.job_todo_id')
                ->where('job_todo_user.user_id', $user->id)
                ->where('job_todo_user.status', 'completed')
                ->whereMonth('job_todo_user.completed_at', $bulan)
                ->whereYear('job_todo_user.completed_at', $tahun)
                ->sum('job_todos.bonus');

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

            /* ================= SALARY KOTOR ================= */
            $salaryKotor =
                $gajiPokokMaster +
                $totalTunjangan +
                $uangLembur +
                $bonusJob;

            /* ================= POTONGAN ================= */
            $totalPotongan = 0;
            $potonganTelat = 0;

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
                    $potonganTelat += $nilai;
                }

                $totalPotongan += $nilai;
            }

            /* ================= GAJI DITERIMA ================= */
            $gajiDiterima =
                $gajiProrata +
                $totalTunjangan +
                $uangLembur +
                $bonusJob -
                $totalPotongan;

            $gajiDiterima = max($gajiDiterima, 0);

            /* ================= DATA LAPORAN ================= */
            $laporan[] = [
                'no'                  => $no++,
                'toko'                => $user->penempatan ?? '-',
                'nama'                => $user->name,

                'hari_hadir'          => $presensi,
                'hari_telat'          => $hariTelat,
                'off_day'             => $offDay,
                'menit_telat'         => $menitTerlambat,

                'gaji_pokok'          => $gajiPokokMaster,
                'gaji_per_hari'       => $gajiPerHari,

                'tunjangan_umum'      => $tunjangan['tunjangan_umum'],
                'tunjangan_transport' => $tunjangan['tunjangan_transport'],
                'tunjangan_thr'       => $tunjangan['tunjangan_thr'],
                'tunjangan_kesehatan' => $tunjangan['tunjangan_kesehatan'],

                'lembur'              => $uangLembur,
                'bonus_job'           => $bonusJob,

                'potongan_telat'      => $potonganTelat,
                'total_potongan'      => $totalPotongan,

                'salary_kotor'        => $salaryKotor,
                'gaji_diterima'       => $gajiDiterima,
            ];
        }

        return compact('laporan', 'bulan', 'tahun');
    }
}
