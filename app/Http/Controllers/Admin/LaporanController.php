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

    /*
    =================================================
    FINAL PAYROLL ENGINE — SAFE VERSION
    + FILTER PENEMPATAN
    =================================================
    */

    private function buildLaporan(Request $request): array
    {
        $bulan = (int) ($request->bulan ?? date('m'));
        $tahun = (int) ($request->tahun ?? date('Y'));
        $hariKerjaStandar = 26;

        // 🔥 FILTER TEMPAT
        $penempatanFilter = $request->penempatan;

        $usersQuery = User::with('salary')
            ->where('role', User::ROLE_KARYAWAN);

        if (!empty($penempatanFilter)) {
            $usersQuery->where('penempatan', $penempatanFilter);
        }

        $users = $usersQuery
            ->orderBy('name')
            ->get();

        // daftar tempat untuk dropdown filter
        $penempatanList = User::whereNotNull('penempatan')
            ->distinct()
            ->pluck('penempatan');

        $rules = SalaryDeductionRule::where('aktif', true)->get();

        $laporan = [];
        $no = 1;

        foreach ($users as $user) {

            $salary = $user->salary;
            if (!$salary || !$salary->aktif) continue;

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

            $hariNormal     = min($presensi, $hariKerjaStandar);
            $hariTambahan   = max($presensi - $hariKerjaStandar, 0);
            $hariTidakMasuk = max($hariKerjaStandar - $hariNormal, 0);

            $menitTerlambat = (int) $absensis
                ->where('status', 'terlambat')
                ->sum(fn($a) => (int) ($a->menit_terlambat ?? 0));

            /*
            ================= TUNJANGAN
            */

            $tunjangan = [
                'tunjangan_umum'      => (float) ($salary->tunjangan_umum ?? 0),
                'tunjangan_transport' => (float) ($salary->tunjangan_transport ?? 0),
                'tunjangan_thr'       => (float) ($salary->tunjangan_thr ?? 0),
                'tunjangan_kesehatan' => (float) ($salary->tunjangan_kesehatan ?? 0),
            ];

            $totalTunjangan = array_sum($tunjangan);

            /*
            ================= GAJI
            */

            $gajiPerHari = (float) ($salary->gaji_harian ?? 0);

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

            $uangLembur = $totalJamLembur * (float) ($salary->lembur_per_jam ?? 0);

            /*
            ================= BONUS JOB
            */

            $bonusJob = DB::table('job_todo_user')
                ->join('job_todos', 'job_todos.id', '=', 'job_todo_user.job_todo_id')
                ->where('job_todo_user.user_id', $user->id)
                ->where('job_todo_user.status', 'completed')
                ->whereMonth('job_todo_user.completed_at', $bulan)
                ->whereYear('job_todo_user.completed_at', $tahun)
                ->sum('job_todos.bonus');

            /*
            ================= SALARY KOTOR
            */

            $salaryKotor =
                $gajiNormal +
                $gajiTambahan +
                $uangLembur +
                $bonusJob;

            if ($salary->include_tunjangan) {
                $salaryKotor += $totalTunjangan;
            }

            /*
            ================= RULE ENGINE POTONGAN
            */

            $totalPotongan = 0;
            $potonganTelat = 0;

            foreach ($rules as $rule) {

                if (!$rule->isApplicableForPenempatan($user->penempatan)) continue;

                $base = match ($rule->base_source) {

                    'gaji_pokok' =>
                        (float) ($salary->gaji_pokok ?? 0),

                    'tunjangan' =>
                        collect($rule->tunjangan_items ?? [])
                            ->sum(fn ($item) => $tunjangan[$item] ?? 0),

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

                    $potonganTelat += $nilai;
                    $totalPotongan += $nilai;
                }

                if ($rule->condition_type === 'off_day') {

                    if ($hariTidakMasuk <= 0) continue;

                    $nilai = $rule->calculate($base);

                    $totalPotongan += $nilai;
                }
            }

            /*
            ================= FINAL
            */

            $gajiDiterima = max($salaryKotor - $totalPotongan, 0);

            /*
            ================= OUTPUT
            */

            $laporan[] = [

                'no' => $no++,

                'toko' => $user->penempatan ?? '-',
                'nama' => $user->name,

                'hari_hadir'       => $presensi,
                'hari_normal'      => $hariNormal,
                'hari_tambahan'    => $hariTambahan,
                'hari_telat'       => $hariTelat,
                'hari_tidak_masuk' => $hariTidakMasuk,
                'menit_telat'      => $menitTerlambat,

                'gaji_pokok'   => (float) ($salary->gaji_pokok ?? 0),
                'gaji_per_hari'=> $gajiPerHari,

                'gaji_bruto' => $gajiNormal,
                'gaji_bonus' => $gajiTambahan,

                'tunjangan_umum'      => $tunjangan['tunjangan_umum'],
                'tunjangan_transport' => $tunjangan['tunjangan_transport'],
                'tunjangan_thr'       => $tunjangan['tunjangan_thr'],
                'tunjangan_kesehatan' => $tunjangan['tunjangan_kesehatan'],

                'lembur'    => $uangLembur,
                'bonus_job' => $bonusJob,

                'potongan_telat' => $potonganTelat,
                'total_potongan' => $totalPotongan,

                'salary_kotor'  => $salaryKotor,
                'gaji_diterima' => $gajiDiterima,
            ];
        }

        return compact(
            'laporan',
            'bulan',
            'tahun',
            'penempatanFilter',
            'penempatanList'
        );
    }
}
