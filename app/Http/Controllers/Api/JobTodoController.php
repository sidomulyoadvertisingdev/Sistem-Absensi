<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobTodo;
use App\Models\User;
use App\Events\JobTodoDone;
use App\Events\JobTodoClaimed;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class JobTodoController extends Controller
{
    /**
     * ==================================================
     * JOB SAYA (DASHBOARD KARYAWAN)
     * ==================================================
     */
    public function myJobs()
    {
        $user = auth()->user();

        if ($user->role !== User::ROLE_KARYAWAN) {
            return response()->json(['data' => []]);
        }

        $jobs = JobTodo::query()
            ->where('status', 'in_progress')
            ->whereHas('users', function ($q) use ($user) {
                $q->where('job_todo_user.user_id', $user->id)
                  ->where('job_todo_user.status', 'accepted');
            })
            ->orderByDesc('id')
            ->get()
            ->map(fn (JobTodo $job) => $this->presentJob($job, 'in_progress'));

        return response()->json(['data' => $jobs]);
    }

    /**
     * ==================================================
     * JOB BROADCAST TERSEDIA
     * ==================================================
     */
    public function available()
    {
        $user = auth()->user();

        $jobs = JobTodo::query()
            ->where('broadcast', true)
            ->where('status', 'open')
            ->whereDoesntHave('users', function ($q) {
                $q->where('job_todo_user.status', 'accepted');
            })
            // Job produksi hanya untuk helper produksi di outlet yang cocok.
            ->where(function ($q) use ($user) {
                $q->where('type', '!=', JobTodo::TYPE_PRODUCTION);
                if ($user && $user->can_close_production && $user->erp_outlet_id) {
                    $q->orWhere(function ($qq) use ($user) {
                        $qq->where('type', JobTodo::TYPE_PRODUCTION)
                           ->where('outlet_id', $user->erp_outlet_id);
                    });
                }
            })
            ->orderByDesc('id')
            ->get()
            ->map(fn (JobTodo $job) => $this->presentJob($job, 'open'));

        return response()->json(['data' => $jobs]);
    }

    /**
     * ==================================================
     * AMBIL JOB BROADCAST
     * ==================================================
     */
    public function take($id)
    {
        $user = auth()->user();

        if ($user->role !== User::ROLE_KARYAWAN) {
            abort(403, 'Bukan karyawan');
        }

        $preview = JobTodo::query()->find($id);
        if ($preview && $preview->isProduction()) {
            if (!$user->can_close_production) {
                abort(403, 'Fitur produksi khusus tim produksi.');
            }
            if ((int) $user->erp_outlet_id !== (int) $preview->outlet_id) {
                abort(403, 'Job produksi ini bukan untuk outlet Anda.');
            }
        }

        $job = null;

        DB::transaction(function () use ($id, $user, &$job) {

            $job = JobTodo::query()
                ->where('id', $id)
                ->where('broadcast', true)
                ->where('status', 'open')
                ->whereDoesntHave('users', function ($q) {
                    $q->where('job_todo_user.status', 'accepted');
                })
                ->lockForUpdate()
                ->firstOrFail();

            $job->users()->syncWithoutDetaching([
                $user->id => ['status' => 'accepted'],
            ]);

            $job->update([
                'status' => 'in_progress',
            ]);
        });

        try {
            event(new JobTodoClaimed($job, $user->id));
        } catch (\Throwable $e) {
            \Log::warning('JobTodoClaimed broadcast gagal', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'message' => 'Job berhasil diambil',
        ]);
    }

    /**
     * ==================================================
     * DETAIL JOB
     * ==================================================
     */
    public function show($id)
    {
        $user = auth()->user();

        if ($user->role !== User::ROLE_KARYAWAN) {
            abort(403);
        }

        $job = JobTodo::query()
            ->where('id', $id)
            ->where('status', 'in_progress')
            ->whereHas('users', function ($q) use ($user) {
                $q->where('job_todo_user.user_id', $user->id)
                  ->where('job_todo_user.status', 'accepted');
            })
            ->firstOrFail();

        return response()->json($this->presentJob($job, 'in_progress', withDescription: true));
    }

    /**
     * ==================================================
     * SELESAIKAN JOB
     * ==================================================
     */
    public function done($id)
    {
        $user = auth()->user();

        if ($user->role !== User::ROLE_KARYAWAN) {
            abort(403);
        }

        $preview = JobTodo::query()->find($id);
        if ($preview && $preview->isProduction()) {
            return response()->json([
                'message' => 'Job produksi diselesaikan dari layar List Produksi (butuh foto + lokasi).',
            ], 422);
        }

        $job = null;

        DB::transaction(function () use ($id, $user, &$job) {

            $job = JobTodo::query()
                ->where('id', $id)
                ->where('status', 'in_progress')
                ->whereHas('users', function ($q) use ($user) {
                    $q->where('job_todo_user.user_id', $user->id)
                      ->where('job_todo_user.status', 'accepted');
                })
                ->lockForUpdate()
                ->firstOrFail();

            $job->users()->updateExistingPivot(
                $user->id,
                [
                    'status'       => 'completed',
                    'completed_at' => Carbon::now(),
                ]
            );

            $job->update([
                'status' => 'done',
            ]);
        });

        /**
         * 🔔 EVENT DI LUAR TRANSACTION (AMAN)
         */
        try {
            event(new JobTodoDone($job, $user->id));
        } catch (\Throwable $e) {
            \Log::error('JobTodoDone broadcast gagal', [
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'Job berhasil diselesaikan',
        ]);
    }

    /**
     * Bentuk umum item job untuk aplikasi. Job produksi membawa field tambahan.
     */
    private function presentJob(JobTodo $job, string $status, bool $withDescription = false): array
    {
        $out = [
            'id'     => (int) $job->id,
            'title'  => (string) $job->title,
            'bonus'  => (int) $job->bonus,
            'status' => $status,
            'type'   => (string) ($job->type ?? 'general'),
        ];

        if ($withDescription) {
            $out['description'] = (string) ($job->description ?? '');
        }

        if ($job->isProduction()) {
            $out['production'] = [
                'job_id'            => (int) $job->id,
                'erp_item_id'       => (int) $job->erp_item_id,
                'erp_order_id'      => (int) $job->erp_order_id,
                'invoice_no'        => (string) ($job->invoice_no ?? ''),
                'customer_name'     => (string) ($job->customer_name ?? ''),
                'product_name'      => (string) ($job->product_name ?? ''),
                'qty'              => (string) ($job->qty ?? ''),
                'deadline_at'       => optional($job->deadline_at)->toIso8601String(),
                'production_status' => (string) ($job->production_status ?? ''),
            ];
        }

        return $out;
    }
}
