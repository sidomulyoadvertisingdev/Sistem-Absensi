<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkSchedule;

class JadwalController extends Controller
{
    /**
     * ===============================
     * JADWAL KERJA PER HARI (SENIN–MINGGU)
     * ===============================
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $jadwal = WorkSchedule::where('user_id', $user->id)
            ->where('aktif', true)
            ->get()
            ->keyBy('hari');

        // urutan hari fix
        $hariList = [
            'senin',
            'selasa',
            'rabu',
            'kamis',
            'jumat',
            'sabtu',
            'minggu',
        ];

        $result = [];

        foreach ($hariList as $hari) {
            if (isset($jadwal[$hari])) {
                $result[] = [
                    'hari' => $hari,
                    'jam_masuk' => $jadwal[$hari]->jam_masuk,
                    'jam_pulang' => $jadwal[$hari]->jam_pulang,
                    'istirahat_mulai' => $jadwal[$hari]->istirahat_mulai,
                    'istirahat_selesai' => $jadwal[$hari]->istirahat_selesai,
                ];
            } else {
                $result[] = [
                    'hari' => $hari,
                    'jam_masuk' => null,
                    'jam_pulang' => null,
                    'istirahat_mulai' => null,
                    'istirahat_selesai' => null,
                ];
            }
        }

        return response()->json([
            'data' => $result
        ]);
    }
}
