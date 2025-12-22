<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AbsensiController;
use App\Http\Controllers\Admin\LemburController;
use App\Http\Controllers\Admin\GajiController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\WorkScheduleController;

/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect('/admin');
});

/*
|--------------------------------------------------------------------------
| AUTH
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

        /*
        | Dashboard
        */
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        /*
        | Absensi
        */
        Route::get('/absensi', [AbsensiController::class, 'index'])
            ->name('absensi');

        /*
        | Lembur
        */
        Route::get('/lembur', [LemburController::class, 'index'])
            ->name('lembur');

        /*
        | Gaji
        */
        Route::get('/gaji', [GajiController::class, 'index'])
            ->name('gaji');

        /*
        | Laporan
        */
        Route::get('/laporan', [LaporanController::class, 'index'])
            ->name('laporan');

        /*
        | Jadwal Kerja (Per User)
        */
        Route::get('/jadwal-kerja', [WorkScheduleController::class, 'index'])
            ->name('jadwal');

        Route::get('/jadwal-kerja/{user}/edit', [WorkScheduleController::class, 'edit'])
            ->name('jadwal.edit');

        Route::post('/jadwal-kerja/{user}', [WorkScheduleController::class, 'update'])
            ->name('jadwal.update');
    });
