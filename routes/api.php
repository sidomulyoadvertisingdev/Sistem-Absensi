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
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\AnnouncementApiController;
use App\Http\Controllers\Api\EmployeeApiController;
use App\Http\Controllers\Api\ChatifyController;
use App\Http\Controllers\Api\AttendanceIntegrationReportController;
use App\Http\Controllers\Api\AttendancePayrollIntegrationController;

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

/* ================= PREFLIGHT (CORS) ================= */
Route::options('/{any}', function () {
    return response()->noContent();
})->where('any', '.*');

/* ================= PROTECTED ROUTES ================= */
Route::middleware('auth:sanctum')->group(function () {
    /* ================= INTEGRATION (SMPO) ================= */
    Route::match(['GET', 'POST'], '/integrations/attendance/report', [AttendanceIntegrationReportController::class, 'report']);
    Route::match(['GET', 'POST'], '/attendance/report', [AttendanceIntegrationReportController::class, 'report']);
    Route::match(['GET', 'POST'], '/attendance/reports', [AttendanceIntegrationReportController::class, 'report']);
    Route::get('/integrations/attendance/salaries', [AttendancePayrollIntegrationController::class, 'index']);
    Route::match(['PUT', 'PATCH', 'POST'], '/integrations/attendance/salaries/{user}', [AttendancePayrollIntegrationController::class, 'update'])
        ->whereNumber('user');
    Route::match(['POST', 'PUT'], '/integrations/attendance/salaries/{user}/pay', [AttendancePayrollIntegrationController::class, 'pay'])
        ->whereNumber('user');
    Route::get('/attendance/salaries', [AttendancePayrollIntegrationController::class, 'index']);
    Route::match(['PUT', 'PATCH', 'POST'], '/attendance/salaries/{user}', [AttendancePayrollIntegrationController::class, 'update'])
        ->whereNumber('user');
    Route::match(['POST', 'PUT'], '/attendance/salaries/{user}/pay', [AttendancePayrollIntegrationController::class, 'pay'])
        ->whereNumber('user');

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

    /* ================= NOTIFIKASI ================= */
    Route::get('/notifications', [NotificationApiController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationApiController::class, 'markRead']);

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

    /* ================= CHAT (NATIVE RN) ================= */
    Route::prefix('chat')->group(function () {
        Route::get('/users', [ChatifyController::class, 'users']);
        Route::get('/rooms', [ChatifyController::class, 'rooms']);
        Route::post('/rooms', [ChatifyController::class, 'createRoom']);
        Route::get('/rooms/{room}/messages', [ChatifyController::class, 'messages']);
        Route::post('/rooms/{room}/messages', [ChatifyController::class, 'sendMessage']);
        Route::post('/rooms/{room}/read', [ChatifyController::class, 'read']);
        Route::post('/rooms/{room}/typing', [ChatifyController::class, 'typing']);
    });
});
