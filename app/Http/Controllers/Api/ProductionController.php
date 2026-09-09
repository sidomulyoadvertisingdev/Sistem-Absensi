<?php

namespace App\Http\Controllers\Api;

use App\Events\JobTodoClaimed;
use App\Events\JobTodoDone;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\JobTodo;
use App\Models\ProductionCloseLog;
use App\Models\User;
use App\Services\Erp\ErpProductionClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Endpoint aplikasi absensi untuk fitur produksi (broadcast job helper).
 *  - GET  /api/production/jobs?scope=available|mine
 *  - POST /api/production/jobs/{jobTodo}/complete   (multipart: foto, latitude, longitude, note)
 */
class ProductionController extends Controller
{
    public function __construct(
        private readonly ErpProductionClient $erp,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->role !== User::ROLE_KARYAWAN) {
            return response()->json(['message' => 'Bukan karyawan.'], 403);
        }
        if (!$user->can_close_production) {
            return response()->json(['message' => 'Fitur ini khusus tim produksi.'], 403);
        }

        $outletId = (int) ($user->erp_outlet_id ?? 0);
        if ($outletId <= 0) {
            return response()->json([
                'message' => 'Outlet ERP Anda belum diatur admin.',
            ], 422);
        }

        $scope = $request->query('scope', 'available') === 'mine' ? 'mine' : 'available';
        $search = trim((string) $request->query('search', ''));

        $base = JobTodo::query()->production()->where('outlet_id', $outletId);

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('product_name', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($scope === 'available') {
            $jobs = (clone $base)
                ->where('broadcast', true)
                ->where('status', 'open')
                ->whereDoesntHave('users', fn ($q) => $q->where('job_todo_user.status', 'accepted'))
                ->orderByDesc('id')
                ->limit(200)
                ->get();
        } else {
            $jobs = (clone $base)
                ->whereHas('users', function ($q) use ($user) {
                    $q->where('job_todo_user.user_id', $user->id)
                        ->whereIn('job_todo_user.status', ['accepted', 'completed']);
                })
                ->where(function ($q) {
                    $q->where('status', 'in_progress')
                        ->orWhere(fn ($qq) => $qq->where('status', 'done')->where('updated_at', '>=', now()->startOfDay()));
                })
                ->with(['users' => fn ($q) => $q->where('users.id', $user->id)])
                ->orderByDesc('id')
                ->limit(200)
                ->get();
        }

        $progressByOrder = $this->progressByOrder($outletId, $jobs->pluck('erp_order_id')->filter()->unique()->all());

        $data = $jobs->map(function (JobTodo $job) use ($progressByOrder, $user) {
            $pivot = $job->relationLoaded('users') ? optional($job->users->first())->pivot : null;

            return [
                'job_id' => (int) $job->id,
                'erp_item_id' => (int) $job->erp_item_id,
                'erp_order_id' => (int) $job->erp_order_id,
                'invoice_no' => (string) ($job->invoice_no ?? ''),
                'customer_name' => (string) ($job->customer_name ?? ''),
                'customer_phone' => (string) ($job->customer_phone ?? ''),
                'product_name' => (string) ($job->product_name ?? $job->title),
                'qty' => (string) ($job->qty ?? ''),
                'deadline_at' => optional($job->deadline_at)->toIso8601String(),
                'spec_summary' => (string) ($job->spec_summary ?? ''),
                'job_status' => (string) $job->status,
                'production_status' => (string) ($job->production_status ?? ''),
                'order_progress' => $progressByOrder[(int) $job->erp_order_id] ?? ['total' => 1, 'completed' => 0],
                'claimed_by_me' => $pivot ? in_array($pivot->status, ['accepted', 'completed'], true) : false,
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'count_available' => (clone $base)->where('broadcast', true)->where('status', 'open')
                    ->whereDoesntHave('users', fn ($q) => $q->where('job_todo_user.status', 'accepted'))->count(),
                'count_mine' => (clone $base)->where('status', 'in_progress')
                    ->whereHas('users', fn ($q) => $q->where('job_todo_user.user_id', $user->id)->where('job_todo_user.status', 'accepted'))
                    ->count(),
                'count_done_today' => ProductionCloseLog::query()
                    ->where('user_id', $user->id)
                    ->where('created_at', '>=', now()->startOfDay())
                    ->count(),
            ],
            'clock_in_ok' => $this->hasOpenClockIn($user->id),
            'can_close_production' => (bool) $user->can_close_production,
        ]);
    }

