<?php

namespace Tests\Feature;

use App\Models\PeerReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PeerReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_karyawan_can_file_a_peer_report(): void
    {
        Storage::fake('public');
        $reporter = User::factory()->create(['role' => User::ROLE_KARYAWAN]);
        $target = User::factory()->create(['role' => User::ROLE_KARYAWAN]);

        $this->actingAs($reporter, 'sanctum')
            ->postJson('/api/peer-reports', [
                'reported_user_id' => $target->id,
                'kategori' => 'Kedisiplinan',
                'lokasi' => 'Gudang',
                'kronologi' => 'Datang terlambat berulang tanpa izin.',
                'bukti' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertCreated()
            ->assertJsonPath('status', true);

        $this->assertDatabaseHas('peer_reports', [
            'reporter_id' => $reporter->id,
            'reported_user_id' => $target->id,
            'kategori' => 'Kedisiplinan',
            'status' => 'pending',
        ]);
    }

    public function test_cannot_report_self(): void
    {
        $u = User::factory()->create(['role' => User::ROLE_KARYAWAN]);

        $this->actingAs($u, 'sanctum')
            ->postJson('/api/peer-reports', [
                'reported_user_id' => $u->id,
                'kategori' => 'Lainnya',
                'kronologi' => 'tidak valid tes',
            ])
            ->assertStatus(422);
    }

    public function test_about_me_only_returns_approved_and_hides_reporter(): void
    {
        $reporter = User::factory()->create(['role' => User::ROLE_KARYAWAN]);
        $target = User::factory()->create(['role' => User::ROLE_KARYAWAN]);

        PeerReport::create([
            'reporter_id' => $reporter->id, 'reported_user_id' => $target->id,
            'kategori' => 'Keamanan', 'kronologi' => 'x', 'status' => 'pending',
        ]);
        PeerReport::create([
            'reporter_id' => $reporter->id, 'reported_user_id' => $target->id,
            'kategori' => 'Sikap Kerja', 'kronologi' => 'sudah disetujui',
            'status' => 'approved', 'reviewed_at' => now(), 'catatan_admin' => 'valid',
        ]);

        $res = $this->actingAs($target, 'sanctum')->getJson('/api/peer-reports/about-me');

        $res->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('data.0.kategori', 'Sikap Kerja')
            ->assertJsonPath('data.0.catatan_admin', 'valid');

        $body = $res->json();
        $this->assertStringNotContainsStringIgnoringCase('reporter', json_encode($body));
        $this->assertArrayNotHasKey('reporter_id', $body['data'][0]);
    }

    public function test_admin_approve_makes_it_visible_to_target(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_OWNER]);
        $reporter = User::factory()->create(['role' => User::ROLE_KARYAWAN]);
        $target = User::factory()->create(['role' => User::ROLE_KARYAWAN]);

        $report = PeerReport::create([
            'reporter_id' => $reporter->id, 'reported_user_id' => $target->id,
            'kategori' => 'Kualitas Kerja', 'kronologi' => 'hasil kerja tidak sesuai standar', 'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.peer-reports.approve', $report), ['catatan_admin' => 'terbukti'])
            ->assertRedirect();

        $this->assertDatabaseHas('peer_reports', [
            'id' => $report->id, 'status' => 'approved', 'reviewed_by' => $admin->id,
        ]);

        $this->actingAs($target, 'sanctum')
            ->getJson('/api/peer-reports/about-me')
            ->assertOk()
            ->assertJsonPath('count', 1);
    }
}
