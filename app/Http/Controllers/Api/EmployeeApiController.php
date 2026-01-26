<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EmployeeApiController extends Controller
{
    /**
     * =====================================================
     * 🔹 LIST EMPLOYEE BERDASARKAN JABATAN
     * =====================================================
     */
    public function index()
    {
        // Ambil user yang punya jabatan
        $employees = User::query()
            ->whereNotNull('jabatan')
            ->select('id', 'name', 'nik', 'jabatan')
            ->orderBy('jabatan')
            ->orderBy('name')
            ->get()
            ->groupBy('jabatan');

        // Pastikan format OBJECT, bukan collection aneh
        $result = [];

        foreach ($employees as $jabatan => $items) {
            $result[$jabatan] = $items->map(function ($u) {
                return [
                    'id'   => $u->id,
                    'name' => $u->name,
                    'nik'  => $u->nik,
                    'jabatan' => $u->jabatan,
                ];
            })->values();
        }

        return response()->json([
            'status' => true,
            'data'   => $result,
        ]);
    }

    /**
     * =====================================================
     * 🔹 LEADERBOARD TOP 3 (AMAN & SEDERHANA)
     * =====================================================
     */
    public function leaderboard()
    {
        /**
         * CATATAN:
         * - PAKAI jobs table kalau ada
         * - Kalau belum, pakai dummy dari job_todos
         */

        try {
            $data = DB::table('job_todos')
                ->join('users', 'users.id', '=', 'job_todos.user_id')
                ->select(
                    'users.id',
                    'users.name',
                    DB::raw('SUM(job_todos.reward) as total_reward')
                )
                ->whereNotNull('job_todos.user_id')
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total_reward')
                ->limit(3)
                ->get();
        } catch (\Exception $e) {
            // ⛑️ Kalau tabel reward belum siap
            return response()->json([
                'status' => true,
                'data'   => [],
            ]);
        }

        $ranked = [];
        $rank = 1;

        foreach ($data as $row) {
            $ranked[] = [
                'rank' => $rank,
                'name' => $row->name,
                'total_reward' => (int) $row->total_reward,
            ];
            $rank++;
        }

        return response()->json([
            'status' => true,
            'data'   => $ranked,
        ]);
    }
}
