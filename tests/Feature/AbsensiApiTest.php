<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Services\AttendanceRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsensiApiTest extends TestCase
{
    use RefreshDatabase;

    private function actingKaryawan(array $attrs = []): User
    {
        $user = User::factory()->create(array_merge([
            'role' => User::ROLE_KARYAWAN,
            'schedule_mode' => 'per_hari',
        ], $attrs));
        $this->actingAs($user, 'sanctum');
        return $user;
    }

    public function test_history_returns_month_records_with_is_late_flag(): void
    {
        $user = $this->actingKaryawan();

        Absensi::create([
            'user_id' => $user->id,
            'tanggal' => '2026-09-03',
            'jam_masuk' => '08:00:00',
            'status' => 'hadir',
            'menit_terlambat' => 0,
        ]);
        Absensi::create([
            'user_id' => $user->id,
            'tanggal' => '2026-09-10',
            'jam_masuk' => '08:30:00',
            'status' => 'terlambat',
            'menit_terlambat' => 30,
        ]);
        // Bulan lain -> tidak boleh ikut.
        Absensi::create([
            'user_id' => $user->id,
            'tanggal' => '2026-08-15',
            'jam_masuk' => '07:55:00',
            'status' => 'hadir',
        ]);

        $res = $this->getJson('/api/absensi/history?bulan=2026-09')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $data = collect($res->json('data'))->keyBy('tanggal');

        $this->assertFalse($data['2026-09-03']['is_late']);
        $this->assertSame(0, $data['2026-09-03']['menit_terlambat']);
        $this->assertTrue($data['2026-09-10']['is_late']);
        $this->assertSame(30, $data['2026-09-10']['menit_terlambat']);
    }

    public function test_history_infers_is_late_from_menit_even_if_status_stale(): void
    {
        $user = $this->actingKaryawan();

        // status "hadir" tapi menit_terlambat > 0 (data lama tak konsisten).
        Absensi::create([
            'user_id' => $user->id,
            'tanggal' => now(config('app.attendance_timezone'))->toDateString(),
            'jam_masuk' => '09:15:00',
            'status' => 'hadir',
            'menit_terlambat' => 15,
        ]);

        $this->getJson('/api/absensi/history')
            ->assertOk()
            ->assertJsonPath('data.0.is_late', true);
    }

    public function test_today_exposes_is_late_and_schedule(): void
    {
        $user = $this->actingKaryawan();

        $hari = now(config('app.attendance_timezone'))->locale('id')->isoFormat('dddd');
        WorkSchedule::create([
            'user_id' => $user->id,
            'hari' => strtolower($hari),
            'jam_masuk' => '08:00:00',
            'jam_pulang' => '17:00:00',
            'aktif' => true,
        ]);

        Absensi::create([
            'user_id' => $user->id,
            'tanggal' => now(config('app.attendance_timezone'))->toDateString(),
            'jam_masuk' => '08:45:00',
            'status' => 'terlambat',
            'menit_terlambat' => 45,
        ]);

        $this->getJson('/api/absensi/today')
            ->assertOk()
            ->assertJsonPath('is_late', true)
            ->assertJsonPath('menit_terlambat', 45)
            ->assertJsonPath('schedule_in', '08:00:00')
            ->assertJsonPath('schedule_out', '17:00:00');
    }

    public function test_recorder_keeps_terlambat_boolean_in_sync_with_status(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_KARYAWAN,
            'schedule_mode' => 'per_hari',
        ]);

        $tanggal = '2026-09-10'; // Kamis
        WorkSchedule::create([
            'user_id' => $user->id,
            'hari' => 'kamis',
            'jam_masuk' => '08:00:00',
            'jam_pulang' => '17:00:00',
            'aktif' => true,
        ]);

        /** @var AttendanceRecorder $recorder */
        $recorder = app(AttendanceRecorder::class);

        $absensi = $recorder->record(
            user: $user,
            tanggal: $tanggal,
            aksi: 'masuk',
            jam: '09:30:00',
            requireFoto: false,
        );

        $this->assertSame('terlambat', $absensi->status);
        $this->assertSame(90, (int) $absensi->menit_terlambat);
        $this->assertTrue((bool) $absensi->fresh()->terlambat);
    }

    public function test_recorder_marks_on_time_when_within_schedule(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_KARYAWAN,
            'schedule_mode' => 'per_hari',
        ]);

        WorkSchedule::create([
            'user_id' => $user->id,
            'hari' => 'kamis',
            'jam_masuk' => '08:00:00',
            'jam_pulang' => '17:00:00',
            'aktif' => true,
        ]);

        $absensi = app(AttendanceRecorder::class)->record(
            user: $user,
            tanggal: '2026-09-10',
            aksi: 'masuk',
            jam: '07:55:00',
            requireFoto: false,
        );

        $this->assertSame('hadir', $absensi->status);
        $this->assertSame(0, (int) $absensi->menit_terlambat);
        $this->assertFalse((bool) $absensi->fresh()->terlambat);
    }
}
