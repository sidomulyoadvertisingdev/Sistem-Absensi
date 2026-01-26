<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobTodo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\JobTodoCreated;

class JobTodoController extends Controller
{
    /**
     * =====================================================
     * LIST JOB TODO (ADMIN)
     * =====================================================
     */
    public function index()
    {
        $todos = JobTodo::withCount([
                'users as total_user'
            ])
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.job-todos.index', compact('todos'));
    }

    /**
     * =====================================================
     * FORM CREATE JOB TODO
     * =====================================================
     */
    public function create()
    {
        $users = User::where('role', User::ROLE_KARYAWAN)
            ->orderBy('name')
            ->get();

        return view('admin.job-todos.create', compact('users'));
    }

    /**
     * =====================================================
     * SIMPAN JOB TODO
     * =====================================================
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'bonus'       => 'required|numeric|min:0',
            'broadcast'   => 'required|in:0,1',
            'users'       => 'nullable|array',
            'users.*'     => 'exists:users,id',
        ]);

        $todo = null;
        $targetUserIds = [];

        /**
         * =================================================
         * DATABASE ADALAH SUMBER KEBENARAN
         * =================================================
         */
        DB::transaction(function () use ($validated, &$todo, &$targetUserIds) {

            /**
             * =============================
             * BROADCAST JOB
             * =============================
             */
            if ((bool) $validated['broadcast'] === true) {

                $todo = JobTodo::create([
                    'title'       => $validated['title'],
                    'description' => $validated['description'] ?? null,
                    'bonus'       => $validated['bonus'],
                    'broadcast'   => true,
                    'status'      => 'open',
                ]);

                $targetUserIds = User::where('role', User::ROLE_KARYAWAN)
                    ->pluck('id')
                    ->toArray();

                foreach ($targetUserIds as $userId) {
                    $todo->users()->attach($userId, [
                        'status' => 'pending',
                    ]);
                }
            }

            /**
             * =============================
             * DIRECT JOB
             * =============================
             */
            else {

                if (empty($validated['users']) || count($validated['users']) !== 1) {
                    abort(422, 'Job direct harus ke satu karyawan');
                }

                $userId = $validated['users'][0];
                $targetUserIds = [$userId];

                $todo = JobTodo::create([
                    'title'       => $validated['title'],
                    'description' => $validated['description'] ?? null,
                    'bonus'       => $validated['bonus'],
                    'broadcast'   => false,
                    'status'      => 'in_progress',
                ]);

                $todo->users()->attach($userId, [
                    'status' => 'accepted',
                ]);
            }
        });

        /**
         * =================================================
         * 🔔 REALTIME EVENT (BONUS)
         * DI LUAR TRANSACTION (WAJIB)
         * =================================================
         */
        foreach ($targetUserIds as $userId) {
            try {
                event(new JobTodoCreated($todo, $userId));
            } catch (\Throwable $e) {
                Log::error('Broadcast JobTodoCreated gagal', [
                    'job_id'  => $todo?->id,
                    'user_id' => $userId,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        return redirect()
            ->route('admin.job-todos.index')
            ->with('success', 'Job Todo berhasil dibuat');
    }

    /**
     * =====================================================
     * DETAIL JOB TODO
     * =====================================================
     */
    public function show(JobTodo $jobTodo)
    {
        $jobTodo->load([
            'users' => fn ($q) => $q->orderBy('name')
        ]);

        return view('admin.job-todos.show', compact('jobTodo'));
    }

    /**
     * =====================================================
     * TUTUP JOB TODO
     * =====================================================
     */
    public function close(JobTodo $jobTodo)
    {
        if (in_array($jobTodo->status, ['done', 'closed'])) {
            return back()->with(
                'warning',
                'Job Todo sudah selesai atau ditutup'
            );
        }

        $jobTodo->update([
            'status' => 'closed',
        ]);

        return back()->with(
            'success',
            'Job Todo berhasil ditutup'
        );
    }
}
