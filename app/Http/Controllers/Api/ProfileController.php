<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * ===============================
     * GET /api/profile
     * ===============================
     */
    public function show(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }

    /**
     * ===============================
     * PUT /api/profile
     * ===============================
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'  => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile berhasil diperbarui',
            'data' => $user,
        ]);
    }

    /**
     * ===============================
     * GET /api/my-applications
     * RIWAYAT LAMARAN USER
     * ===============================
     */
    public function applications(Request $request)
    {
        $user = $request->user();

        $applications = $user->jobApplicants()
            ->with('job')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $applications,
        ]);
    }
}
