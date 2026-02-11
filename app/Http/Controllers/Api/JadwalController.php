<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkSchedule;
use App\Models\WorkScheduleDate;

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
        $mode = $user->schedule_mode ?? 'per_hari';

        if ($mode === 'per_tanggal') {
            $jadwalTanggal = WorkScheduleDate::where('user_id', $user->id)
                ->orderBy('tanggal', 'asc')
                ->get();

            $result = [];

            foreach ($jadwalTanggal as $item) {
                $result[] = [
                    'tanggal' => optional($item->tanggal)->format('Y-m-d'),
                    'jam_masuk' => $item->jam_masuk,
                    'jam_pulang' => $item->jam_pulang,
                    'istirahat_mulai' => $item->istirahat_mulai,
                    'istirahat_selesai' => $item->istirahat_selesai,
                    'aktif' => (bool) $item->aktif,
                ];
            }

            return response()->json([
                'mode' => $mode,
                'data' => $result
            ]);
        }

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
            'mode' => $mode,
            'data' => $result
        ]);
    }
}
