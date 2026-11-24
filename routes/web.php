<?php

use App\Http\Controllers\ProfileController;
use App\Models\Candidate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;

use App\Http\Controllers\VoteController;
use App\Http\Controllers\Admin\CountController;
use App\Http\Controllers\RedirectRoleController;
use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\DashboardController;
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
Route::get('/generateNewUsername', [GeneratorController::class, 'generateUsername']);
Route::get('/generateNewPassword', [GeneratorController::class, 'generatePassword']);

Route::get('/vote', function () {
    $now = Carbon::now();
    $this->param['user'] = Auth::user();
    $this->param['candidates'] = Candidate::all();
    return view('vote_closed', $this->param);
    // if ($now->gte(Carbon::createFromTime(07, 0, 0))) {
    //     return view('vote_page', $this->param);
    // }
    // else if ($now->gte(Carbon::createFromTime(15, 0, 0))) {
    //     return view('vote_closed', $this->param);
    // }
    // else {
    //     return view('vote_prepare', $this->param);
    // }
})->middleware('auth');

// Route::get('/vote', [VoteController::class, 'vote_page'])->middleware('auth');
Route::post('/vote-paslon', [VoteController::class, 'vote'])->middleware('auth');

Route::prefix('/back-admin-logged')->group(function () {
    Route::get('/redirect-role', [RedirectRoleController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/candidate', [CandidateController::class, 'index']);
    Route::get('/candidate/add', [CandidateController::class, 'add']);
    Route::post('/candidate/store', [CandidateController::class, 'store']);
    Route::post('/candidate/edit/{candidate}', [CandidateController::class, 'edit']);
    Route::put('/candidate/update/{candidate}', [CandidateController::class, 'update']);
    Route::delete('/candidate/delete/{candidate}', [CandidateController::class, 'destroy']);

    Route::get('/participant', [ParticipantController::class, 'index']);
    Route::get('/participant/add', [ParticipantController::class, 'add']);
    Route::get('/participant/broadcast', [ParticipantController::class, 'broadcast']);
    Route::post('/participant/store', [ParticipantController::class, 'store']);
    Route::post('/participant/edit/{participant}', [ParticipantController::class, 'edit']);
    Route::put('/participant/update/{participant}', [ParticipantController::class, 'update']);
    Route::delete('/participant/delete/{participant}', [ParticipantController::class, 'destroy']);
    Route::post('/participant/import', [ParticipantController::class, 'import']);
    Route::post('/participant/email-backup/{participant}', [ParticipantController::class, 'email_backup']);

    Route::get('/participant-verification', [ParticipantController::class, 'verification']);
    Route::post('/participant-verification/verified', [ParticipantController::class, 'sendVerified']);
    Route::post('/participant-verification/edit/{participant}', [ParticipantController::class, 'editVerification']);
    Route::put('/participant-verification/update/{participant}', [ParticipantController::class, 'updateVerification']);
    Route::post('/participant-verification/info/{participant}', [ParticipantController::class, 'infoVerification']);
    Route::get('/participant-verification/info/print/{participant}', [ParticipantController::class, 'printVerification']);

    Route::get('/live-count', [CountController::class, 'index']);
});

require __DIR__.'/auth.php';
