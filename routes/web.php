<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectMediaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/showcase', [HomeController::class, 'showcase'])->name('showcase');
Route::get('/showcase/{project}', [HomeController::class, 'showcaseProject'])->name('showcase.show');
Route::get('/mentoring', [HomeController::class, 'mentoring'])->name('mentoring');
Route::view('/kebijakan-privasi', 'privacy')->name('privacy');
Route::view('/syarat-ketentuan', 'terms')->name('terms');
Route::get('/media/{path}', [ProjectMediaController::class, 'show'])
    ->where('path', '.*')
    ->middleware('active.user')
    ->name('media.show');
Route::get('/storage/{path}', [ProjectMediaController::class, 'show'])
    ->where('path', '.*')
    ->middleware('active.user')
    ->name('storage.media.show');

// Halaman login: hanya untuk yang belum login
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.submit');
});

// Semua yang sudah login
Route::middleware(['auth', 'active.user'])->group(function () {
    // Temporary storage diagnostics
    Route::get('/_diag/storage', function () {
        return response()->json([
            'media_disk' => config('filesystems.project_media_disk', 'public'),
            's3_config' => [
                'key_present' => filled(config('filesystems.disks.s3.key')),
                'secret_present' => filled(config('filesystems.disks.s3.secret')),
                'region_present' => filled(config('filesystems.disks.s3.region')),
                'bucket_present' => filled(config('filesystems.disks.s3.bucket')),
                'endpoint_present' => filled(config('filesystems.disks.s3.endpoint')),
            ],
            'php_environment' => [
                'key_present' => (bool) getenv('AWS_ACCESS_KEY_ID'),
                'secret_present' => (bool) getenv('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);
    })->name('diagnostics.storage');
    Route::get('/landing', [HomeController::class, 'landingRole'])->name('landing');
    Route::get('/dashboard/peserta', [HomeController::class, 'dashboardPeserta'])->name('dashboard.peserta');
    Route::get('/dashboard/profile', [HomeController::class, 'profile'])->name('dashboard.profile');
    Route::put('/dashboard/profile', [HomeController::class, 'updateProfile'])->name('dashboard.profile.update');
    Route::get('/dashboard/mentor', [HomeController::class, 'dashboardMentor'])->name('dashboard.mentor');
    Route::get('/dashboard/mentor/evaluations', [HomeController::class, 'mentorEvaluationHistory'])->name('dashboard.mentor.evaluations');
    Route::get('/dashboard/mentor/schedules', [HomeController::class, 'mentorSchedules'])->name('dashboard.mentor.schedules');
    Route::post('/dashboard/mentor/schedules', [HomeController::class, 'storeMentorSchedule'])->name('dashboard.mentor.schedules.store');
    Route::put('/dashboard/mentor/schedules/{scheduleId}', [HomeController::class, 'updateMentorSchedule'])->name('dashboard.mentor.schedules.update');
    Route::patch('/dashboard/mentor/schedules/{scheduleId}/cancel', [HomeController::class, 'cancelMentorSchedule'])->name('dashboard.mentor.schedules.cancel');
    Route::get('/dashboard/pengurus', [HomeController::class, 'dashboardPengurus'])->name('dashboard.pengurus');
    Route::get('/dashboard/pengurus/approval-publikasi', [HomeController::class, 'pengurusApprovals'])->name('dashboard.pengurus.approvals');
    Route::get('/dashboard/pengurus/approval-publikasi/{project}', [HomeController::class, 'showPengurusApproval'])->name('dashboard.pengurus.approval.show');
    Route::post('/dashboard/pengurus/approval-publikasi/{project}', [HomeController::class, 'storePengurusApproval'])->name('dashboard.pengurus.approval.store');
    Route::get('/dashboard/pengurus/audit-trail', [HomeController::class, 'pengurusAuditTrail'])->name('dashboard.pengurus.audit');
    Route::get('/dashboard/pengurus/users', [HomeController::class, 'pengurusUsers'])->name('dashboard.pengurus.users');
    Route::put('/dashboard/pengurus/users/{userId}', [HomeController::class, 'updatePengurusUser'])->name('dashboard.pengurus.users.update');
    Route::patch('/dashboard/pengurus/users/{userId}/status', [HomeController::class, 'togglePengurusUserStatus'])->name('dashboard.pengurus.users.status');
    Route::get('/dashboard/pengurus/settings', [HomeController::class, 'pengurusSettings'])->name('dashboard.pengurus.settings');
    Route::put('/dashboard/pengurus/settings/active-period', [HomeController::class, 'updatePengurusActivePeriod'])->name('dashboard.pengurus.settings.period');
    Route::get('/dashboard/mentor/projects/{project}/review', [HomeController::class, 'showMentorReview'])->name('dashboard.mentor.project.review');
    Route::post('/dashboard/mentor/projects/{project}/review', [HomeController::class, 'storeMentorReview'])->name('dashboard.mentor.project.review.store');
    Route::get('/dashboard/peserta/projects', [HomeController::class, 'participantProjects'])->name('dashboard.peserta.projects.index');
    Route::get('/dashboard/peserta/projects/create', [HomeController::class, 'createProject'])->name('dashboard.peserta.project.create');
    Route::post('/dashboard/peserta/projects', [HomeController::class, 'storeProject'])->name('dashboard.peserta.project.store');
    Route::get('/dashboard/peserta/project/{projectId}/edit', [HomeController::class, 'editProject'])->name('dashboard.peserta.project.edit');
    Route::put('/dashboard/peserta/project/{projectId}', [HomeController::class, 'updateProject'])->name('dashboard.peserta.project.update');
    Route::get('/dashboard/peserta/projects/{project}', [HomeController::class, 'showParticipantProject'])->name('dashboard.peserta.project.show');
    Route::post('/dashboard/peserta/projects/{project}/resubmit', [HomeController::class, 'resubmitProject'])->name('dashboard.peserta.project.resubmit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
