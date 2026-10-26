<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Halaman login: hanya untuk yang belum login
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.submit');
});

// Semua yang sudah login
Route::middleware('auth')->group(function () {
    Route::get('/landing', [HomeController::class, 'landingRole'])->name('landing');
    Route::get('/dashboard/peserta', [HomeController::class, 'dashboardPeserta'])->name('dashboard.peserta');
    Route::get('/dashboard/mentor', [HomeController::class, 'dashboardMentor'])->name('dashboard.mentor');
    Route::get('/dashboard/pengurus', [HomeController::class, 'dashboardPengurus'])->name('dashboard.pengurus');
    Route::get('/dashboard/pengurus/approval-publikasi', [HomeController::class, 'pengurusApprovals'])->name('dashboard.pengurus.approvals');
    Route::get('/dashboard/pengurus/approval-publikasi/{project}', [HomeController::class, 'showPengurusApproval'])->name('dashboard.pengurus.approval.show');
    Route::post('/dashboard/pengurus/approval-publikasi/{project}', [HomeController::class, 'storePengurusApproval'])->name('dashboard.pengurus.approval.store');
    Route::get('/dashboard/pengurus/audit-trail', [HomeController::class, 'pengurusAuditTrail'])->name('dashboard.pengurus.audit');
    Route::get('/dashboard/mentor/projects/{project}/review', [HomeController::class, 'showMentorReview'])->name('dashboard.mentor.project.review');
    Route::post('/dashboard/mentor/projects/{project}/review', [HomeController::class, 'storeMentorReview'])->name('dashboard.mentor.project.review.store');
    Route::get('/dashboard/peserta/projects', [HomeController::class, 'participantProjects'])->name('dashboard.peserta.projects.index');
    Route::get('/dashboard/peserta/projects/create', [HomeController::class, 'createProject'])->name('dashboard.peserta.project.create');
    Route::post('/dashboard/peserta/projects', [HomeController::class, 'storeProject'])->name('dashboard.peserta.project.store');
    Route::get('/dashboard/peserta/projects/{project}', [HomeController::class, 'showParticipantProject'])->name('dashboard.peserta.project.show');
    Route::post('/dashboard/peserta/projects/{project}/resubmit', [HomeController::class, 'resubmitProject'])->name('dashboard.peserta.project.resubmit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
