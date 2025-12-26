<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AbsensiController;
use App\Http\Controllers\Api\JadwalController;
use App\Http\Controllers\Api\GajiController;
use App\Http\Controllers\Api\LemburController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Semua route di sini menggunakan prefix /api
| Auth frontend menggunakan Sanctum (Bearer Token)
|--------------------------------------------------------------------------
*/

/**
 * ===============================
 * AUTH
 * ===============================
 */
Route::post('/login', [AuthController::class, 'login']);

/**
 * ===============================
 * PROTECTED ROUTES (LOGIN WAJIB)
 * ===============================
 */
Route::middleware('auth:sanctum')->group(function () {

    /**
     * ===============================
     * DASHBOARD
     * ===============================
     */
    Route::get('/dashboard', [DashboardController::class, 'index']);

    /**
     * ===============================
     * ABSENSI
     * ===============================
     * - Masuk / Istirahat / Pulang
     * - Wajib foto
     */
    Route::get('/absensi/today', [AbsensiController::class, 'today']);
    Route::post('/absensi', [AbsensiController::class, 'store']);

    /**
     * ===============================
     * JADWAL KERJA
     * ===============================
     * - Senin sampai Minggu
     * - Sesuai setting admin
     */
    Route::get('/jadwal', [JadwalController::class, 'index']);

    /**
     * ===============================
     * LEMBUR
     * ===============================
     * ALUR RESMI:
     * 1. requested -> user ajukan (jam_mulai otomatis)
     * 2. approved  -> admin setujui (lembur berjalan)
     * 3. finished  -> user klik selesai (jam_selesai otomatis)
     * NOTE:
     * - Lembur hanya dihitung gaji jika status = finished
     */
    Route::get('/lembur', [LemburController::class, 'index']);
    Route::post('/lembur', [LemburController::class, 'store']);
    Route::post('/lembur/{lembur}/finish', [LemburController::class, 'finish']);

    /**
     * ===============================
     * GAJI
     * ===============================
     * - Hitung lembur status finished saja
     */
    Route::get('/gaji', [GajiController::class, 'index']);

    /**
     * ===============================
     * USER PROFILE (DEBUG / OPTIONAL)
     * ===============================
     */
    Route::get('/me', function (Request $request) {
        return $request->user();
    });
});
