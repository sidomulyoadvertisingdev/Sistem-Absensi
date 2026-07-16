<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

class AttendanceRecorder
{
    /**
     * Normalisasi input jam (anti duplikasi tanggal, samakan pemisah waktu).
     */
    public function normalizeJamInput(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $jamInput = trim((string) $value);

        if ($jamInput === '') {
            return null;
        }

        // normalisasi input jam: hapus duplikasi tanggal dan samakan pemisah waktu
        $jamNormalized = preg_replace(
            '/^(\d{4}-\d{2}-\d{2})\s+\1\s+/',
            '$1 ',
            str_replace('.', ':', $jamInput)
        );

        try {
            return Carbon::parse($jamNormalized)->format('H:i:s');
        } catch (\Exception $e) {
            // fallback: ambil bagian jam saja bila string mengandung tanggal ganda/format tidak umum
            if (preg_match('/\b(\d{2}:\d{2}(?::\d{2})?)\b/', $jamNormalized, $matches)) {
                $format = strlen($matches[1]) === 5 ? 'H:i' : 'H:i:s';
                return Carbon::createFromFormat($format, $matches[1])->format('H:i:s');
            }
        }

        return null;
    }

    /**
     * Mencatat satu aksi absensi untuk user pada tanggal tertentu.
     *
     * @param  User            $user        Karyawan yang diabsen.
     * @param  string          $tanggal     Tanggal (Y-m-d).
     * @param  string          $aksi        masuk|istirahat_mulai|istirahat_selesai|pulang.
     * @param  string|null     $jam        Waktu aksi (bisa berupa time saja atau datetime).
     * @param  UploadedFile|null $foto      Foto opsional.
     * @param  bool            $requireFoto true untuk endpoint mobile, false untuk integrasi.
     * @return Absensi
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    public function record(
        User $user,
        string $tanggal,
        string $aksi,
        ?string $jam,
        ?UploadedFile $foto = null,
        bool $requireFoto = true
    ): Absensi {
        $jamNormalized = $this->normalizeJamInput($jam);

        if (!$jamNormalized) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'jam' => 'Format jam tidak valid',
            ]);
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

        if ($absensi->locked) {
            abort(423, 'Absensi pada tanggal tersebut sudah ditutup dan tidak bisa diubah.');
        }

        $fotoWajib = ['masuk', 'istirahat_mulai', 'istirahat_selesai', 'pulang'];

        if (in_array($aksi, $fotoWajib, true)) {
            if ($requireFoto && $foto === null && empty($absensi->foto)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'foto' => 'Foto wajib untuk aksi ini',
                ]);
            }

            if ($foto !== null) {
                validator(['foto' => $foto], [
                    'foto' => 'image|mimes:jpg,jpeg,png|max:2048',
                ])->validate();

                $absensi->foto = $foto->store('absensi', 'public');
            }
        }

        match ($aksi) {
            'masuk' => $absensi->jam_masuk = $jamNormalized,
            'istirahat_mulai' => $absensi->istirahat_mulai = $jamNormalized,
            'istirahat_selesai' => $absensi->istirahat_selesai = $jamNormalized,
            'pulang' => $absensi->jam_pulang = $jamNormalized,
        };

        $jadwal = $user->resolveWorkSchedule($tanggal);

        $menitTerlambat = 0;
        $status = 'hadir';

        if ($jadwal) {
            $absensiJamMasuk = $this->normalizeJamInput($absensi->jam_masuk ?? null);
            $absensiJamPulang = $this->normalizeJamInput($absensi->jam_pulang ?? null);
            $jadwalJamMasuk = $this->normalizeJamInput($jadwal->jam_masuk ?? null);
            $jadwalJamPulang = $this->normalizeJamInput($jadwal->jam_pulang ?? null);

            if ($absensiJamMasuk && $jadwalJamMasuk) {
                $jamMasuk = Carbon::createFromFormat('Y-m-d H:i:s', $tanggal . ' ' . $absensiJamMasuk);
                $batasMasuk = Carbon::createFromFormat('Y-m-d H:i:s', $tanggal . ' ' . $jadwalJamMasuk);

                if (!empty($jadwal->toleransi_masuk)) {
                    $batasMasuk->addMinutes($jadwal->toleransi_masuk);
                }

                if ($jamMasuk->gt($batasMasuk)) {
                    $menitTerlambat += $batasMasuk->diffInMinutes($jamMasuk);
                }
            }

            if ($absensiJamPulang && $jadwalJamPulang) {
                $jamPulang = Carbon::createFromFormat('Y-m-d H:i:s', $tanggal . ' ' . $absensiJamPulang);
                $batasPulang = Carbon::createFromFormat('Y-m-d H:i:s', $tanggal . ' ' . $jadwalJamPulang);

                if ($jamPulang->lt($batasPulang)) {
                    $menitTerlambat += $jamPulang->diffInMinutes($batasPulang);
                }
            }

            $status = $menitTerlambat > 0 ? 'terlambat' : 'hadir';
        }

        $absensi->menit_terlambat = $menitTerlambat;
        $absensi->status = $status;

        $absensi->save();

        return $absensi;
    }
}
