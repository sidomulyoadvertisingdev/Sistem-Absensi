<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\{
    DashboardController,
    AbsensiController,
    LemburController,
    LaporanController,
    WorkScheduleController,
    UserSalaryController,
    UserController,
    PelanggaranController,
    MasterJabatanController,
    MasterLokasiController,
    MasterPelanggaranController
};

/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect('/admin'));

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'guest'])->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware(['web', 'auth'])
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

        /* ================= DASHBOARD ================= */
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        /* ================= ABSENSI ================= */
        Route::get('/absensi', [AbsensiController::class, 'index'])
            ->name('absensi');
        Route::get('/absensi/create', [AbsensiController::class, 'create'])
            ->name('absensi.create');
        Route::post('/absensi', [AbsensiController::class, 'store'])
            ->name('absensi.store');

        /* ================= LEMBUR ================= */
        Route::get('/lembur', [LemburController::class, 'index'])
            ->name('lembur');
        Route::get('/lembur/create', [LemburController::class, 'create'])
            ->name('lembur.create');
        Route::post('/lembur', [LemburController::class, 'store'])
            ->name('lembur.store');
        Route::post('/lembur/{id}/approve', [LemburController::class, 'approve'])
            ->name('lembur.approve');

        /* ================= GAJI ================= */
        Route::get('/gaji', [UserSalaryController::class, 'index'])
            ->name('gaji');
        Route::get('/gaji/{user}/edit', [UserSalaryController::class, 'edit'])
            ->name('gaji.edit');
        Route::post('/gaji/{user}', [UserSalaryController::class, 'update'])
            ->name('gaji.update');
        Route::get('/gaji/{user}/slip/pdf', [UserSalaryController::class, 'slipPdf'])
            ->name('gaji.slip.pdf');

        /* ================= LAPORAN ================= */
        Route::get('/laporan', [LaporanController::class, 'index'])
            ->name('laporan');

        /* ================= JADWAL ================= */
        Route::get('/jadwal-kerja', [WorkScheduleController::class, 'index'])
            ->name('jadwal');
        Route::get('/jadwal-kerja/{user}/edit', [WorkScheduleController::class, 'edit'])
            ->name('jadwal.edit');
        Route::post('/jadwal-kerja/{user}', [WorkScheduleController::class, 'update'])
            ->name('jadwal.update');

        /* ================= KARYAWAN ================= */
        Route::get('/karyawan', [UserController::class, 'index'])
            ->name('karyawan.index');
        Route::get('/karyawan/create', [UserController::class, 'create'])
            ->name('karyawan.create');
        Route::post('/karyawan', [UserController::class, 'store'])
            ->name('karyawan.store');

        /* ======================================================
        | PELANGGARAN KARYAWAN
        ====================================================== */
        Route::prefix('pelanggaran')
            ->name('pelanggaran.')
            ->group(function () {

                Route::get('/', [PelanggaranController::class, 'index'])
                    ->name('index');

                Route::get('/create', [PelanggaranController::class, 'create'])
                    ->name('create');

                Route::post('/', [PelanggaranController::class, 'store'])
                    ->name('store');

                Route::get('/user/{user}', [PelanggaranController::class, 'show'])
                    ->name('riwayat');

                Route::prefix('master')
                    ->name('master.')
                    ->group(function () {

                        Route::get('/jabatan', [MasterJabatanController::class, 'index'])
                            ->name('jabatan.index');
                        Route::get('/jabatan/create', [MasterJabatanController::class, 'create'])
                            ->name('jabatan.create');
                        Route::post('/jabatan', [MasterJabatanController::class, 'store'])
                            ->name('jabatan.store');

                        Route::get('/lokasi', [MasterLokasiController::class, 'index'])
                            ->name('lokasi.index');
                        Route::get('/lokasi/create', [MasterLokasiController::class, 'create'])
                            ->name('lokasi.create');
                        Route::post('/lokasi', [MasterLokasiController::class, 'store'])
                            ->name('lokasi.store');

                        Route::get('/kode', [MasterPelanggaranController::class, 'index'])
                            ->name('kode.index');
                        Route::get('/kode/create', [MasterPelanggaranController::class, 'create'])
                            ->name('kode.create');
                        Route::post('/kode', [MasterPelanggaranController::class, 'store'])
                            ->name('kode.store');
                    });
            });
    });
