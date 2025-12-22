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
        return view('admin.dashboard', [

            /*
            |--------------------------------------------------------------------------
            | INFO BOX UTAMA
            |--------------------------------------------------------------------------
            */
            'totalAbsensi' => Absensi::count(),

            'totalLembur' => Lembur::count(),

            // sementara pakai jumlah user
            'totalGaji' => User::count(),

            'totalUser' => User::where('role', 'karyawan')->count(),

            /*
            |--------------------------------------------------------------------------
            | INFO BOX PENEMPATAN KERJA
            |--------------------------------------------------------------------------
            */
            'karyawanSMLeccy' => User::where('role', 'karyawan')
                ->where('penempatan', 'SM Lecy')
                ->count(),

            'karyawanGudang' => User::where('role', 'karyawan')
                ->where('penempatan', 'SM Gudang')
                ->count(),

            'karyawanSMPenempatan' => User::where('role', 'karyawan')
                ->where('penempatan', 'SM Percetakan')
                ->count(),

            /*
            |--------------------------------------------------------------------------
            | DATA TERBARU
            |--------------------------------------------------------------------------
            */
            'absensiTerbaru' => Absensi::with('user')
                ->orderBy('tanggal', 'desc')
                ->limit(5)
                ->get(),

            'lemburTerbaru' => Lembur::with('user')
                ->orderBy('tanggal', 'desc')
                ->limit(5)
                ->get(),
        ]);
    }
}
