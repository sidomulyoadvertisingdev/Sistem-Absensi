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
use App\Http\Controllers\Api\PelanggaranApiController;
use App\Http\Controllers\Api\SubmissionApiController;
use App\Http\Controllers\Api\AnnouncementApiController;
use App\Http\Controllers\Api\EmployeeApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Prefix : /api
| Auth   : Sanctum (Bearer Token)
|--------------------------------------------------------------------------
*/

/* ================= AUTH ================= */
Route::post('/login', [AuthController::class, 'login']);

/* ================= PROTECTED ROUTES ================= */
Route::middleware('auth:sanctum')->group(function () {

    /* ================= DASHBOARD ================= */
    Route::get('/dashboard', [DashboardController::class, 'index']);

    /* ================= ABSENSI ================= */
    Route::get('/absensi/today', [AbsensiController::class, 'today']);
    Route::post('/absensi', [AbsensiController::class, 'store']);

    /* ================= JADWAL ================= */
    Route::get('/jadwal', [JadwalController::class, 'index']);

    /* ================= LEMBUR ================= */
    Route::get('/lembur', [LemburController::class, 'index']);
    Route::post('/lembur', [LemburController::class, 'store']);
    Route::post('/lembur/{lembur}/finish', [LemburController::class, 'finish'])
        ->whereNumber('lembur');

    /* ================= GAJI ================= */
    Route::get('/gaji', [GajiController::class, 'index']);

    /* ================= JOB TODO ================= */
    Route::get('/job-todos/my', [JobTodoController::class, 'myJobs']);
    Route::get('/job-todos/available', [JobTodoController::class, 'available']);
    Route::post('/job-todos/{id}/take', [JobTodoController::class, 'take'])
        ->whereNumber('id');
    Route::get('/job-todos/{id}', [JobTodoController::class, 'show'])
        ->whereNumber('id');
    Route::post('/job-todos/{id}/done', [JobTodoController::class, 'done'])
        ->whereNumber('id');

    /* ================= PELANGGARAN ================= */
    Route::get('/violations', [PelanggaranApiController::class, 'index']);
    Route::get('/violations/{id}', [PelanggaranApiController::class, 'show'])
        ->whereNumber('id');
    Route::get('/violations/{id}/download-sp', [PelanggaranApiController::class, 'downloadSp'])
        ->whereNumber('id');

    /* ================= SUBMISSION / PENGAJUAN ================= */
    Route::get('/submission-types', [SubmissionApiController::class, 'types']);
    Route::get('/submissions', [SubmissionApiController::class, 'index']);
    Route::post('/submissions', [SubmissionApiController::class, 'store']);
    Route::get('/submissions/{id}', [SubmissionApiController::class, 'show'])
        ->whereNumber('id');

    /* ================= ANNOUNCEMENTS / PENGUMUMAN ================= */
    Route::get('/announcements', [AnnouncementApiController::class, 'index']);
    Route::get('/announcements/{id}', [AnnouncementApiController::class, 'show'])
        ->whereNumber('id');

    /* ================= EMPLOYEE (🔥 FITUR BARU) ================= */
    // List semua employee (group by jabatan di frontend)
    Route::get('/employees', [EmployeeApiController::class, 'index']);

    // Leaderboard Top 1 - Top 3 (berdasarkan reward / produktivitas)
    Route::get('/employees/leaderboard', [EmployeeApiController::class, 'leaderboard']);

    /* ================= USER PROFILE ================= */
    Route::get('/me', function (Request $request) {
        return $request->user();
    });
});
