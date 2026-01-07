<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobTodo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     * - BROADCAST  → OPEN + PENDING
     * - DIRECT     → IN_PROGRESS + ACCEPTED
     * - REALTIME NOTIFICATION
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

        DB::transaction(function () use ($validated) {

            /**
             * =================================================
             * BROADCAST JOB (SEMUA KARYAWAN)
             * =================================================
             */
            if ((bool) $validated['broadcast'] === true) {

                $todo = JobTodo::create([
                    'title'       => $validated['title'],
                    'description' => $validated['description'] ?? null,
                    'bonus'       => $validated['bonus'],
                    'broadcast'   => true,
                    'status'      => 'open',
                ]);

                $karyawanIds = User::where('role', User::ROLE_KARYAWAN)
                    ->pluck('id');

                foreach ($karyawanIds as $userId) {
                    $todo->users()->attach($userId, [
                        'status' => 'pending',
                    ]);
                }

                // 🔔 Realtime ke semua karyawan
                broadcast(new JobTodoCreated($todo))->toOthers();
            }

            /**
             * =================================================
             * DIRECT JOB (LANGSUNG KE 1 KARYAWAN)
             * =================================================
             */
            else {

                if (empty($validated['users']) || count($validated['users']) !== 1) {
                    abort(422, 'Job direct harus ke satu karyawan');
                }

                $userId = $validated['users'][0];

                // 🔥 LANGSUNG DIKERJAKAN
                $todo = JobTodo::create([
                    'title'       => $validated['title'],
                    'description' => $validated['description'] ?? null,
                    'bonus'       => $validated['bonus'],
                    'broadcast'   => false,
                    'status'      => 'in_progress',
                ]);

                // 🔥 LANGSUNG ACCEPTED
                $todo->users()->attach($userId, [
                    'status' => 'accepted',
                ]);

                // 🔔 Realtime khusus user ini
                broadcast(
                    new JobTodoCreated($todo, $userId)
                )->toOthers();
            }
        });

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
     * TUTUP JOB TODO (ADMIN ONLY)
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
