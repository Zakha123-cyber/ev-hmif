<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\VoteController;
use App\Http\Controllers\Admin\CountController;
use App\Http\Controllers\RedirectRoleController;
use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ContactPersonController;
use App\Http\Controllers\Admin\ElectionScheduleController;
use App\Http\Controllers\Admin\GeneratorController;
use App\Http\Controllers\Admin\ParticipantController;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class, 'index']);

Route::get('/vote', [VoteController::class, 'vote_page'])->middleware('auth');
Route::post('/vote-paslon', [VoteController::class, 'vote'])->middleware(['auth', 'voting.open']);

Route::prefix('/back-admin-logged')->middleware('auth')->group(function () {
    Route::get('/redirect-role', [RedirectRoleController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/candidate', [CandidateController::class, 'index']);
    Route::get('/candidate/add', [CandidateController::class, 'add']);
    Route::post('/candidate/store', [CandidateController::class, 'store']);
    Route::match(['get', 'post'], '/candidate/edit/{candidate}', [CandidateController::class, 'edit']);
    Route::put('/candidate/update/{candidate}', [CandidateController::class, 'update']);
    Route::delete('/candidate/delete/{candidate}', [CandidateController::class, 'destroy']);

    Route::get('/participant', [ParticipantController::class, 'index']);
    Route::get('/participant/add', [ParticipantController::class, 'add']);
    Route::post('/participant/broadcast', [ParticipantController::class, 'broadcast']);
    Route::post('/participant/store', [ParticipantController::class, 'store']);
    Route::match(['get', 'post'], '/participant/edit/{participant}', [ParticipantController::class, 'edit']);
    Route::put('/participant/update/{participant}', [ParticipantController::class, 'update']);
    Route::delete('/participant/delete/{participant}', [ParticipantController::class, 'destroy']);
    Route::post('/participant/import', [ParticipantController::class, 'import']);
    Route::post('/participant/email-backup/{participant}', [ParticipantController::class, 'email_backup']);

    Route::get('/participant-verification', [ParticipantController::class, 'verification']);
    Route::post('/participant-verification/verified', [ParticipantController::class, 'sendVerified']);
    Route::match(['get', 'post'], '/participant-verification/edit/{participant}', [ParticipantController::class, 'editVerification']);
    Route::put('/participant-verification/update/{participant}', [ParticipantController::class, 'updateVerification']);
    Route::match(['get', 'post'], '/participant-verification/info/{participant}', [ParticipantController::class, 'infoVerification']);
    Route::get('/participant-verification/info/print/{participant}', [ParticipantController::class, 'printVerification']);

    Route::get('/live-count', [CountController::class, 'index']);

    Route::get('/generate-username', [GeneratorController::class, 'generateUsername']);
    Route::get('/generate-password', [GeneratorController::class, 'generatePassword']);

    Route::get('/contact-person', [ContactPersonController::class, 'index']);
    Route::post('/contact-person', [ContactPersonController::class, 'store']);
    Route::get('/contact-person/{contact}/edit', [ContactPersonController::class, 'edit']);
    Route::put('/contact-person/{contact}', [ContactPersonController::class, 'update']);
    Route::delete('/contact-person/{contact}', [ContactPersonController::class, 'destroy']);

    Route::get('/election-schedule', [ElectionScheduleController::class, 'edit']);
    Route::put('/election-schedule', [ElectionScheduleController::class, 'update']);
});

require __DIR__.'/auth.php';
