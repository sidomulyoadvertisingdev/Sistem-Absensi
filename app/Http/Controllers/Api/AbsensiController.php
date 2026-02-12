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
        $request->validate([
            'aksi' => 'required|in:masuk,istirahat_mulai,istirahat_selesai,pulang',
            'jam'  => 'required',
        ]);

        $user = $request->user();
        $tanggal = Carbon::today()->toDateString();

        /**
         * ===================================
         * NORMALISASI JAM (ANTI DOUBLE DATE)
         * ===================================
         */
        $jamInput = trim((string) $request->jam);

        // normalisasi input jam: hapus duplikasi tanggal dan samakan pemisah waktu
        $jamNormalized = preg_replace(
            '/^(\d{4}-\d{2}-\d{2})\s+\1\s+/',
            '$1 ',
            str_replace('.', ':', $jamInput)
        );

        try {
            $jam = Carbon::parse($jamNormalized)->format('H:i:s');
        } catch (\Exception $e) {
            // fallback: ambil bagian jam saja bila string mengandung tanggal ganda/format tidak umum
            if (preg_match('/\b(\d{2}:\d{2}(?::\d{2})?)\b/', $jamNormalized, $matches)) {
                $format = strlen($matches[1]) === 5 ? 'H:i' : 'H:i:s';
                $jam = Carbon::createFromFormat($format, $matches[1])->format('H:i:s');
            } else {
                return response()->json([
                    'message' => 'Format jam tidak valid'
                ], 422);
            }
        }

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
         * FOTO WAJIB
         * ===============================
         */
        $fotoWajib = ['masuk', 'istirahat_mulai', 'istirahat_selesai', 'pulang'];

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
         * SET JAM BERDASARKAN AKSI
         * ===============================
         */
        match ($request->aksi) {
            'masuk' => $absensi->jam_masuk = $jam,
            'istirahat_mulai' => $absensi->istirahat_mulai = $jam,
            'istirahat_selesai' => $absensi->istirahat_selesai = $jam,
            'pulang' => $absensi->jam_pulang = $jam,
        };

        /**
         * ===============================
         * HITUNG TERLAMBAT
         * ===============================
         */
        $jadwal = $user->resolveWorkSchedule($tanggal);

        $menitTerlambat = 0;
        $status = 'hadir';

        if ($jadwal) {

            if ($absensi->jam_masuk && $jadwal->jam_masuk) {

                $jamMasuk = Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    $tanggal.' '.$absensi->jam_masuk
                );

                $batasMasuk = Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    $tanggal.' '.$jadwal->jam_masuk
                );

                if (!empty($jadwal->toleransi_masuk)) {
                    $batasMasuk->addMinutes($jadwal->toleransi_masuk);
                }

                if ($jamMasuk->gt($batasMasuk)) {
                    $menitTerlambat += $batasMasuk->diffInMinutes($jamMasuk);
                }
            }

            if ($absensi->jam_pulang && $jadwal->jam_pulang) {

                $jamPulang = Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    $tanggal.' '.$absensi->jam_pulang
                );

                $batasPulang = Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    $tanggal.' '.$jadwal->jam_pulang
                );

                if ($jamPulang->lt($batasPulang)) {
                    $menitTerlambat += $jamPulang->diffInMinutes($batasPulang);
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
