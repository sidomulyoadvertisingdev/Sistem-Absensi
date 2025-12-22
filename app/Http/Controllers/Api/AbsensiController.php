<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /**
     * CHECK-IN USER
     * Hitung terlambat otomatis
     */
    public function checkin(Request $request)
    {
        $user = $request->user();

        // Cegah double check-in di hari yang sama
        $cek = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', now())
            ->first();

        if ($cek) {
            return response()->json([
                'message' => 'Anda sudah check-in hari ini'
            ], 400);
        }

        $jamMasuk = Carbon::now();

        // JAM KERJA NORMAL (08:00)
        $batasMasuk = Carbon::createFromTime(8, 0, 0);

        // HITUNG TERLAMBAT
        $terlambat = $jamMasuk->gt($batasMasuk);

        Absensi::create([
            'user_id'   => $user->id,
            'tanggal'   => now()->toDateString(),
            'jam_masuk' => $jamMasuk->toTimeString(),
            'status'    => 'hadir',
            'terlambat' => $terlambat
        ]);

        return response()->json([
            'message'   => 'Check-in berhasil',
            'jam_masuk' => $jamMasuk->toTimeString(),
            'terlambat' => $terlambat
        ]);
    }

    /**
     * CHECK-OUT USER
     */
    public function checkout(Request $request)
    {
        $user = $request->user();

        $absensi = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', now())
            ->first();

        if (!$absensi) {
            return response()->json([
                'message' => 'Anda belum check-in'
            ], 400);
        }

        if ($absensi->jam_pulang) {
            return response()->json([
                'message' => 'Anda sudah check-out'
            ], 400);
        }

        $absensi->update([
            'jam_pulang' => Carbon::now()->toTimeString()
        ]);

        return response()->json([
            'message'    => 'Check-out berhasil',
            'jam_pulang' => $absensi->jam_pulang
        ]);
    }

    /**
     * RIWAYAT ABSENSI USER (UNTUK FRONTEND)
     */
    public function riwayat(Request $request)
    {
        $data = Absensi::where('user_id', $request->user()->id)
            ->orderBy('tanggal', 'desc')
            ->limit(30)
            ->get();

        return response()->json($data);
    }
}
