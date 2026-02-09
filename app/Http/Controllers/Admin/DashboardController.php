<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Lembur;
use App\Models\UserSalary;
use App\Models\JobApplicant;
use App\Models\JobTodo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $selectedMonth = Carbon::now()->startOfMonth();
        $bulan = $request->query('bulan');

        if (is_string($bulan) && preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            try {
                $selectedMonth = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
            } catch (\Throwable $e) {
                $selectedMonth = Carbon::now()->startOfMonth();
            }
        }

        $selectedBulan = $selectedMonth->format('Y-m');
        $startOfMonth = $selectedMonth->copy()->startOfMonth()->toDateString();
        $endOfMonth = $selectedMonth->copy()->endOfMonth()->toDateString();
        $periodeAbsensiLabel = $selectedMonth->copy()
            ->locale('id')
            ->translatedFormat('F Y');

        $karyawanIds = User::where('role', User::ROLE_KARYAWAN)
            ->pluck('id');

        /*
        |------------------------------------------------------------------
        | ABSENSI TERBARU
        |------------------------------------------------------------------
        */
        $absensiTerbaru = Absensi::with('user')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($absensi) {

                if ($absensi->jam_pulang) {
                    $absensi->jam_tampil = $absensi->jam_pulang;
                    $absensi->aksi_label = 'Pulang';
                } elseif ($absensi->istirahat_selesai) {
                    $absensi->jam_tampil = $absensi->istirahat_selesai;
                    $absensi->aksi_label = 'Selesai Istirahat';
                } elseif ($absensi->istirahat_mulai) {
                    $absensi->jam_tampil = $absensi->istirahat_mulai;
                    $absensi->aksi_label = 'Istirahat';
                } elseif ($absensi->jam_masuk) {
                    $absensi->jam_tampil = $absensi->jam_masuk;
                    $absensi->aksi_label = 'Masuk';
                } else {
                    $absensi->jam_tampil = '-';
                    $absensi->aksi_label = '-';
                }

                $absensi->status_label = match ($absensi->status) {
                    'hadir' => 'Hadir',
                    'terlambat' => 'Terlambat',
                    'tidak_hadir' => 'Tidak Hadir',
                    default => ucfirst($absensi->status),
                };

                $absensi->status_badge = match ($absensi->status) {
                    'hadir' => 'success',
                    'terlambat' => 'warning',
                    'tidak_hadir' => 'danger',
                    default => 'secondary',
                };

                $absensi->aksi_badge = match ($absensi->aksi_label) {
                    'Masuk' => 'info',
                    'Istirahat' => 'primary',
                    'Selesai Istirahat' => 'secondary',
                    'Pulang' => 'dark',
                    default => 'light',
                };

                return $absensi;
            });

        /*
        |------------------------------------------------------------------
        | LEMBUR TERBARU
        |------------------------------------------------------------------
        */
        $lemburTerbaru = Lembur::with('user')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->latest('tanggal')
            ->limit(5)
            ->get();

        /*
        |------------------------------------------------------------------
        | INFO BOX UTAMA
        |------------------------------------------------------------------
        */
        $totalAbsensi = Absensi::count();
        $totalLembur  = Lembur::count();
        $totalUser    = User::where('role', User::ROLE_KARYAWAN)->count();
        $totalGaji    = UserSalary::where('aktif', true)->count();

        /*
        |------------------------------------------------------------------
        | ANALITIK DISIPLIN KARYAWAN (BULAN BERJALAN)
        |------------------------------------------------------------------
        */
        $terlambatBulanIni = Absensi::whereIn('user_id', $karyawanIds)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->where('status', 'terlambat');

        $karyawanTerlambat = (clone $terlambatBulanIni)
            ->distinct()
            ->count('user_id');

        $totalKeterlambatan = (clone $terlambatBulanIni)->count();

        $totalMenitTerlambat = (clone $terlambatBulanIni)
            ->sum('menit_terlambat');

        $hariKerjaStandar = 26;

        // Samakan dengan laporan: tidak masuk = 26 - (hadir + terlambat) per karyawan.
        $presensiPerUser = Absensi::select('user_id', DB::raw('COUNT(*) as total_presensi'))
            ->whereIn('user_id', $karyawanIds)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->whereIn('status', ['hadir', 'terlambat'])
            ->groupBy('user_id')
            ->pluck('total_presensi', 'user_id');

        $karyawanTidakMasuk = 0;
        $totalTidakMasuk = 0;

        foreach ($karyawanIds as $karyawanId) {
            $totalPresensi = (int) ($presensiPerUser[$karyawanId] ?? 0);
            $hariTidakMasuk = max($hariKerjaStandar - $totalPresensi, 0);

            if ($hariTidakMasuk > 0) {
                $karyawanTidakMasuk++;
                $totalTidakMasuk += $hariTidakMasuk;
            }
        }

        $karyawanTidakPernahTerlambat = max($totalUser - $karyawanTerlambat, 0);

        $totalHadir = Absensi::whereIn('user_id', $karyawanIds)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->where('status', 'hadir')
            ->count();

        $topKaryawanTerlambat = Absensi::with('user:id,name')
            ->select(
                'user_id',
                DB::raw('COUNT(*) as total_terlambat'),
                DB::raw('COALESCE(SUM(menit_terlambat), 0) as total_menit')
            )
            ->whereIn('user_id', $karyawanIds)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->where('status', 'terlambat')
            ->groupBy('user_id')
            ->orderByDesc('total_terlambat')
            ->orderByDesc('total_menit')
            ->limit(5)
            ->get();

        /*
        |------------------------------------------------------------------
        | MONITORING PELAMAR
        |------------------------------------------------------------------
        */
        $pelamarPending   = JobApplicant::where('status', 'pending')->count();
        $pelamarReview    = JobApplicant::where('status', 'review')->count();
        $pelamarInterview = JobApplicant::where('status', 'interview')->count();
        $pelamarTraining  = JobApplicant::where('status', 'training')->count();
        $pelamarAccepted  = JobApplicant::where('status', 'accepted')->count();
        $pelamarRejected  = JobApplicant::where('status', 'rejected')->count();

        /*
        |------------------------------------------------------------------
        | JOB TODO (SUDAH SESUAI STATUS FINAL)
        |------------------------------------------------------------------
        */
        $jobTotal = JobTodo::count();

        // broadcast belum diambil
        $jobOpen = JobTodo::where('status', 'open')->count();

        // sedang dikerjakan (direct + broadcast accepted)
        $jobSedangDikerjakan = JobTodo::where('status', 'in_progress')->count();

        // selesai (bonus masuk gaji)
        $jobSelesai = JobTodo::where('status', 'done')->count();

        // ditutup admin
        $jobClosed = JobTodo::where('status', 'closed')->count();

        /*
        |------------------------------------------------------------------
        | PENEMPATAN KARYAWAN
        |------------------------------------------------------------------
        */
        $karyawanSMLeccy = User::where('role', User::ROLE_KARYAWAN)
            ->where('penempatan', 'SM Lecy')
            ->count();

        $karyawanGudang = User::where('role', User::ROLE_KARYAWAN)
            ->where('penempatan', 'SM Gudang')
            ->count();

        $karyawanSMPenempatan = User::where('role', User::ROLE_KARYAWAN)
            ->where('penempatan', 'SM Percetakan')
            ->count();

        return view('admin.dashboard', compact(
            'totalAbsensi',
            'totalLembur',
            'totalGaji',
            'totalUser',
            'selectedBulan',
            'periodeAbsensiLabel',
            'karyawanTerlambat',
            'totalKeterlambatan',
            'totalMenitTerlambat',
            'karyawanTidakPernahTerlambat',
            'karyawanTidakMasuk',
            'totalTidakMasuk',
            'totalHadir',
            'topKaryawanTerlambat',

            'pelamarPending',
            'pelamarReview',
            'pelamarInterview',
            'pelamarTraining',
            'pelamarAccepted',
            'pelamarRejected',

            'jobTotal',
            'jobOpen',
            'jobSedangDikerjakan',
            'jobSelesai',
            'jobClosed',

            'karyawanSMLeccy',
            'karyawanGudang',
            'karyawanSMPenempatan',

            'absensiTerbaru',
            'lemburTerbaru'
        ));
    }
}
