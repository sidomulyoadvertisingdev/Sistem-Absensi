<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * ===============================
     * GET /api/profile
     * ===============================
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $photoUrl = $user->profile_photo
            ? asset('storage/' . $user->profile_photo)
            : null;

        return response()->json([
            'success' => true,
            'data' => array_merge(
                $user->toArray(),
                ['photo_url' => $photoUrl]
            ),
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
            'address' => 'nullable|string|max:500',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $photoFile = null;
        if ($request->hasFile('photo')) {
            $photoFile = $request->file('photo');
        } elseif ($request->hasFile('foto')) {
            $photoFile = $request->file('foto');
        }

        if ($photoFile) {
            if (!empty($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $validated['profile_photo'] = $photoFile
                ->store('profile-photos', 'public');
        }

        $user->update($validated);

        $photoUrl = $user->profile_photo
            ? asset('storage/' . $user->profile_photo)
            : null;

        return response()->json([
            'success' => true,
            'message' => 'Profile berhasil diperbarui',
            'data' => array_merge(
                $user->toArray(),
                ['photo_url' => $photoUrl]
            ),
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
