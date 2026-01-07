<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /**
     * POST /api/register
     * Role default: user (pelamar)
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        /**
         * ===============================
         * CREATE USER
         * ===============================
         * password AUTO HASH
         * role AUTO user
         */
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => $validated['password'], // ⬅️ AUTO HASH (casts)
            'role'     => User::ROLE_USER,
        ]);

        /**
         * ===============================
         * CREATE SANCTUM TOKEN
         * ===============================
         */
        $token = $user->createToken('register')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ], 201);
    }
}
