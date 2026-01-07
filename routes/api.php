<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AbsensiController;
use App\Http\Controllers\Api\JadwalController;
use App\Http\Controllers\Api\GajiController;
use App\Http\Controllers\Api\LemburController;
use App\Http\Controllers\Api\JobTodoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Prefix otomatis: /api
| Auth: Sanctum (Bearer Token)
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | ABSENSI
    |--------------------------------------------------------------------------
    */
    Route::get('/absensi/today', [AbsensiController::class, 'today']);
    Route::post('/absensi', [AbsensiController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    | JADWAL
    |--------------------------------------------------------------------------
    */
    Route::get('/jadwal', [JadwalController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | LEMBUR
    |--------------------------------------------------------------------------
    */
    Route::get('/lembur', [LemburController::class, 'index']);
    Route::post('/lembur', [LemburController::class, 'store']);
    Route::post('/lembur/{lembur}/finish', [LemburController::class, 'finish'])
        ->whereNumber('lembur');

    /*
    |--------------------------------------------------------------------------
    | GAJI
    |--------------------------------------------------------------------------
    */
    Route::get('/gaji', [GajiController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | JOB TODO (🔥 FINAL & STABLE)
    |--------------------------------------------------------------------------
    */

    // 🔹 Job milik saya (direct + broadcast yang diambil)
    Route::get('/job-todos/my', [JobTodoController::class, 'myJobs']);

    // 🔹 Job broadcast yang masih tersedia (status = open)
    Route::get('/job-todos/available', [JobTodoController::class, 'available']);

    // 🔹 Ambil job broadcast
    Route::post('/job-todos/{id}/take', [JobTodoController::class, 'take'])
        ->whereNumber('id');

    // 🔹 Detail job (yang dimiliki user)
    Route::get('/job-todos/{id}', [JobTodoController::class, 'show'])
        ->whereNumber('id');

    // 🔹 Selesaikan job
    Route::post('/job-todos/{id}/done', [JobTodoController::class, 'done'])
        ->whereNumber('id');

    /*
    |--------------------------------------------------------------------------
    | USER PROFILE
    |--------------------------------------------------------------------------
    */
    Route::get('/me', function (Request $request) {
        return $request->user();
    });
});
