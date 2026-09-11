<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AttendanceRecorder;
use Illuminate\Http\Request;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

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
        // Tanggal WIB, bukan UTC — jam 00:00–07:00 WIB masih UTC "kemarin".
        $today = Carbon::now(config('app.attendance_timezone'))->toDateString();

        $absensi = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        $jadwal = $user->resolveWorkSchedule($today);

        return response()->json([
            'tanggal' => $today,
            'status' => $absensi?->status ?? 'belum_absen',
            // Boolean eksplisit supaya klien tidak perlu menebak dari jam.
            'is_late' => (bool) ($absensi && ($absensi->status === 'terlambat' || (int) $absensi->menit_terlambat > 0)),
            'menit_terlambat' => (int) ($absensi?->menit_terlambat ?? 0),
            'jam_masuk' => $absensi?->jam_masuk,
            'istirahat_mulai' => $absensi?->istirahat_mulai,
            'istirahat_selesai' => $absensi?->istirahat_selesai,
            'jam_pulang' => $absensi?->jam_pulang,
            'schedule_in' => $jadwal->jam_masuk ?? null,
            'schedule_out' => $jadwal->jam_pulang ?? null,
            'locations' => $this->allowedLocations($user),
        ]);
    }

    /**
     * ===============================
     * RIWAYAT ABSENSI (PER BULAN)
     * ===============================
     * GET /absensi/history?bulan=YYYY-MM  (default: bulan berjalan WIB)
     */
    public function history(Request $request)
    {
        $user = $request->user();
        $tz = config('app.attendance_timezone');

        $bulan = (string) $request->query('bulan', '');
        try {
            $base = preg_match('/^\d{4}-\d{2}$/', $bulan)
                ? Carbon::createFromFormat('Y-m-d', $bulan . '-01', $tz)->startOfMonth()
                : Carbon::now($tz);
        } catch (\Exception $e) {
            $base = Carbon::now($tz);
        }

        $items = Absensi::where('user_id', $user->id)
            ->whereYear('tanggal', $base->year)
            ->whereMonth('tanggal', $base->month)
            ->orderByDesc('tanggal')
            ->get();

        $data = $items->map(function (Absensi $a) {
            $tanggal = $a->tanggal instanceof \Carbon\CarbonInterface
                ? $a->tanggal->toDateString()
                : (string) $a->tanggal;

            return [
                'id' => $a->id,
                'tanggal' => $tanggal,
                'jam_masuk' => $a->jam_masuk,
                'istirahat_mulai' => $a->istirahat_mulai,
                'istirahat_selesai' => $a->istirahat_selesai,
                'jam_pulang' => $a->jam_pulang,
                'status' => $a->status,
                'is_late' => $a->status === 'terlambat' || (int) $a->menit_terlambat > 0,
                'menit_terlambat' => (int) $a->menit_terlambat,
                'foto_url' => $a->foto_url,
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * ===============================
     * DAFTAR LOKASI ABSENSI USER
     * ===============================
     * Dipakai aplikasi untuk menampilkan titik & radius yang diizinkan.
     */
    public function locations(Request $request)
    {
        return response()->json([
            'data' => $this->allowedLocations($request->user()),
        ]);
    }

    /**
     * Lokasi absensi aktif yang di-assign ke user.
     */
    private function allowedLocations($user): array
    {
        return $user->attendanceLocations()
            ->where('aktif', true)
            ->orderBy('nama')
            ->get(['attendance_locations.id', 'nama', 'alamat', 'latitude', 'longitude', 'radius'])
            ->map(fn ($loc) => [
                'id'        => $loc->id,
                'nama'      => $loc->nama,
                'alamat'    => $loc->alamat,
                'latitude'  => (float) $loc->latitude,
                'longitude' => (float) $loc->longitude,
                'radius'    => (int) $loc->radius,
            ])
            ->all();
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
            // `jam` dari klien tidak lagi dipakai untuk perhitungan (rawan salah
            // zona / dimanipulasi) — tetap diterima untuk kompatibilitas.
            'jam'  => 'nullable',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy'  => 'nullable|numeric|min:0',
        ], [
            'latitude.required'  => 'Lokasi GPS tidak terbaca. Aktifkan izin lokasi di HP lalu coba lagi.',
            'longitude.required' => 'Lokasi GPS tidak terbaca. Aktifkan izin lokasi di HP lalu coba lagi.',
        ]);

        $user = $request->user();
        // Sumber kebenaran waktu = jam server (WIB), bukan kiriman perangkat.
        $now = Carbon::now(config('app.attendance_timezone'));
        $tanggal = $now->toDateString();

        try {
            $absensi = $this->recorder->record(
                user: $user,
                tanggal: $tanggal,
                aksi: $request->aksi,
                jam: $now->format('H:i:s'),
                foto: $request->file('foto'),
                requireFoto: true,
                latitude: (float) $request->latitude,
                longitude: (float) $request->longitude,
                enforceGeofence: true,
                accuracy: $request->filled('accuracy') ? (float) $request->accuracy : null
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

    /**
     * ===============================
     * CEK "BADAN PENUH" (ABSENSI KIOS)
     * ===============================
     * Terima 1 frame kamera resolusi rendah, jalankan pose detection di server.
     * Foto TIDAK disimpan. Bila detektor tidak tersedia -> available:false
     * (aplikasi otomatis jatuh ke foto manual).
     */
    public function checkFullBody(Request $request)
    {
        if (! config('services.pose.enabled', true)) {
            Log::info('[pose] deteksi dimatikan via config services.pose.enabled=false');
            return response()->json(['available' => false, 'reason' => 'disabled']);
        }

        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
        ]);

        $python  = (string) config('services.pose.python_bin', 'python3');
        $script  = (string) config('services.pose.script_path', base_path('scripts/pose/detect_fullbody.py'));
        $timeout = (int) config('services.pose.timeout', 15);

        if (! is_file($script)) {
            Log::warning('[pose] script tidak ditemukan', ['path' => $script, 'python_bin' => $python]);
            return response()->json(['available' => false, 'reason' => 'script_missing']);
        }

        $tmp = tempnam(sys_get_temp_dir(), 'pose_') . '.jpg';

        try {
            $request->file('foto')->move(dirname($tmp), basename($tmp));

            $result = Process::timeout($timeout)->run([$python, $script, $tmp]);

            if (! $result->successful()) {
                Log::warning('[pose] script gagal', [
                    'exit' => $result->exitCode(),
                    'stderr' => mb_substr($result->errorOutput(), 0, 500),
                ]);
                return response()->json(['available' => false, 'reason' => 'exec_failed']);
            }

            $json = json_decode(trim($result->output()), true);
            if (! is_array($json)) {
                Log::warning('[pose] output bukan JSON', ['out' => mb_substr($result->output(), 0, 500)]);
                return response()->json(['available' => false, 'reason' => 'bad_output']);
            }

            if (($json['available'] ?? true) === false) {
                Log::warning('[pose] script balas available:false', [
                    'reason' => $json['reason'] ?? null,
                    'detail' => $json['detail'] ?? null,
                ]);
            }

            return response()->json($json);
        } catch (\Throwable $e) {
            Log::warning('[pose] exception', ['error' => $e->getMessage()]);
            return response()->json(['available' => false, 'reason' => 'exception']);
        } finally {
            if (is_file($tmp)) {
                @unlink($tmp);
            }
        }
    }
}
