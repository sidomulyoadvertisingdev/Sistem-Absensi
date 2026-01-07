<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Lembur;
use App\Models\UserSalary;
use App\Models\JobApplicant;
use App\Models\JobTodo;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /*
        |------------------------------------------------------------------
        | ABSENSI TERBARU
        |------------------------------------------------------------------
        */
        $absensiTerbaru = Absensi::with('user')
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
