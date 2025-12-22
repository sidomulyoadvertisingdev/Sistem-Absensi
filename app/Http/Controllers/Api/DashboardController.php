<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $user = $request->user();

        $bulan = now()->month;
        $tahun = now()->year;

        // HADIR BULAN INI
        $hadir = Absensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->where('status', 'hadir')
            ->count();

        // TERLAMBAT BULAN INI
        $terlambat = Absensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->where('terlambat', true)
            ->count();

        // IZIN BULAN INI
        $izin = Absensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->where('status', 'izin')
            ->count();

        // RESPONSE HARUS SESUAI FRONTEND
        return response()->json([
            'attendance' => [
                'month' => [
                    'hadir' => $hadir,
                    'terlambat' => $terlambat,
                    'izin' => $izin,
                ]
            ]
        ]);
    }
}
