<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AttendanceRecorder;
use Illuminate\Http\Request;
use App\Models\Absensi;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    public function __construct(
        protected AttendanceRecorder $recorder
    ) {
    }

    /**
     * ===============================
     * STATUS ABSENSI HARI INI
     * ===============================
     */
    public function today(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today()->toDateString();

        $absensi = Absensi::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        return response()->json([
            'tanggal' => $today,
            'status' => $absensi?->status ?? 'belum_absen',
            'jam_masuk' => $absensi?->jam_masuk,
            'istirahat_mulai' => $absensi?->istirahat_mulai,
            'istirahat_selesai' => $absensi?->istirahat_selesai,
            'jam_pulang' => $absensi?->jam_pulang,
        ]);
    }

    /**
     * ===============================
     * SIMPAN ABSENSI
     * ===============================
     */
    public function store(Request $request)
    {
        $request->validate([
            'aksi' => 'required|in:masuk,istirahat_mulai,istirahat_selesai,pulang',
            'jam'  => 'required',
        ]);

        $user = $request->user();
        $tanggal = Carbon::today()->toDateString();

        try {
            $absensi = $this->recorder->record(
                user: $user,
                tanggal: $tanggal,
                aksi: $request->aksi,
                jam: $request->jam,
                foto: $request->file('foto'),
                requireFoto: true
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => collect($e->errors())->first()[0] ?? 'Validasi gagal',
            ], 422);
        }

        // Kirim notifikasi ke aplikasi eksternal (webhook outbound).
        event(new \App\Events\AttendanceRecorded($absensi, $user, $request->aksi));

        return response()->json([
            'message' => 'Absensi berhasil disimpan',
            'data' => $absensi,
        ]);
    }
}
