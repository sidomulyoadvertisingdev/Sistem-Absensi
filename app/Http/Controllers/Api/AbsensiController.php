<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\WorkSchedule;
use Carbon\Carbon;

class AbsensiController extends Controller
{
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
     * SIMPAN ABSENSI (MASUK / ISTIRAHAT / PULANG)
     * ===============================
     */
    public function store(Request $request)
    {
        $request->validate([
            'aksi' => 'required|in:masuk,istirahat_mulai,istirahat_selesai,pulang',
            'jam'  => 'required',
            'foto' => 'nullable|image|max:2048',
        ]);

        $user = $request->user();
        $tanggal = Carbon::today()->toDateString();
        $jam = Carbon::parse($request->jam)->format('H:i');

        $absensi = Absensi::firstOrCreate(
            [
                'user_id' => $user->id,
                'tanggal' => $tanggal,
            ],
            [
                'status' => 'hadir',
            ]
        );

        /**
         * ===============================
         * VALIDASI FOTO WAJIB
         * ===============================
         */
        $fotoWajib = ['masuk', 'istirahat_selesai', 'pulang'];

        if (in_array($request->aksi, $fotoWajib) && !$request->hasFile('foto')) {
            return response()->json([
                'message' => 'Foto wajib untuk aksi ini'
            ], 422);
        }

        /**
         * ===============================
         * SIMPAN FOTO
         * ===============================
         */
        if ($request->hasFile('foto')) {
            $absensi->foto = $request->file('foto')
                ->store('absensi', 'public');
        }

        /**
         * ===============================
         * SIMPAN AKSI
         * ===============================
         */
        if ($request->aksi === 'masuk') {
            $absensi->jam_masuk = $jam;
            $absensi->status = 'hadir';
        }

        if ($request->aksi === 'istirahat_mulai') {
            $absensi->istirahat_mulai = $jam;
        }

        if ($request->aksi === 'istirahat_selesai') {
            $absensi->istirahat_selesai = $jam;
        }

        if ($request->aksi === 'pulang') {
            $absensi->jam_pulang = $jam;
        }

        $absensi->save();

        return response()->json([
            'message' => 'Absensi berhasil disimpan',
            'data' => $absensi,
        ]);
    }
}
