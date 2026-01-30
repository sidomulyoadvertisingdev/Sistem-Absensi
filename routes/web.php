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
    MasterPelanggaranController,
    JobController,
    JobFormFieldController,
    JobApplicantController,
    JobTodoController,
    SalaryDeductionRuleController,
    SubmissionTypeController,
    PayrollController
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
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware(['web', 'auth'])
    ->name('logout');

/*
|--------------------------------------------------------------------------
| APP UPDATE POPUP
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth'])->post('/app-update/acknowledge', function () {
    auth()->user()->update([
        'app_version_seen' => config('app.app_version'),
    ]);
    return redirect()->back();
})->name('app.update.ack');

/*
|--------------------------------------------------------------------------
| ADMIN AREA
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth', 'is_admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {

        /* ================= DASHBOARD ================= */
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        /* ================= USERS ================= */
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'allUsers'])->name('index');
            Route::post('/{user}/promote', [UserController::class, 'promoteToKaryawan'])->name('promote');
            Route::post('/{user}/demote', [UserController::class, 'demoteToUser'])->name('demote');
        });

        /* ================= ABSENSI ================= */
        Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi');
        Route::get('/absensi/create', [AbsensiController::class, 'create'])->name('absensi.create');
        Route::post('/absensi', [AbsensiController::class, 'store'])->name('absensi.store');

        /* ================= LEMBUR ================= */
        Route::get('/lembur', [LemburController::class, 'index'])->name('lembur');
        Route::get('/lembur/create', [LemburController::class, 'create'])->name('lembur.create');
        Route::post('/lembur', [LemburController::class, 'store'])->name('lembur.store');
        Route::post('/lembur/{id}/approve', [LemburController::class, 'approve'])->name('lembur.approve');

        /* ================= GAJI ================= */
        Route::get('/gaji', [UserSalaryController::class, 'index'])->name('gaji');
        Route::get('/gaji/{user}/edit', [UserSalaryController::class, 'edit'])->name('gaji.edit');
        Route::post('/gaji/{user}', [UserSalaryController::class, 'update'])->name('gaji.update');
        Route::get('/gaji/{user}/slip/pdf', [UserSalaryController::class, 'slipPdf'])->name('gaji.slip.pdf');

        /* ================= DETAIL GAJI & PAYROLL ================= */
        Route::get('/gaji/{user}/detail', [PayrollController::class, 'show'])
            ->name('gaji.detail');

        Route::post('/gaji/{user}/pay', [PayrollController::class, 'pay'])
            ->name('gaji.pay');

        Route::get('/gaji/{user}/slip/final/pdf', [PayrollController::class, 'exportPdf'])
            ->name('gaji.slip.final.pdf');

        /* ================= LAPORAN ================= */
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');
        Route::get('/laporan/gaji/pdf', [LaporanController::class, 'exportPdf'])
            ->name('laporan.gaji.pdf');

        /* ================= ATURAN POTONGAN GAJI (PRIMARY) ================= */
        Route::prefix('salary-deduction-rules')->name('salary-deduction-rules.')->group(function () {
            Route::get('/', [SalaryDeductionRuleController::class, 'index'])->name('index');
            Route::get('/create', [SalaryDeductionRuleController::class, 'create'])->name('create');
            Route::post('/', [SalaryDeductionRuleController::class, 'store'])->name('store');
            Route::get('/{rule}/edit', [SalaryDeductionRuleController::class, 'edit'])->name('edit');
            Route::put('/{rule}', [SalaryDeductionRuleController::class, 'update'])->name('update');
            Route::delete('/{rule}', [SalaryDeductionRuleController::class, 'destroy'])->name('destroy');
            Route::post('/{rule}/toggle', [SalaryDeductionRuleController::class, 'toggle'])->name('toggle');
        });

        /* ================= ATURAN POTONGAN GAJI (ALIAS / LEGACY) ================= */
        Route::prefix('potongan-gaji')->name('potongan-gaji.')->group(function () {
            Route::get('/', [SalaryDeductionRuleController::class, 'index'])->name('index');
            Route::get('/create', [SalaryDeductionRuleController::class, 'create'])->name('create');
            Route::post('/', [SalaryDeductionRuleController::class, 'store'])->name('store');
            Route::get('/{rule}/edit', [SalaryDeductionRuleController::class, 'edit'])->name('edit');
            Route::put('/{rule}', [SalaryDeductionRuleController::class, 'update'])->name('update');
            Route::delete('/{rule}', [SalaryDeductionRuleController::class, 'destroy'])->name('destroy');
            Route::post('/{rule}/toggle', [SalaryDeductionRuleController::class, 'toggle'])->name('toggle');
        });

        /* ================= JADWAL KERJA ================= */
        Route::get('/jadwal-kerja', [WorkScheduleController::class, 'index'])->name('jadwal');
        Route::get('/jadwal-kerja/{user}/edit', [WorkScheduleController::class, 'edit'])->name('jadwal.edit');
        Route::post('/jadwal-kerja/{user}', [WorkScheduleController::class, 'update'])->name('jadwal.update');

        /* ================= KARYAWAN ================= */
        Route::prefix('karyawan')->name('karyawan.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{id}', [UserController::class, 'update'])->name('update');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
            Route::get('/export/csv', [UserController::class, 'exportCsv'])->name('export.csv');
            Route::post('/import/csv', [UserController::class, 'importCsv'])->name('import.csv');
        });

        /* ================= JOBS ================= */
        Route::prefix('jobs')->name('jobs.')->group(function () {
            Route::get('/', [JobController::class, 'index'])->name('index');
            Route::get('/create', [JobController::class, 'create'])->name('create');
            Route::post('/', [JobController::class, 'store'])->name('store');
            Route::get('/{job}/edit', [JobController::class, 'edit'])->name('edit');
            Route::put('/{job}', [JobController::class, 'update'])->name('update');
            Route::delete('/{job}', [JobController::class, 'destroy'])->name('destroy');

            Route::post('/{job}/fields', [JobFormFieldController::class, 'store'])->name('fields.store');
            Route::delete('/fields/{field}', [JobFormFieldController::class, 'destroy'])->name('fields.destroy');

            Route::get('/{job}/applicants', [JobApplicantController::class, 'index'])
                ->name('applicants.index');
        });

        Route::get('/jobs/applicants', [JobApplicantController::class, 'indexAll'])
            ->name('jobs.applicants.all');

        /* ================= JOB TODO ================= */
        Route::prefix('job-todos')->name('job-todos.')->group(function () {
            Route::get('/', [JobTodoController::class, 'index'])->name('index');
            Route::get('/create', [JobTodoController::class, 'create'])->name('create');
            Route::post('/', [JobTodoController::class, 'store'])->name('store');
            Route::get('/{jobTodo}', [JobTodoController::class, 'show'])->name('show');
            Route::put('/{jobTodo}/close', [JobTodoController::class, 'close'])->name('close');
        });

        /* ================= PELANGGARAN ================= */
        Route::prefix('pelanggaran')->name('pelanggaran.')->group(function () {
            Route::get('/', [PelanggaranController::class, 'index'])->name('index');
            Route::get('/create', [PelanggaranController::class, 'create'])->name('create');
            Route::post('/', [PelanggaranController::class, 'store'])->name('store');
            Route::get('/user/{user}', [PelanggaranController::class, 'show'])->name('riwayat');

            Route::prefix('master')->name('master.')->group(function () {
                Route::get('/jabatan', [MasterJabatanController::class, 'index'])->name('jabatan.index');
                Route::get('/jabatan/create', [MasterJabatanController::class, 'create'])->name('jabatan.create');
                Route::post('/jabatan', [MasterJabatanController::class, 'store'])->name('jabatan.store');

                Route::get('/lokasi', [MasterLokasiController::class, 'index'])->name('lokasi.index');
                Route::get('/lokasi/create', [MasterLokasiController::class, 'create'])->name('lokasi.create');
                Route::post('/lokasi', [MasterLokasiController::class, 'store'])->name('lokasi.store');

                Route::get('/kode', [MasterPelanggaranController::class, 'index'])->name('kode.index');
                Route::get('/kode/create', [MasterPelanggaranController::class, 'create'])->name('kode.create');
                Route::post('/kode', [MasterPelanggaranController::class, 'store'])->name('kode.store');
            });
        });

        /* ================= SUBMISSION TYPES ================= */
        Route::prefix('submission-types')->name('submission-types.')->group(function () {
            Route::get('/', [SubmissionTypeController::class, 'index'])->name('index');
            Route::get('/create', [SubmissionTypeController::class, 'create'])->name('create');
            Route::post('/', [SubmissionTypeController::class, 'store'])->name('store');
            Route::get('/{type}/edit', [SubmissionTypeController::class, 'edit'])->name('edit');
            Route::put('/{type}', [SubmissionTypeController::class, 'update'])->name('update');
            Route::delete('/{type}', [SubmissionTypeController::class, 'destroy'])->name('destroy');
            Route::post('/{type}/toggle', [SubmissionTypeController::class, 'toggle'])->name('toggle');
        });

        /* ================= SUBMISSION ================= */
        Route::prefix('submission')->name('submission.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SubmissionController::class, 'index'])->name('index');
            Route::get('/{submission}', [\App\Http\Controllers\Admin\SubmissionController::class, 'show'])->name('show');
            Route::post('/{submission}/approve', [\App\Http\Controllers\Admin\SubmissionController::class, 'approve'])->name('approve');
            Route::post('/{submission}/reject', [\App\Http\Controllers\Admin\SubmissionController::class, 'reject'])->name('reject');
            Route::post('/{submission}/cancel', [\App\Http\Controllers\Admin\SubmissionController::class, 'cancel'])->name('cancel');
        });

        /* ================= ANNOUNCEMENTS ================= */
        Route::prefix('announcements')->name('announcements.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AnnouncementController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\AnnouncementController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\AnnouncementController::class, 'store'])->name('store');
            Route::get('/{announcement}', [\App\Http\Controllers\Admin\AnnouncementController::class, 'show'])->name('show');
            Route::post('/{announcement}/toggle', [\App\Http\Controllers\Admin\AnnouncementController::class, 'toggle'])->name('toggle');
        });
    });
