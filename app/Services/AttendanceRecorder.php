<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\AttendanceLocation;
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
     * @param  float|null      $latitude    Koordinat GPS aplikasi (untuk geofence).
     * @param  float|null      $longitude   Koordinat GPS aplikasi (untuk geofence).
     * @param  bool            $enforceGeofence true untuk endpoint mobile karyawan.
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
        bool $requireFoto = true,
        ?float $latitude = null,
        ?float $longitude = null,
        bool $enforceGeofence = false,
        ?float $accuracy = null
    ): Absensi {
        $jamNormalized = $this->normalizeJamInput($jam);

        if (!$jamNormalized) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'jam' => 'Format jam tidak valid',
            ]);
        }

        // ============================================================
        // GEOFENCE — absensi hanya boleh dari dalam radius lokasi
        // yang sudah ditentukan admin (khusus aplikasi karyawan).
        // ============================================================
        $matchedLocation = $enforceGeofence
            ? $this->resolveGeofenceLocation($user, $latitude, $longitude, $accuracy)
            : null;

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

        if ($matchedLocation) {
            $absensi->attendance_location_id = $matchedLocation->id;

            if ($aksi === 'masuk') {
                $absensi->lat_masuk = $latitude;
                $absensi->lng_masuk = $longitude;
            } elseif ($aksi === 'pulang') {
                $absensi->lat_pulang = $latitude;
                $absensi->lng_pulang = $longitude;
            }
        }

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
                    $menitTerlambat += (int) round($batasMasuk->diffInMinutes($jamMasuk));
                }
            }

            if ($absensiJamPulang && $jadwalJamPulang) {
                $jamPulang = Carbon::createFromFormat('Y-m-d H:i:s', $tanggal . ' ' . $absensiJamPulang);
                $batasPulang = Carbon::createFromFormat('Y-m-d H:i:s', $tanggal . ' ' . $jadwalJamPulang);

                if ($jamPulang->lt($batasPulang)) {
                    $menitTerlambat += (int) round($jamPulang->diffInMinutes($batasPulang));
                }
            }

            $status = $menitTerlambat > 0 ? 'terlambat' : 'hadir';
        }

        $absensi->menit_terlambat = $menitTerlambat;
        $absensi->status = $status;
        // Jaga kolom boolean `terlambat` tetap sinkron dengan status — sebelumnya
        // tidak pernah di-update sehingga nilainya selalu basi (default false).
        $absensi->terlambat = $status === 'terlambat';

        $absensi->save();

        return $absensi;
    }

    /**
     * Pastikan koordinat GPS berada dalam radius salah satu lokasi
     * absensi yang di-assign ke user. Melempar ValidationException
     * bila GPS kosong, user belum punya lokasi, atau di luar radius.
     */
    private function resolveGeofenceLocation(
        User $user,
        ?float $latitude,
        ?float $longitude,
        ?float $accuracy = null
    ): AttendanceLocation {
        if ($latitude === null || $longitude === null) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'lokasi' => 'Lokasi GPS tidak terbaca. Aktifkan izin lokasi di HP lalu coba lagi.',
            ]);
        }

        $locations = $user->attendanceLocations()
            ->where('aktif', true)
            ->get();

        if ($locations->isEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'lokasi' => 'Anda belum ditugaskan ke lokasi absensi manapun. Hubungi admin.',
            ]);
        }

        /** @var AttendanceLocation|null $nearest */
        $nearest = null;
        $nearestDistance = null;

        foreach ($locations as $location) {
            $distance = $location->distanceFrom($latitude, $longitude);

            if ($nearestDistance === null || $distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearest = $location;
            }
        }

        // Toleransi akurasi GPS (maks 50 m) supaya pembacaan GPS yang kurang
        // presisi — umum di perangkat desktop / dalam ruangan — tidak menolak
        // karyawan yang benar-benar berada di lokasi.
        $accuracyAllowance = 0.0;
        if ($accuracy !== null && $accuracy > 0) {
            $accuracyAllowance = min($accuracy, 50);
        }

        if ($nearestDistance > $nearest->radius + $accuracyAllowance) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'lokasi' => sprintf(
                    'Anda berada %s m dari lokasi "%s". Absensi hanya bisa dalam radius %d m.',
                    number_format($nearestDistance, 0, ',', '.'),
                    $nearest->nama,
                    $nearest->radius
                ),
            ]);
        }

        return $nearest;
    }
}
