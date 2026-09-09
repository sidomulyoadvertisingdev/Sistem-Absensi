<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Submission;
use App\Models\SubmissionType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionApiTest extends TestCase
{
    use RefreshDatabase;

    private function karyawan(): User
    {
        $u = User::factory()->create(['role' => User::ROLE_KARYAWAN]);
        $this->actingAs($u, 'sanctum');
        return $u;
    }

    public function test_types_returns_all_active_including_lampiran(): void
    {
        $this->karyawan();
        SubmissionType::create(['kode' => 'CUTI', 'nama' => 'Cuti', 'butuh_alasan' => true, 'butuh_lampiran' => false, 'is_izin_pulang_awal' => false, 'aktif' => true]);
        SubmissionType::create(['kode' => 'IZIN', 'nama' => 'Izin', 'butuh_alasan' => true, 'butuh_lampiran' => true, 'is_izin_pulang_awal' => false, 'aktif' => true]);
        SubmissionType::create(['kode' => 'OFF', 'nama' => 'Nonaktif', 'butuh_alasan' => false, 'butuh_lampiran' => false, 'is_izin_pulang_awal' => false, 'aktif' => false]);

        $this->getJson('/api/submission-types')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['kode' => 'IZIN']);
    }

    public function test_store_json_without_lampiran(): void
    {
        $this->karyawan();
        $type = SubmissionType::create(['kode' => 'CUTI', 'nama' => 'Cuti', 'butuh_alasan' => true, 'butuh_lampiran' => false, 'is_izin_pulang_awal' => false, 'aktif' => true]);

        $this->postJson('/api/submissions', [
            'submission_type_id' => $type->id,
            'alasan' => 'Acara keluarga',
        ])
            ->assertCreated()
            ->assertJsonPath('status', true);

        $this->assertDatabaseHas('submissions', [
            'submission_type_id' => $type->id,
            'kode' => 'CUTI',
            'status' => 'pending',
        ]);
    }

    public function test_store_multipart_with_lampiran(): void
    {
        Storage::fake('public');
        $this->karyawan();
        $type = SubmissionType::create(['kode' => 'IZIN', 'nama' => 'Izin', 'butuh_alasan' => true, 'butuh_lampiran' => true, 'is_izin_pulang_awal' => false, 'aktif' => true]);

        $this->post('/api/submissions', [
            'submission_type_id' => $type->id,
            'alasan' => 'Sakit, ada surat dokter',
            'lampiran' => UploadedFile::fake()->image('surat.jpg'),
        ], ['Accept' => 'application/json'])
            ->assertCreated();

        $row = Submission::first();
        $this->assertNotNull($row->lampiran);
        Storage::disk('public')->assertExists($row->lampiran);
    }

    public function test_lampiran_required_returns_422(): void
    {
        $this->karyawan();
        $type = SubmissionType::create(['kode' => 'IZIN', 'nama' => 'Izin', 'butuh_alasan' => true, 'butuh_lampiran' => true, 'is_izin_pulang_awal' => false, 'aktif' => true]);

        $this->postJson('/api/submissions', [
            'submission_type_id' => $type->id,
            'alasan' => 'tanpa lampiran',
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Lampiran wajib diunggah');
    }

    public function test_izin_pulang_awal_requires_clock_in(): void
    {
        $u = $this->karyawan();
        $type = SubmissionType::create(['kode' => 'IPA', 'nama' => 'Izin Pulang Awal', 'butuh_alasan' => true, 'butuh_lampiran' => false, 'is_izin_pulang_awal' => true, 'aktif' => true]);

        // belum absen masuk
        $this->postJson('/api/submissions', ['submission_type_id' => $type->id, 'alasan' => 'urusan'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Pengajuan ini hanya bisa dibuat setelah absen masuk.');

        // sudah absen masuk
        Absensi::create(['user_id' => $u->id, 'tanggal' => now()->toDateString(), 'jam_masuk' => '08:00:00', 'status' => 'hadir']);
        $this->postJson('/api/submissions', ['submission_type_id' => $type->id, 'alasan' => 'urusan'])
            ->assertCreated();
    }
}
