<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * TAMPILKAN FORM LOGIN (WEB)
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * LOGIN WEB (ADMINLTE)
     * ⛔ BLOK REQUEST API / JSON
     */
    public function login(Request $request)
    {
        // 🔥 PROTEKSI UTAMA
        // Jika request JSON / dari API → TOLAK
        if ($request->expectsJson() || $request->isJson()) {
            abort(404);
        }

        // validasi login web
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau password salah'])
                ->withInput();
        }

        $request->session()->regenerate();

        return redirect()->intended('/admin');
    }

    /**
     * LOGOUT WEB
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
