<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
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
     * SIMPAN ABSENSI
     * ===============================
     */
    public function store(Request $request)
    {
        // ✅ VALIDASI DASAR (TANPA FOTO)
        $request->validate([
            'aksi' => 'required|in:masuk,istirahat_mulai,istirahat_selesai,pulang',
            'jam'  => 'required',
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
         * FOTO WAJIB UNTUK AKSI TERTENTU
         * ===============================
         */
        $fotoWajib = ['masuk', 'istirahat_selesai', 'pulang'];

        if (in_array($request->aksi, $fotoWajib)) {

            // ❌ TIDAK ADA FOTO
            if (!$request->hasFile('foto')) {
                return response()->json([
                    'message' => 'Foto wajib untuk aksi ini'
                ], 422);
            }

            // ✅ VALIDASI FOTO (SETELAH PASTI ADA)
            $request->validate([
                'foto' => 'image|mimes:jpg,jpeg,png|max:2048',
            ]);

            // ✅ SIMPAN FOTO
            $absensi->foto = $request->file('foto')
                ->store('absensi', 'public');
        }

        /**
         * ===============================
         * SIMPAN JAM SESUAI AKSI
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