    public function complete(Request $request, JobTodo $jobTodo): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->role !== User::ROLE_KARYAWAN) {
            return response()->json(['message' => 'Bukan karyawan.'], 403);
        }
        if (!$user->can_close_production || (int) ($user->erp_outlet_id ?? 0) <= 0) {
            return response()->json(['message' => 'Anda tidak berwenang menutup produksi.'], 403);
        }

        if (!$jobTodo->isProduction()) {
            return response()->json(['message' => 'Job ini bukan job produksi.'], 422);
        }

        $claimed = $jobTodo->users()
            ->where('users.id', $user->id)
            ->wherePivot('status', 'accepted')
            ->exists();

        if (!$claimed || $jobTodo->status !== 'in_progress') {
            return response()->json(['message' => 'Job ini belum Anda ambil atau sudah selesai.'], 409);
        }

        if (!$this->hasOpenClockIn($user->id)) {
            return response()->json([
                'message' => 'Anda harus absen masuk dulu sebelum menutup produksi.',
            ], 422);
        }

        $data = $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'accuracy' => ['nullable', 'numeric'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $fotoPath = $request->file('foto')->store('production-close/' . now()->format('Ymd'), 'public');
        $photoUrl = asset('storage/' . $fotoPath);
        $capturedAt = now();

        try {
            $erpResult = $this->erp->completeItem((int) $jobTodo->erp_item_id, [
                'outlet_id' => (int) $user->erp_outlet_id,
                'note' => (string) ($data['note'] ?? ''),
                'actor' => [
                    'name' => (string) $user->name,
                    'nik' => (string) ($user->nik ?? ''),
                    'source' => 'absensi-app',
                    'photo_url' => $photoUrl,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                    'captured_at' => $capturedAt->toIso8601String(),
                ],
            ]);
        } catch (\RuntimeException $e) {
            Log::warning('[production] complete ERP gagal', [
                'job_id' => $jobTodo->id,
                'erp_item_id' => $jobTodo->erp_item_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menutup produksi di ERP: ' . $e->getMessage(),
            ], 502);
        }

        $orderCompleted = (bool) ($erpResult['order_completed'] ?? false);
        $orderProgress = is_array($erpResult['order_progress'] ?? null)
            ? $erpResult['order_progress']
            : ['total' => 1, 'completed' => 1];

        DB::transaction(function () use ($jobTodo, $user, $orderCompleted, $orderProgress, $fotoPath, $data, $capturedAt, $erpResult) {
            $jobTodo->users()->updateExistingPivot($user->id, [
                'status' => 'completed',
                'completed_at' => $capturedAt,
            ]);
            $jobTodo->update([
                'status' => 'done',
                'production_status' => 'completed',
            ]);

            ProductionCloseLog::query()->create([
                'job_todo_id' => $jobTodo->id,
                'user_id' => $user->id,
                'erp_item_id' => (int) $jobTodo->erp_item_id,
                'erp_order_id' => (int) $jobTodo->erp_order_id,
                'invoice_no' => $jobTodo->invoice_no,
                'outlet_id' => $jobTodo->outlet_id,
                'order_completed' => $orderCompleted,
                'note' => $data['note'] ?? null,
                'foto_path' => $fotoPath,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'accuracy' => $data['accuracy'] ?? null,
                'captured_at' => $capturedAt,
                'erp_response' => $erpResult,
            ]);
        });

        try {
            event(new JobTodoDone($jobTodo, $user->id));
        } catch (\Throwable $e) {
            Log::warning('[production] JobTodoDone broadcast gagal', ['error' => $e->getMessage()]);
        }

        $invoice = (string) ($jobTodo->invoice_no ?? '');
        $message = $orderCompleted
            ? "Seluruh item nota {$invoice} selesai — CRM sudah diberi tahu otomatis."
            : 'Item ditandai selesai. Sisa ' . max(0, ($orderProgress['total'] ?? 1) - ($orderProgress['completed'] ?? 1)) . ' item di nota ini.';

        return response()->json([
            'success' => true,
            'order_completed' => $orderCompleted,
            'order_progress' => $orderProgress,
            'message' => $message,
        ]);
    }

    /**
     * Ringkasan job produksi yang sudah dikerjakan user (untuk laporan donat di profil).
     */
    public function myStats(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->role !== User::ROLE_KARYAWAN) {
            return response()->json(['message' => 'Bukan karyawan.'], 403);
        }

        $logs = ProductionCloseLog::query()->where('user_id', $user->id);

        $today = Carbon::today();
        $completedTotal = (clone $logs)->count();
        $completedToday = (clone $logs)->where('created_at', '>=', $today)->count();
        $completedWeek = (clone $logs)->where('created_at', '>=', $today->copy()->startOfWeek())->count();
        $completedMonth = (clone $logs)->where('created_at', '>=', $today->copy()->startOfMonth())->count();
        $ordersDone = (clone $logs)->where('order_completed', true)->distinct()->count('erp_order_id');

        $active = JobTodo::query()
            ->production()
            ->where('status', 'in_progress')
            ->whereHas('users', fn ($q) => $q->where('job_todo_user.user_id', $user->id)
                ->where('job_todo_user.status', 'accepted'))
            ->count();

        return response()->json([
            'completed_total' => $completedTotal,
            'completed_today' => $completedToday,
            'completed_week' => $completedWeek,
            'completed_month' => $completedMonth,
            'orders_completed' => $ordersDone,
            'active' => $active,
            'can_close_production' => (bool) $user->can_close_production,
            // Kartu laporan hanya relevan untuk tim produksi / yang pernah menutup job.
            'visible' => (bool) $user->can_close_production || $completedTotal > 0 || $active > 0,
        ]);
    }

    private function hasOpenClockIn(int $userId): bool
    {
        return Absensi::query()
            ->where('user_id', $userId)
            ->whereDate('tanggal', Carbon::today()->toDateString())
            ->whereNotNull('jam_masuk')
            ->whereNull('jam_pulang')
            ->exists();
    }

    /**
     * @param  array<int, int>  $orderIds
     * @return array<int, array{total:int, completed:int}>
     */
    private function progressByOrder(int $outletId, array $orderIds): array
    {
        if ($orderIds === []) {
            return [];
        }

        $rows = JobTodo::query()
            ->production()
            ->where('outlet_id', $outletId)
            ->whereIn('erp_order_id', $orderIds)
            ->get(['erp_order_id', 'status', 'production_status']);

        $out = [];
        foreach ($rows->groupBy('erp_order_id') as $oid => $group) {
            $completed = $group->filter(
                fn ($j) => $j->status === 'done' || strtolower((string) $j->production_status) === 'completed'
            )->count();
            $out[(int) $oid] = ['total' => $group->count(), 'completed' => $completed];
        }

        return $out;
    }
}
