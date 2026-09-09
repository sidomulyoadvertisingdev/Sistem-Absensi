<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushTokenController extends Controller
{
    /**
     * Daftarkan Expo push token milik user yang sedang login.
     * Dipanggil app via POST /api/push-tokens (syncPushToken).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:50'],
        ]);

        $user = $request->user();

        PushToken::updateOrCreate(
            ['user_id' => $user->id, 'token' => $request->string('token')->toString()],
            ['platform' => $request->input('platform', 'expo')]
        );

        return response()->json(['status' => 'ok']);
    }
}