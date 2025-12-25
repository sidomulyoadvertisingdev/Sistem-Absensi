<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Lembur;

class DashboardController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | ABSENSI TERBARU
        | - JAM SESUAI INPUT
        | - KETERANGAN AKSI (MASUK / ISTIRAHAT / DST)
        | - STATUS (HADIR / TERLAMBAT / TIDAK HADIR)
        |--------------------------------------------------------------------------
        */
        $absensiTerbaru = Absensi::with('user')
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($absensi) {

                // Tentukan JAM & AKSI terakhir
                if (!empty($absensi->jam_pulang)) {
                    $absensi->jam_tampil  = $absensi->jam_pulang;
                    $absensi->aksi_label  = 'Pulang';
                } elseif (!empty($absensi->istirahat_selesai)) {
                    $absensi->jam_tampil  = $absensi->istirahat_selesai;
                    $absensi->aksi_label  = 'Selesai Istirahat';
                } elseif (!empty($absensi->istirahat_mulai)) {
                    $absensi->jam_tampil  = $absensi->istirahat_mulai;
                    $absensi->aksi_label  = 'Istirahat';
                } elseif (!empty($absensi->jam_masuk)) {
                    $absensi->jam_tampil  = $absensi->jam_masuk;
                    $absensi->aksi_label  = 'Masuk';
                } else {
                    $absensi->jam_tampil  = '-';
                    $absensi->aksi_label  = '-';
                }

                // Status label
                $absensi->status_label = match ($absensi->status) {
                    'hadir'       => 'Hadir',
                    'terlambat'   => 'Terlambat',
                    'tidak_hadir' => 'Tidak Hadir',
                    default       => ucfirst($absensi->status),
                };

                // Badge status
                $absensi->status_badge = match ($absensi->status) {
                    'hadir'       => 'success',
                    'terlambat'   => 'warning',
                    'tidak_hadir' => 'danger',
                    default       => 'secondary',
                };

                // Badge aksi
                $absensi->aksi_badge = match ($absensi->aksi_label) {
                    'Masuk'             => 'info',
                    'Istirahat'         => 'primary',
                    'Selesai Istirahat' => 'secondary',
                    'Pulang'            => 'dark',
                    default             => 'light',
                };

                return $absensi;
            });

        return view('admin.dashboard', [

            /* INFO BOX */
            'totalAbsensi' => Absensi::count(),
            'totalLembur'  => Lembur::count(),
            'totalGaji'    => User::count(),
            'totalUser'    => User::where('role', 'karyawan')->count(),

            /* PENEMPATAN */
            'karyawanSMLeccy' => User::where('role', 'karyawan')
                ->where('penempatan', 'SM Lecy')->count(),

            'karyawanGudang' => User::where('role', 'karyawan')
                ->where('penempatan', 'SM Gudang')->count(),

            'karyawanSMPenempatan' => User::where('role', 'karyawan')
                ->where('penempatan', 'SM Percetakan')->count(),

            /* DATA TERBARU */
            'absensiTerbaru' => $absensiTerbaru,

            'lemburTerbaru' => Lembur::with('user')
                ->orderBy('tanggal', 'desc')
                ->limit(5)
                ->get(),
        ]);
    }
}
