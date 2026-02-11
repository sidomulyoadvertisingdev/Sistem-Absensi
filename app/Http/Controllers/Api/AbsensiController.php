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
     * SIMPAN ABSENSI (API)
     * ===============================
     */
    public function store(Request $request)
    {
        $request->validate([
            'aksi' => 'required|in:masuk,istirahat_mulai,istirahat_selesai,pulang',
            'jam'  => 'required|date_format:H:i',
        ]);

        $user = $request->user();
        $tanggal = Carbon::today()->toDateString();
        $jam = $request->jam;

        $absensi = Absensi::firstOrCreate(
            [
                'user_id' => $user->id,
                'tanggal' => $tanggal,
            ],
            [
                'status' => 'hadir',
                'menit_terlambat' => 0,
            ]
        );

        /**
         * ===============================
         * FOTO WAJIB UNTUK AKSI TERTENTU
         * ===============================
         */
        $fotoWajib = [
            'masuk',
            'istirahat_mulai',
            'istirahat_selesai',
            'pulang',
        ];

        if (in_array($request->aksi, $fotoWajib)) {
            if (!$request->hasFile('foto')) {
                return response()->json([
                    'message' => 'Foto wajib untuk aksi ini'
                ], 422);
            }

            $request->validate([
                'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            $absensi->foto = $request->file('foto')
                ->store('absensi', 'public');
        }

        /**
         * ===============================
         * AMBIL JADWAL KERJA SESUAI MODE
         * ===============================
         */
        $jadwal = $user->resolveWorkSchedule($tanggal);

        /**
         * ===============================
         * AKSI MASUK
         * ===============================
         */
        if ($request->aksi === 'masuk') {
            $absensi->jam_masuk = $jam;
        }

        /**
         * ===============================
         * ISTIRAHAT
         * ===============================
         */
        if ($request->aksi === 'istirahat_mulai') {
            $absensi->istirahat_mulai = $jam;
        }

        if ($request->aksi === 'istirahat_selesai') {
            $absensi->istirahat_selesai = $jam;
        }

        /**
         * ===============================
         * AKSI PULANG
         * ===============================
         */
        if ($request->aksi === 'pulang') {
            $absensi->jam_pulang = $jam;
        }

        /**
         * ===============================
         * FINAL STATUS + MENIT TERLAMBAT
         * ===============================
         */
        $menitTerlambat = $absensi->menit_terlambat ?? 0;
        $status = $absensi->status ?? 'hadir';

        if ($jadwal) {
            $menitTerlambat = 0;

            if ($absensi->jam_masuk && $jadwal->jam_masuk) {
                $jamMasuk = Carbon::parse(
                    $tanggal.' '.$absensi->jam_masuk
                );

                $batas = Carbon::parse(
                    $tanggal.' '.$jadwal->jam_masuk
                );

                if (!empty($jadwal->toleransi_masuk)) {
                    $batas->addMinutes($jadwal->toleransi_masuk);
                }

                if ($jamMasuk->gt($batas)) {
                    $menitTerlambat += $batas->diffInMinutes($jamMasuk);
                }
            }

            if ($absensi->jam_pulang && $jadwal->jam_pulang) {
                $jamPulang = Carbon::parse(
                    $tanggal.' '.$absensi->jam_pulang
                );

                $batas = Carbon::parse(
                    $tanggal.' '.$jadwal->jam_pulang
                );

                if ($jamPulang->lt($batas)) {
                    $menitTerlambat += $jamPulang->diffInMinutes($batas);
                }
            }

            $status = $menitTerlambat > 0 ? 'terlambat' : 'hadir';
        }

        $absensi->menit_terlambat = $menitTerlambat;
        $absensi->status = $status;

        $absensi->save();

        return response()->json([
            'message' => 'Absensi berhasil disimpan',
            'data' => $absensi,
        ]);
    }
}
