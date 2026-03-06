<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationApiAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_karyawan_token_cannot_access_attendance_report(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_KARYAWAN,
        ]);

        $token = $user->createToken('employee-app', [
            'integration.attendance.report.read',
        ])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/integrations/attendance/report');

        $response
            ->assertForbidden()
            ->assertJson([
                'status' => 'error',
                'message' => 'Akses ditolak.',
            ]);
    }

    public function test_panel_admin_token_with_report_ability_can_access_attendance_report(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_OWNER,
        ]);

        $token = $user->createToken('integration:test-report', [
            'integration.attendance.report.read',
        ])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/integrations/attendance/report');

        $response
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    public function test_panel_admin_token_without_write_ability_cannot_update_payroll(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_OWNER,
        ]);

        $employee = User::factory()->create([
            'role' => User::ROLE_KARYAWAN,
        ]);

        $token = $admin->createToken('integration:readonly-payroll', [
            'integration.attendance.payroll.read',
        ])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/integrations/attendance/salaries/' . $employee->id, [
                'gaji_pokok' => 5000000,
            ]);

        $response
            ->assertForbidden()
            ->assertJson([
                'status' => 'error',
                'message' => 'Token tidak memiliki izin untuk endpoint ini.',
            ]);
    }
}
