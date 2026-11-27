<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

// Akun dibuat oleh admin (import + broadcast / verifikasi manual), jadi aplikasi ini
// hanya butuh login & logout — tidak ada registrasi, reset password, atau verifikasi email.

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Login admin di alamat terpisah (config/election.php -> admin_login_path).
    Route::get(config('election.admin_login_path'), [AuthenticatedSessionController::class, 'createAdmin'])
                ->name('admin.login');
    Route::post(config('election.admin_login_path'), [AuthenticatedSessionController::class, 'storeAdmin']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');
});
