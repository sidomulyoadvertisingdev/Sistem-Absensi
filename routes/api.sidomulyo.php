<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\ApplyJobController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;

/*
|--------------------------------------------------------------------------
| SIDOMULYO API
|--------------------------------------------------------------------------
| Prefix otomatis : /api
| Middleware      : api
| Laravel 11 / 12
*/

/*
|--------------------------------------------------------------------------
| AUTH (PUBLIC)
|--------------------------------------------------------------------------
| Login & Register
*/

// POST /api/register
Route::post('register', [RegisterController::class, 'register'])
    ->name('api.register');

// POST /api/login
Route::post('login', [LoginController::class, 'login'])
    ->name('api.login');

/*
|--------------------------------------------------------------------------
| JOB LISTING (PUBLIC)
|--------------------------------------------------------------------------
| Bisa diakses TANPA login
*/

// GET /api/jobs
Route::get('jobs', [JobController::class, 'index'])
    ->name('api.jobs.index');

// GET /api/jobs/{job}
Route::get('jobs/{job}', [JobController::class, 'show'])
    ->name('api.jobs.show');

/*
|--------------------------------------------------------------------------
| PROTECTED API (WAJIB LOGIN)
|--------------------------------------------------------------------------
| Sanctum Bearer Token
| Header: Authorization: Bearer TOKEN
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | APPLY JOB
    |--------------------------------------------------------------------------
    | POST /api/jobs/{job}/apply
    */
    Route::post('jobs/{job}/apply', [ApplyJobController::class, 'store'])
        ->name('api.jobs.apply');

    /*
    |--------------------------------------------------------------------------
    | PROFILE USER
    |--------------------------------------------------------------------------
    | GET  /api/profile
    | PUT  /api/profile
    | GET  /api/my-applications
    */
    Route::get('profile', [ProfileController::class, 'show'])
        ->name('api.profile.show');

    Route::put('profile', [ProfileController::class, 'update'])
        ->name('api.profile.update');

    // 🔥 RIWAYAT LAMARAN (INI YANG KURANG)
    Route::get('my-applications', [ProfileController::class, 'applications'])
        ->name('api.profile.applications');

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    | POST /api/logout
    */
    Route::post('logout', [LoginController::class, 'logout'])
        ->name('api.logout');
});

/*
|--------------------------------------------------------------------------
| PREFLIGHT (OPTIONS)
|--------------------------------------------------------------------------
| Wajib untuk browser (CORS)
*/
Route::options('{any}', function () {
    return response()->noContent();
})->where('any', '.*');
