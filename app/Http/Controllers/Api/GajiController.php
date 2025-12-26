<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lembur;
use Carbon\Carbon;

class GajiController extends Controller
{
    /**
     * ===============================
     * DATA GAJI USER LOGIN (BULAN INI)
     * ===============================
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $salary = $user->salary;

        if (!$salary || !$salary->aktif) {
            return response()->json([
                'data' => null,
                'message' => 'Data gaji belum tersedia'
            ]);
        }

        // ===============================
        // BULAN & TAHUN
        // ===============================
        $now   = Carbon::now();
        $bulan = $now->month;
        $tahun = $now->year;

        // ===============================
        // HITUNG JAM LEMBUR APPROVED
        // ===============================
        $totalJamLembur = Lembur::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->where('status', 'approved')
            ->get()
            ->sum(function ($item) {
                return Carbon::parse($item->jam_mulai)
                    ->diffInHours(Carbon::parse($item->jam_selesai));
            });

        // ===============================
        // HITUNG TOTAL LEMBUR & GAJI
        // ===============================
        $totalLembur = $totalJamLembur * ($salary->lembur_per_jam ?? 0);

        $totalGaji =
            $salary->gaji_pokok +
            $salary->uang_makan +
            $salary->transport +
            $totalLembur;

        return response()->json([
            'data' => [
                'bulan' => $now->translatedFormat('F Y'),

                'gaji_pokok' => $salary->gaji_pokok,
                'uang_makan' => $salary->uang_makan,
                'transport' => $salary->transport,
                'lembur_per_jam' => $salary->lembur_per_jam,

                'total_jam_lembur' => $totalJamLembur,
                'total_lembur' => $totalLembur,
                'total_gaji' => $totalGaji,
            ]
        ]);
    }
}
