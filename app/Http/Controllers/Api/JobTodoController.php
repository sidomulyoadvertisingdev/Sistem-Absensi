<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobTodo;
use App\Models\User;
use App\Events\JobTodoDone;
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
            ->map(function ($job) {
                return [
                    'id'     => $job->id,
                    'title'  => $job->title,
                    'bonus'  => $job->bonus,
                    'status' => 'in_progress',
                ];
            });

        return response()->json(['data' => $jobs]);
    }

    /**
     * ==================================================
     * JOB BROADCAST TERSEDIA
     * ==================================================
     */
    public function available()
    {
        $jobs = JobTodo::query()
            ->where('broadcast', true)
            ->where('status', 'open')
            ->whereDoesntHave('users', function ($q) {
                $q->where('job_todo_user.status', 'accepted');
            })
            ->orderByDesc('id')
            ->get(['id', 'title', 'bonus']);

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

        DB::transaction(function () use ($id, $user) {

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

        return response()->json([
            'id'          => $job->id,
            'title'       => $job->title,
            'description' => $job->description,
            'bonus'       => $job->bonus,
            'status'      => 'in_progress',
        ]);
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
         * 🔔 EVENT DI LUAR TRANSACTION (AMAM)
         */
        try {
            event(new JobTodoDone($job, $user->id)); // ✅ FIX MINIMAL
        } catch (\Throwable $e) {
            \Log::error('JobTodoDone broadcast gagal', [
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'Job berhasil diselesaikan',
        ]);
    }
}
