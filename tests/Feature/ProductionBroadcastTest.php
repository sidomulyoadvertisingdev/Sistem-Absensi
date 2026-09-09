<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\JobTodo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductionBroadcastTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.erp.base_url' => 'https://erp.test/api',
            'services.erp.api_key' => 'key',
            'services.erp.api_secret' => 'secret',
            'services.erp.production_jobs_enabled' => true,
        ]);
    }

    private function ingestToken(): string
    {
        $admin = User::factory()->create(['role' => User::ROLE_OWNER]);

        return $admin->createToken('erp', ['integration.production.items.write'])->plainTextToken;
    }

    private function helper(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => User::ROLE_KARYAWAN,
            'can_close_production' => true,
            'erp_outlet_id' => 7,
        ], $overrides));
    }

    private function itemPayload(array $overrides = []): array
    {
        return array_merge([
            'erp_item_id' => 5001,
            'erp_order_id' => 900,
            'invoice_no' => 'INV/2026/09/01',
            'outlet_id' => 7,
            'customer_name' => 'PT Uji',
            'product_name' => 'Banner 440gr',
            'qty' => '3',
            'production_status' => 'in_production',
        ], $overrides);
    }

    public function test_ingest_creates_broadcast_production_job(): void
    {
        $res = $this->withHeader('Authorization', 'Bearer ' . $this->ingestToken())
            ->postJson('/api/integrations/production/items', [
                'event' => 'entered_production',
                'items' => [$this->itemPayload()],
            ]);

        $res->assertOk()->assertJsonPath('result.created', 1);

        $this->assertDatabaseHas('job_todos', [
            'type' => 'production',
            'erp_item_id' => 5001,
            'outlet_id' => 7,
            'status' => 'open',
            'broadcast' => true,
        ]);
    }

    public function test_ingest_completed_event_closes_job(): void
    {
        $token = $this->ingestToken();
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/integrations/production/items', ['items' => [$this->itemPayload()]])
            ->assertOk();

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/integrations/production/items', [
                'event' => 'completed',
                'items' => [$this->itemPayload()],
            ])->assertOk()->assertJsonPath('result.closed', 1);

        $this->assertDatabaseHas('job_todos', ['erp_item_id' => 5001, 'status' => 'closed']);
    }

    public function test_helper_without_permission_cannot_take_production_job(): void
    {
        $job = JobTodo::create([
            'type' => 'production', 'source' => 'pos', 'erp_item_id' => 42, 'outlet_id' => 7,
            'title' => 'Banner', 'bonus' => 0, 'broadcast' => true, 'status' => 'open',
        ]);
        $user = $this->helper(['can_close_production' => false]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/job-todos/' . $job->id . '/take')
            ->assertStatus(403);
    }

    public function test_helper_can_take_production_job_in_own_outlet(): void
    {
        $job = JobTodo::create([
            'type' => 'production', 'source' => 'pos', 'erp_item_id' => 43, 'erp_order_id' => 1, 'outlet_id' => 7,
            'title' => 'Banner', 'bonus' => 0, 'broadcast' => true, 'status' => 'open',
        ]);
        $user = $this->helper();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/job-todos/' . $job->id . '/take')
            ->assertOk();

        $this->assertSame('in_progress', $job->fresh()->status);
        $this->assertDatabaseHas('job_todo_user', [
            'job_todo_id' => $job->id, 'user_id' => $user->id, 'status' => 'accepted',
        ]);
    }

    public function test_complete_requires_clock_in(): void
    {
        $user = $this->helper();
        $job = $this->claimedJob($user);

        Storage::fake('public');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/production/jobs/' . $job->id . '/complete', [
                'foto' => UploadedFile::fake()->image('s.jpg'),
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Anda harus absen masuk dulu sebelum menutup produksi.');
    }

    public function test_complete_happy_path_proxies_to_erp_and_logs(): void
    {
        Storage::fake('public');
        Http::fake([
            'erp.test/*' => Http::response([
                'success' => true,
                'order_completed' => true,
                'order_progress' => ['total' => 1, 'completed' => 1],
                'item' => ['id' => 43, 'production_status' => 'completed'],
                'order_status' => 'ready_pickup',
            ], 200),
        ]);

        $user = $this->helper();
        Absensi::create([
            'user_id' => $user->id,
            'tanggal' => now()->toDateString(),
            'jam_masuk' => '08:00:00',
            'status' => 'hadir',
        ]);
        $job = $this->claimedJob($user);

        $res = $this->actingAs($user, 'sanctum')
            ->postJson('/api/production/jobs/' . $job->id . '/complete', [
                'foto' => UploadedFile::fake()->image('s.jpg'),
                'latitude' => -7.55,
                'longitude' => 110.8,
                'note' => 'QC ok',
            ]);

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('order_completed', true);

        $this->assertSame('done', $job->fresh()->status);
        $this->assertDatabaseHas('job_todo_user', [
            'job_todo_id' => $job->id, 'user_id' => $user->id, 'status' => 'completed',
        ]);
        $this->assertDatabaseHas('production_close_logs', [
            'job_todo_id' => $job->id, 'user_id' => $user->id, 'erp_item_id' => $job->erp_item_id, 'order_completed' => true,
        ]);
    }

    public function test_my_stats_counts_completed_jobs(): void
    {
        $user = $this->helper();

        \App\Models\ProductionCloseLog::create([
            'user_id' => $user->id, 'erp_item_id' => 1, 'erp_order_id' => 900,
            'invoice_no' => 'INV/1', 'outlet_id' => 7, 'order_completed' => true,
        ]);
        \App\Models\ProductionCloseLog::create([
            'user_id' => $user->id, 'erp_item_id' => 2, 'erp_order_id' => 900,
            'invoice_no' => 'INV/1', 'outlet_id' => 7, 'order_completed' => false,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/production/my-stats')
            ->assertOk()
            ->assertJsonPath('completed_total', 2)
            ->assertJsonPath('completed_today', 2)
            ->assertJsonPath('orders_completed', 1)
            ->assertJsonPath('active', 0);
    }

    public function test_generic_done_endpoint_rejects_production_job(): void
    {
        $user = $this->helper();
        $job = $this->claimedJob($user);

        Absensi::create([
            'user_id' => $user->id, 'tanggal' => now()->toDateString(), 'jam_masuk' => '08:00:00', 'status' => 'hadir',
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/job-todos/' . $job->id . '/done')
            ->assertStatus(422);
    }

    private function claimedJob(User $user): JobTodo
    {
        $job = JobTodo::create([
            'type' => 'production', 'source' => 'pos', 'erp_item_id' => 43, 'erp_order_id' => 900,
            'invoice_no' => 'INV/2026/09/01', 'outlet_id' => 7,
            'title' => 'Banner', 'bonus' => 0, 'broadcast' => true, 'status' => 'in_progress',
        ]);
        $job->users()->attach($user->id, ['status' => 'accepted']);

        return $job;
    }
}
