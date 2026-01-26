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
            ]
        );

        /**
         * ===============================
         * FOTO WAJIB UNTUK AKSI TERTENTU
         * ===============================
         */
        $fotoWajib = ['masuk', 'istirahat_selesai', 'pulang'];

        if (in_array($request->aksi, $fotoWajib)) {
            if (!$request->hasFile('foto')) {
                return response()->json([
                    'message' => 'Foto wajib untuk aksi ini'
                ], 422);
            }

            $request->validate([
                'foto' => 'image|mimes:jpg,jpeg,png|max:2048',
            ]);

            $absensi->foto = $request->file('foto')
                ->store('absensi', 'public');
        }

        /**
         * ===============================
         * AMBIL JADWAL KERJA HARI INI
         * ===============================
         */
        $hari = strtolower(
            Carbon::parse($tanggal)->locale('id')->isoFormat('dddd')
        );

        $jadwal = WorkSchedule::where('user_id', $user->id)
            ->where('hari', $hari)
            ->where('aktif', true)
            ->first();

        /**
         * ===============================
         * AKSI MASUK
         * ===============================
         */
        if ($request->aksi === 'masuk') {

            $jamMasuk = Carbon::createFromFormat(
                'Y-m-d H:i',
                $tanggal . ' ' . $jam
            );

            $status = 'hadir';

            if ($jadwal && $jadwal->jam_masuk) {
                $batasMasuk = Carbon::parse(
                    $tanggal . ' ' . $jadwal->jam_masuk
                );

                if (!empty($jadwal->toleransi_masuk)) {
                    $batasMasuk->addMinutes($jadwal->toleransi_masuk);
                }

                if ($jamMasuk->gt($batasMasuk)) {
                    $status = 'terlambat';
                }
            }

            $absensi->jam_masuk = $jam;
            $absensi->status = $status;
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

            $jamPulang = Carbon::createFromFormat(
                'Y-m-d H:i',
                $tanggal . ' ' . $jam
            );

            if ($jadwal && $jadwal->jam_pulang) {
                $batasPulang = Carbon::parse(
                    $tanggal . ' ' . $jadwal->jam_pulang
                );

                if ($jamPulang->lt($batasPulang)) {
                    $absensi->status = 'terlambat';
                }
            }

            $absensi->jam_pulang = $jam;
        }

        $absensi->save();

        return response()->json([
            'message' => 'Absensi berhasil disimpan',
            'data' => $absensi,
        ]);
    }
}
