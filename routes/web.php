<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AbsensiController;
use App\Http\Controllers\Admin\LemburController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\WorkScheduleController;
use App\Http\Controllers\Admin\UserSalaryController;
use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect('/admin'));

/*
|--------------------------------------------------------------------------
| AUTH (ADMIN WEB)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| ADMIN AREA
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'is_admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {

        Route::get('/gaji/{user}/slip/pdf',
    [UserSalaryController::class, 'slipPdf']
)->name('gaji.slip.pdf');


        /*
        | DASHBOARD
        */
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        /*
        | ABSENSI
        */
        Route::get('/absensi', [AbsensiController::class, 'index'])
            ->name('absensi');

        Route::get('/absensi/create', [AbsensiController::class, 'create'])
            ->name('absensi.create');

        Route::post('/absensi', [AbsensiController::class, 'store'])
            ->name('absensi.store');

        /*
        | LEMBUR
        */
        Route::get('/lembur', [LemburController::class, 'index'])
            ->name('lembur');

        Route::get('/lembur/create', [LemburController::class, 'create'])
            ->name('lembur.create');

        Route::post('/lembur', [LemburController::class, 'store'])
            ->name('lembur.store');

        Route::post('/lembur/{id}/approve', [LemburController::class, 'approve'])
            ->name('lembur.approve');

        /*
        | GAJI
        */
        Route::get('/gaji', [UserSalaryController::class, 'index'])
            ->name('gaji');

        Route::get('/gaji/{user}/edit', [UserSalaryController::class, 'edit'])
            ->name('gaji.edit');

        Route::post('/gaji/{user}', [UserSalaryController::class, 'update'])
            ->name('gaji.update');

        /*
        | LAPORAN
        */
        Route::get('/laporan', [LaporanController::class, 'index'])
            ->name('laporan');

        /*
        | JADWAL KERJA
        */
        Route::get('/jadwal-kerja', [WorkScheduleController::class, 'index'])
            ->name('jadwal');

        Route::get('/jadwal-kerja/{user}/edit', [WorkScheduleController::class, 'edit'])
            ->name('jadwal.edit');

        Route::post('/jadwal-kerja/{user}', [WorkScheduleController::class, 'update'])
            ->name('jadwal.update');

        /*
        | KARYAWAN
        */
        Route::get('/karyawan', [UserController::class, 'index'])
            ->name('karyawan.index');

        Route::get('/karyawan/create', [UserController::class, 'create'])
            ->name('karyawan.create');

        Route::post('/karyawan', [UserController::class, 'store'])
            ->name('karyawan.store');
    });


