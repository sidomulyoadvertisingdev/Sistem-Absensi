<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class CheckFullBodyTest extends TestCase
{
    use RefreshDatabase;

    private function actingKaryawan(): User
    {
        $user = User::factory()->create(['role' => User::ROLE_KARYAWAN]);
        $this->actingAs($user, 'sanctum');
        return $user;
    }

    public function test_returns_pose_script_result_verbatim(): void
    {
        config([
            'services.pose.enabled' => true,
            'services.pose.script_path' => base_path('scripts/pose/detect_fullbody.py'),
        ]);

        Process::fake([
            '*' => Process::result(
                output: json_encode([
                    'available' => true,
                    'full_body' => true,
                    'score' => 0.91,
                    'reasons' => [],
                    'person_count' => 1,
                    'coverage' => ['head_y' => 0.1, 'feet_y' => 0.95],
                ]),
            ),
        ]);

        $this->actingKaryawan();

        $this->postJson('/api/absensi/check-fullbody', [
            'foto' => UploadedFile::fake()->image('peek.jpg', 320, 480),
        ])
            ->assertOk()
            ->assertJsonPath('available', true)
            ->assertJsonPath('full_body', true)
            ->assertJsonPath('score', 0.91);
    }

    public function test_reports_reasons_when_not_full_body(): void
    {
        config(['services.pose.enabled' => true, 'services.pose.script_path' => base_path('scripts/pose/detect_fullbody.py')]);

        Process::fake([
            '*' => Process::result(output: json_encode([
                'available' => true,
                'full_body' => false,
                'score' => 0.2,
                'reasons' => ['kaki_tidak_terlihat', 'mundur_agar_kaki_masuk_frame'],
                'person_count' => 1,
            ])),
        ]);

        $this->actingKaryawan();

        $this->postJson('/api/absensi/check-fullbody', [
            'foto' => UploadedFile::fake()->image('peek.jpg'),
        ])
            ->assertOk()
            ->assertJsonPath('full_body', false)
            ->assertJsonFragment(['reasons' => ['kaki_tidak_terlihat', 'mundur_agar_kaki_masuk_frame']]);
    }

    public function test_available_false_when_script_missing(): void
    {
        config([
            'services.pose.enabled' => true,
            'services.pose.script_path' => base_path('scripts/pose/__does_not_exist__.py'),
        ]);

        $this->actingKaryawan();

        $this->postJson('/api/absensi/check-fullbody', [
            'foto' => UploadedFile::fake()->image('peek.jpg'),
        ])
            ->assertOk()
            ->assertJsonPath('available', false)
            ->assertJsonPath('reason', 'script_missing');
    }

    public function test_available_false_when_disabled(): void
    {
        config(['services.pose.enabled' => false]);
        $this->actingKaryawan();

        $this->postJson('/api/absensi/check-fullbody', [
            'foto' => UploadedFile::fake()->image('peek.jpg'),
        ])
            ->assertOk()
            ->assertJsonPath('available', false)
            ->assertJsonPath('reason', 'disabled');
    }

    public function test_available_false_when_script_fails(): void
    {
        config(['services.pose.enabled' => true, 'services.pose.script_path' => base_path('scripts/pose/detect_fullbody.py')]);

        Process::fake(['*' => Process::result(output: '', errorOutput: 'boom', exitCode: 1)]);

        $this->actingKaryawan();

        $this->postJson('/api/absensi/check-fullbody', [
            'foto' => UploadedFile::fake()->image('peek.jpg'),
        ])
            ->assertOk()
            ->assertJsonPath('available', false)
            ->assertJsonPath('reason', 'exec_failed');
    }

    public function test_requires_foto(): void
    {
        config(['services.pose.enabled' => true]);
        $this->actingKaryawan();

        $this->postJson('/api/absensi/check-fullbody', [])
            ->assertStatus(422);
    }
}
