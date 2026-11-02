<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createTestUser(string $roleName = 'Peserta', string $email = 'andi@example.com', string $name = 'Andi Peserta'): void
{
    DB::table('roles')->insert([
        'role_id' => 'ROL-001',
        'role_name' => $roleName,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('users')->insert([
        'user_id' => 'USR-001',
        'role_id' => 'ROL-001',
        'name' => $name,
        'email' => $email,
        'password_hash' => Hash::make('password123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function createMentorReviewFixture(string $status = 'Submitted'): array
{
    createTestUser('Mentor', 'budi@example.com', 'Budi Mentor');
    $mentor = \App\Models\User::where('email', 'budi@example.com')->firstOrFail();

    DB::table('roles')->insert([
        'role_id' => 'ROL-002',
        'role_name' => 'Peserta',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('users')->insert([
        'user_id' => 'USR-002',
        'role_id' => 'ROL-002',
        'name' => 'Andi Peserta',
        'email' => 'andi@example.com',
        'password_hash' => Hash::make('password123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('mentoring_fields')->insert([
        'field_id' => 'FLD-001',
        'field_name' => 'Game Dev',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('mentoring_periods')->insert([
        'period_id' => 'PRD-001',
        'period_name' => 'Semester Genap 2026',
        'is_active' => true,
        'start_date' => '2026-02-01',
        'end_date' => '2026-07-31',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('projects')->insert([
        'project_id' => 'PRJ-REVIEW-001',
        'field_id' => 'FLD-001',
        'period_id' => 'PRD-001',
        'created_by' => 'USR-002',
        'title' => 'Petualangan Nusantara',
        'description' => 'Game edukasi budaya nusantara.',
        'project_link' => 'https://example.com/demo',
        'status' => $status,
        'submitted_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('project_members')->insert([
        'project_id' => 'PRJ-REVIEW-001',
        'user_id' => 'USR-002',
        'member_role' => 'Programmer',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('project_tools')->insert([
        'tool_id' => 'TLS-001',
        'project_id' => 'PRJ-REVIEW-001',
        'tool_name' => 'Unity',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('project_files')->insert([
        'file_id' => 'FIL-001',
        'project_id' => 'PRJ-REVIEW-001',
        'file_name' => 'Dokumentasi.pdf',
        'file_path' => 'projects/dokumentasi.pdf',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('project_versions')->insert([
        'version_id' => 'VER-001',
        'project_id' => 'PRJ-REVIEW-001',
        'version_number' => 'v1.1',
        'changelog' => 'Memperbarui dokumentasi dan memperbaiki bug.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [
        $mentor,
        \App\Models\User::where('email', 'andi@example.com')->firstOrFail(),
        'PRJ-REVIEW-001',
    ];
}

test('the login page displays role selection buttons', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Masuk Sebagai')
        ->assertSee('Peserta')
        ->assertSee('Mentor')
        ->assertSee('Pengurus');
});

test('the public landing page keeps public content and shows the authenticated role navigation', function () {
    createTestUser();
    $user = \App\Models\User::where('email', 'andi@example.com')->firstOrFail();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('ARSIP AKADEMIK & SHOWCASE MULTIMEDIA', false)
        ->assertDontSee('Selamat datang, Andi Peserta')
        ->assertSee('Dashboard Peserta')
        ->assertSee('Andi Peserta')
        ->assertSee('(Peserta)')
        ->assertSee('Keluar')
        ->assertDontSee('Masuk</a>', false);
});

test('an authenticated user is redirected from the login form to landing', function () {
    createTestUser();
    $user = \App\Models\User::where('email', 'andi@example.com')->firstOrFail();

    $this->actingAs($user)
        ->get(route('login'))
        ->assertRedirect(route('landing'));
});

test('a participant logs in and sees the participant dashboard', function () {
    createTestUser();

    $response = $this->post(route('login.submit'), [
        'email' => 'andi@example.com',
        'password' => 'password123',
        'role' => 'Peserta',
    ]);

    $response->assertRedirect(route('landing'));
    $this->assertAuthenticated();

    $this->get(route('landing'))
        ->assertOk()
        ->assertSee('Selamat datang,')
        ->assertSee('Andi Peserta')
        ->assertSee('Andi Peserta')
        ->assertSee('(Peserta)')
        ->assertSee('Dashboard Peserta')
        ->assertSee(route('dashboard.peserta'))
        ->assertSee('4 Bidang Mentoring')
        ->assertDontSee('Showcase Capstone')
        ->assertHeader('Cache-Control', 'must-revalidate, no-cache, no-store, private');
});

test('a mentor can log in with the mentor role selected', function () {
    createTestUser('Mentor', 'budi@example.com', 'Budi Mentor');

    $this->post(route('login.submit'), [
        'email' => 'budi@example.com',
        'password' => 'password123',
        'role' => 'Mentor',
    ])->assertRedirect(route('landing'));

    $this->assertSame('USR-001', auth()->id());

    $this->get(route('landing'))
        ->assertOk()
        ->assertSee('Selamat datang,')
        ->assertSee('Budi Mentor')
        ->assertSee('Budi Mentor')
        ->assertSee('(Mentor)')
        ->assertSee('Dashboard Mentor')
        ->assertSee(route('dashboard.mentor'))
        ->assertSee('4 Bidang Mentoring')
        ->assertDontSee('Selamat datang, Andi Peserta');
});

test('an administrator sees the role dashboard link and capstone monitoring dashboard', function () {
    createTestUser('Pengurus KSM', 'budi.santoso@example.com', 'Budi Santoso');
    $administrator = \App\Models\User::where('email', 'budi.santoso@example.com')->firstOrFail();

    DB::table('roles')->insert([
        'role_id' => 'ROL-002',
        'role_name' => 'Mentor',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('users')->insert([
        'user_id' => 'USR-002',
        'role_id' => 'ROL-002',
        'name' => 'Fajar Mentor',
        'email' => 'fajar@example.com',
        'password_hash' => Hash::make('password123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('roles')->insert([
        'role_id' => 'ROL-003',
        'role_name' => 'Peserta',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('users')->insert([
        'user_id' => 'USR-003',
        'role_id' => 'ROL-003',
        'name' => 'Nexus Lab',
        'email' => 'nexus@example.com',
        'password_hash' => Hash::make('password123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('mentoring_fields')->insert([
        'field_id' => 'FLD-001',
        'field_name' => 'Game Dev',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('mentoring_fields')->insert([
        'field_id' => 'FLD-002',
        'field_name' => 'Web Dev',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('mentoring_periods')->insert([
        'period_id' => 'PRD-001',
        'period_name' => 'Semester Genap 2026',
        'is_active' => true,
        'start_date' => '2026-02-01',
        'end_date' => '2026-07-31',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach ([
        ['PRJ-ADMIN-001', 'Petualangan Nusantara', 'Approved', 'FLD-001'],
        ['PRJ-ADMIN-002', 'Portal Riset Kolaboratif', 'Published', 'FLD-002'],
        ['PRJ-ADMIN-003', 'Arsip Game Lama', 'Archived', 'FLD-001'],
        ['PRJ-ADMIN-004', 'Dalam Review Mentor', 'In Review', 'FLD-001'],
    ] as [$projectId, $title, $status, $fieldId]) {
        DB::table('projects')->insert([
            'project_id' => $projectId,
            'field_id' => $fieldId,
            'period_id' => 'PRD-001',
            'created_by' => 'USR-003',
            'title' => $title,
            'status' => $status,
            'submitted_at' => now(),
            'published_at' => $status === 'Published' ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    DB::table('project_members')->insert([
        'project_id' => 'PRJ-ADMIN-001',
        'user_id' => 'USR-003',
        'member_role' => 'Programmer',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('reviews')->insert([
        'review_id' => 'REV-ADMIN-0001',
        'project_id' => 'PRJ-ADMIN-001',
        'reviewer_id' => 'USR-002',
        'comment' => 'Direkomendasikan untuk publikasi.',
        'rating' => '5',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('audit_logs')->insert([
        'log_id' => 'LOG-ADMIN-0001',
        'user_id' => $administrator->user_id,
        'project_id' => 'PRJ-ADMIN-001',
        'action' => 'sync_status',
        'description' => 'Status karya diperiksa pengurus.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    for ($index = 1; $index <= 6; $index++) {
        DB::table('projects')->insert([
            'project_id' => 'PRJ-ADMIN-' . str_pad((string) (4 + $index), 3, '0', STR_PAD_LEFT),
            'field_id' => 'FLD-001',
            'period_id' => 'PRD-001',
            'created_by' => 'USR-003',
            'title' => 'Capstone Tambahan ' . $index,
            'status' => 'Approved',
            'submitted_at' => now()->subMinutes($index),
            'created_at' => now()->subMinutes($index),
            'updated_at' => now()->subMinutes($index),
        ]);
    }

    $this->assertDatabaseHas('projects', [
        'project_id' => 'PRJ-ADMIN-001',
        'status' => 'Approved',
    ]);
    $this->assertSame(7, \App\Models\Project::where('status', 'Approved')->count());

    $this->actingAs($administrator)
        ->get(route('landing'))
        ->assertOk()
        ->assertSee('Dashboard Pengurus')
        ->assertSee(route('dashboard.pengurus'));

    $dashboardResponse = $this->get(route('dashboard.pengurus'))
        ->assertOk()
        ->assertSee('Dashboard Monitoring &amp; Kurasi Capstone', false)
        ->assertSee('PORTAL PENGURUS')
        ->assertSee('Approval Publikasi')
        ->assertSee('Log Audit Trail')
        ->assertSee('Kelola Pengguna')
        ->assertSee(route('dashboard.pengurus.settings'))
        ->assertSee('Menunggu Persetujuan')
        ->assertSee('Petualangan Nusantara')
        ->assertSee('ID: PRJ-ADMIN-001')
        ->assertSee('Fajar Mentor')
        ->assertSee('5/5')
        ->assertDontSee('Arsip Game Lama')
        ->assertDontSee('Portal Riset Kolaboratif')
        ->assertDontSee('Dalam Review Mentor')
        ->assertSee('Budi Santoso')
        ->assertSee('Status karya diperiksa pengurus.')
        ->assertSee('100.0%');
    $this->assertSame(5, substr_count($dashboardResponse->getContent(), 'pengurus-approval-button'));
    $this->assertSame(7, \App\Models\Project::where('status', 'Approved')->count());

    $this->get(route('dashboard.pengurus.approvals'))
        ->assertOk()
        ->assertSee('Petualangan Nusantara')
        ->assertSee('Capstone Tambahan 6');

    $this->get(route('dashboard.pengurus.approvals', ['field' => 'FLD-001']))
        ->assertOk()
        ->assertSee('ID: PRJ-ADMIN-001')
        ->assertDontSee('ID: PRJ-ADMIN-002');

    $this->get(route('dashboard.pengurus.audit'))
        ->assertOk()
        ->assertSee('Log Audit Trail Aktivitas Kurasi')
        ->assertSee('Status karya diperiksa pengurus.')
        ->assertSee('PRJ-ADMIN-001');
});

test('administrator approves, requests publication, and records each publication action', function () {
    [$administrator, , $projectId] = createMentorReviewFixture('Approved');
    DB::table('roles')->where('role_id', 'ROL-001')->update(['role_name' => 'Pengurus KSM']);
    DB::table('roles')->insert([
        'role_id' => 'ROL-003',
        'role_name' => 'Mentor',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('users')->insert([
        'user_id' => 'USR-003',
        'role_id' => 'ROL-003',
        'name' => 'Fajar Mentor',
        'email' => 'fajar@example.com',
        'password_hash' => Hash::make('password123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('reviews')->insert([
        'review_id' => 'REV-ADMIN-REVIEW',
        'project_id' => $projectId,
        'reviewer_id' => 'USR-003',
        'comment' => 'Project layak direkomendasikan ke showcase.',
        'rating' => '4',
        'rubric_scores' => json_encode([
            'technical_quality' => ['score' => 5, 'weight' => 40],
            'creative_quality' => ['score' => 3, 'weight' => 30],
            'publication_readiness' => ['score' => 4, 'weight' => 30],
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $administrator->refresh();
    $this->actingAs($administrator)
        ->get(route('dashboard.pengurus.approval.show', $projectId))
        ->assertOk()
        ->assertSee('Persetujuan Publikasi')
        ->assertSee('Checklist Standar Etalase')
        ->assertSee('Catatan Rekomendasi Mentor')
        ->assertSee('Project layak direkomendasikan ke showcase.')
        ->assertSee('Berikan Approval Resmi Pengurus');

    $this->post(route('dashboard.pengurus.approval.store', $projectId), [
        'action' => 'approve',
    ])->assertRedirect(route('dashboard.pengurus.approval.show', $projectId));

    $this->assertDatabaseHas('projects', [
        'project_id' => $projectId,
        'status' => 'Approved',
        'publication_approved_by' => 'USR-001',
    ]);
    $this->assertDatabaseHas('audit_logs', [
        'action' => 'PUBLICATION_APPROVED',
        'project_id' => $projectId,
        'user_id' => 'USR-001',
    ]);

    $this->post(route('dashboard.pengurus.approval.store', $projectId), [
        'action' => 'publish',
        'showcase' => '1',
    ])->assertSessionHasErrors([
        'checklist.thumbnail',
        'checklist.rights',
        'checklist.demo',
        'checklist.originality',
    ]);
    $this->assertDatabaseHas('projects', ['project_id' => $projectId, 'status' => 'Approved']);

    $this->post(route('dashboard.pengurus.approval.store', $projectId), [
        'action' => 'publish',
        'showcase' => '1',
        'checklist' => [
            'thumbnail' => '1',
            'rights' => '1',
            'demo' => '1',
            'originality' => '1',
        ],
    ])
        ->assertRedirect(route('dashboard.pengurus.approval.show', $projectId))
        ->assertSessionHas('status', 'Project berhasil dipublikasikan ke showcase.');

    $this->assertDatabaseHas('projects', [
        'project_id' => $projectId,
        'status' => 'Published',
        'is_featured' => true,
    ]);
    $this->assertDatabaseHas('audit_logs', [
        'action' => 'PROJECT_PUBLISHED',
        'project_id' => $projectId,
        'user_id' => 'USR-001',
    ]);
    $this->get(route('dashboard.pengurus.audit'))
        ->assertOk()
        ->assertSee('PUBLICATION_APPROVED')
        ->assertSee('PROJECT_PUBLISHED')
        ->assertSee($projectId);
});

test('only administrators can open the administrator dashboard', function () {
    createTestUser();
    $participant = \App\Models\User::where('email', 'andi@example.com')->firstOrFail();

    $this->actingAs($participant)
        ->get(route('dashboard.pengurus'))
        ->assertForbidden();
    $this->get(route('dashboard.pengurus.approvals'))
        ->assertForbidden();
    $this->get(route('dashboard.pengurus.audit'))
        ->assertForbidden();

    auth()->logout();
    $this->get(route('dashboard.pengurus'))
        ->assertRedirect(route('login'));
});

test('an administrator can return an approved project with a revision note', function () {
    [$administrator, , $projectId] = createMentorReviewFixture('Approved');
    DB::table('roles')->where('role_id', 'ROL-001')->update(['role_name' => 'Pengurus KSM']);
    $administrator->refresh();

    $this->actingAs($administrator)
        ->post(route('dashboard.pengurus.approval.store', $projectId), [
            'action' => 'request_revision',
            'comment' => 'Mohon lengkapi bukti lisensi aset visual.',
        ])
        ->assertRedirect(route('dashboard.pengurus.approvals'))
        ->assertSessionHas('status', 'Catatan revisi berhasil dikirim kepada peserta.');

    $this->assertDatabaseHas('projects', [
        'project_id' => $projectId,
        'status' => 'Revision Required',
    ]);
    $this->assertDatabaseHas('reviews', [
        'project_id' => $projectId,
        'reviewer_id' => 'USR-001',
        'comment' => 'Mohon lengkapi bukti lisensi aset visual.',
        'rating' => null,
    ]);
    $this->assertDatabaseHas('audit_logs', [
        'action' => 'REVISION_REQUESTED',
        'project_id' => $projectId,
    ]);
});

test('a mentor can open the mentor review dashboard and filter its queue', function () {
    createTestUser('Mentor', 'budi@example.com', 'Budi Mentor');
    $mentor = \App\Models\User::where('email', 'budi@example.com')->firstOrFail();

    DB::table('roles')->insert([
        'role_id' => 'ROL-002',
        'role_name' => 'Peserta',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('users')->insert([
        'user_id' => 'USR-002',
        'role_id' => 'ROL-002',
        'name' => 'Andi Peserta',
        'email' => 'andi@example.com',
        'password_hash' => Hash::make('password123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('mentoring_fields')->insert([
        'field_id' => 'FLD-001',
        'field_name' => 'Game Dev',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('mentoring_periods')->insert([
        'period_id' => 'PRD-001',
        'period_name' => 'Semester Genap 2026',
        'is_active' => true,
        'start_date' => '2026-02-01',
        'end_date' => '2026-07-31',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach ([
        ['PRJ-QUEUE-001', 'Petualangan Nusantara', 'Submitted'],
        ['PRJ-QUEUE-002', 'Knight 2D Quest', 'In Review'],
        ['PRJ-QUEUE-003', 'Echoes of Java', 'Resubmitted'],
        ['PRJ-QUEUE-004', 'Project Arsip', 'Revision Required'],
    ] as [$projectId, $title, $status]) {
        DB::table('projects')->insert([
            'project_id' => $projectId,
            'field_id' => 'FLD-001',
            'period_id' => 'PRD-001',
            'created_by' => 'USR-002',
            'title' => $title,
            'status' => $status,
            'submitted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    DB::table('project_members')->insert([
        'project_id' => 'PRJ-QUEUE-001',
        'user_id' => 'USR-002',
        'member_role' => 'Programmer',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('reviews')->insert([
        'review_id' => 'REV-000000000000001',
        'project_id' => 'PRJ-QUEUE-004',
        'reviewer_id' => $mentor->user_id,
        'comment' => 'Silakan lengkapi dokumentasi project.',
        'rating' => '4',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($mentor)
        ->get(route('dashboard.mentor'))
        ->assertOk()
        ->assertSee('Antrian Review Masuk')
        ->assertSee('Antrian Review')
        ->assertSee('participant-sidebar')
        ->assertSee('participant-sidebar-shell')
        ->assertSee('sidebar-link active')
        ->assertSee('Riwayat Evaluasi')
        ->assertSee('Jadwal Mentoring')
        ->assertSee('Profil')
        ->assertDontSee('Navigasi Utama')
        ->assertSee('PORTAL MENTOR')
        ->assertSee('Kembali ke Beranda')
        ->assertSee('Kebijakan Privasi')
        ->assertDontSee('Panduan Penilaian')
        ->assertSee(route('dashboard.mentor.evaluations'))
        ->assertSee(route('dashboard.mentor.schedules'))
        ->assertSee('Semester Genap 2026')
        ->assertSee('Petualangan Nusantara')
        ->assertSee('Knight 2D Quest')
        ->assertSee('Echoes of Java')
        ->assertDontSee('Project Arsip')
        ->assertSee('Menunggu Review')
        ->assertSee('Revisi Masuk')
        ->assertSee('Selesai Ditinjau');

    $this->get(route('dashboard.mentor', ['status' => 'Resubmitted', 'q' => 'Echoes']))
        ->assertOk()
        ->assertSee('Echoes of Java')
        ->assertDontSee('Petualangan Nusantara')
        ->assertDontSee('Knight 2D Quest');
});

test('a mentor can view only their own evaluation history', function () {
    [$mentor, , $projectId] = createMentorReviewFixture();
    DB::table('users')->insert([
        'user_id' => 'USR-OTHER-MENTOR',
        'role_id' => 'ROL-001',
        'name' => 'Mentor Lain',
        'email' => 'mentor-lain@example.com',
        'password_hash' => Hash::make('password123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('reviews')->insert([
        [
            'review_id' => 'REV-HISTORY-OWN',
            'project_id' => $projectId,
            'reviewer_id' => $mentor->user_id,
            'comment' => 'Feedback dari mentor yang login.',
            'rating' => '4',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'review_id' => 'REV-HISTORY-OTHER',
            'project_id' => $projectId,
            'reviewer_id' => 'USR-OTHER-MENTOR',
            'comment' => 'Feedback milik mentor lain.',
            'rating' => '2',
            'created_at' => now()->subMinute(),
            'updated_at' => now()->subMinute(),
        ],
    ]);

    $this->actingAs($mentor)
        ->get(route('dashboard.mentor.evaluations'))
        ->assertOk()
        ->assertSee('Riwayat Evaluasi')
        ->assertSee('PORTAL MENTOR')
        ->assertSee('Halo, Budi Mentor')
        ->assertSee('Kembali ke Beranda')
        ->assertSee('Petualangan Nusantara')
        ->assertSee('Feedback dari mentor yang login.')
        ->assertDontSee('Feedback milik mentor lain.');
});

test('a mentor can create update and cancel their mentoring schedules', function () {
    [$mentor, , $projectId] = createMentorReviewFixture();
    $this->actingAs($mentor);

    $this->get(route('dashboard.mentor.schedules'))
        ->assertOk()
        ->assertSee('Jadwal Mentoring')
        ->assertSee('Buat Jadwal Baru')
        ->assertSee('Petualangan Nusantara');

    $this->post(route('dashboard.mentor.schedules.store'), [
        'project_id' => $projectId,
        'starts_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
        'duration_minutes' => 60,
        'location' => 'Ruang Multimedia',
        'meeting_url' => '',
        'notes' => 'Siapkan demo project.',
    ])
        ->assertRedirect(route('dashboard.mentor.schedules'))
        ->assertSessionHas('status', 'Jadwal mentoring berhasil dibuat.');

    $schedule = DB::table('mentoring_schedules')->where('mentor_id', $mentor->user_id)->first();
    $this->assertNotNull($schedule);
    $this->assertSame($projectId, $schedule->project_id);
    $this->assertSame('Scheduled', $schedule->status);

    $updatedStart = now()->addDays(3)->format('Y-m-d\TH:i');
    $this->put(route('dashboard.mentor.schedules.update', $schedule->schedule_id), [
        'starts_at' => $updatedStart,
        'duration_minutes' => 90,
        'location' => '',
        'meeting_url' => 'https://meet.example.test/session',
        'notes' => 'Bawa pembaruan terakhir.',
    ])
        ->assertRedirect(route('dashboard.mentor.schedules'))
        ->assertSessionHas('status', 'Jadwal mentoring berhasil diperbarui.');

    $this->assertDatabaseHas('mentoring_schedules', [
        'schedule_id' => $schedule->schedule_id,
        'duration_minutes' => 90,
        'location' => null,
        'meeting_url' => 'https://meet.example.test/session',
        'notes' => 'Bawa pembaruan terakhir.',
    ]);

    $this->patch(route('dashboard.mentor.schedules.cancel', $schedule->schedule_id))
        ->assertRedirect(route('dashboard.mentor.schedules'))
        ->assertSessionHas('status', 'Jadwal mentoring berhasil dibatalkan.');

    $this->assertDatabaseHas('mentoring_schedules', [
        'schedule_id' => $schedule->schedule_id,
        'status' => 'Canceled',
    ]);
    $this->get(route('dashboard.mentor.schedules'))
        ->assertSee('Dibatalkan')
        ->assertDontSee('Ubah jadwal');
});

test('mentoring schedules require valid meeting details and are private to their mentor', function () {
    [$mentor, $participant, $projectId] = createMentorReviewFixture();
    $scheduleData = [
        'project_id' => $projectId,
        'starts_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
        'duration_minutes' => 60,
        'location' => '',
        'meeting_url' => '',
    ];

    $this->actingAs($mentor)
        ->post(route('dashboard.mentor.schedules.store'), $scheduleData)
        ->assertSessionHasErrors('location');
    $this->assertDatabaseCount('mentoring_schedules', 0);

    $this->post(route('dashboard.mentor.schedules.store'), [
        ...$scheduleData,
        'location' => 'Ruang Multimedia',
    ])->assertRedirect(route('dashboard.mentor.schedules'));

    $schedule = DB::table('mentoring_schedules')->first();
    DB::table('users')->insert([
        'user_id' => 'USR-OTHER-MENTOR',
        'role_id' => 'ROL-001',
        'name' => 'Mentor Lain',
        'email' => 'mentor-lain@example.com',
        'password_hash' => Hash::make('password123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $otherMentor = \App\Models\User::query()->findOrFail('USR-OTHER-MENTOR');
    $this->actingAs($otherMentor)
        ->patch(route('dashboard.mentor.schedules.cancel', $schedule->schedule_id))
        ->assertNotFound();

    $this->actingAs($participant)
        ->get(route('dashboard.mentor.schedules'))
        ->assertForbidden();
});

test('only mentors can open the mentor dashboard', function () {
    createTestUser();
    $participant = \App\Models\User::where('email', 'andi@example.com')->firstOrFail();

    $this->actingAs($participant)
        ->get(route('dashboard.mentor'))
        ->assertForbidden();

    auth()->logout();
    $this->get(route('dashboard.mentor'))
        ->assertRedirect(route('login'));
});

test('pengurus can search and update registered user accounts', function () {
    createTestUser('Pengurus KSM', 'pengurus@example.com', 'Pengurus Utama');
    DB::table('roles')->insert([
        ['role_id' => 'ROL-PESERTA', 'role_name' => 'Peserta', 'created_at' => now(), 'updated_at' => now()],
        ['role_id' => 'ROL-MENTOR', 'role_name' => 'Mentor', 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('users')->insert([
        'user_id' => 'USR-MANAGED',
        'role_id' => 'ROL-PESERTA',
        'name' => 'Peserta Dikelola',
        'email' => 'peserta@example.com',
        'password_hash' => Hash::make('password123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $pengurus = \App\Models\User::query()->findOrFail('USR-001');

    $this->actingAs($pengurus)
        ->get(route('dashboard.pengurus.users', ['q' => 'peserta@example.com']))
        ->assertOk()
        ->assertSee('Kelola Pengguna')
        ->assertSee('peserta@example.com')
        ->assertDontSee('password_hash')
        ->assertSee(route('dashboard.pengurus.settings'));

    $this->put(route('dashboard.pengurus.users.update', 'USR-MANAGED'), [
        'name' => 'Nama Diperbarui',
        'email' => 'nama.baru@example.com',
        'role_id' => 'ROL-MENTOR',
    ])->assertRedirect(route('dashboard.pengurus.users'))
        ->assertSessionHas('status', 'Data pengguna berhasil diperbarui.');

    $this->assertDatabaseHas('users', [
        'user_id' => 'USR-MANAGED',
        'name' => 'Nama Diperbarui',
        'email' => 'nama.baru@example.com',
        'role_id' => 'ROL-MENTOR',
    ]);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => 'USR-001',
        'action' => 'USER_UPDATED',
    ]);
});

test('pengurus can deactivate and reactivate accounts but cannot deactivate themselves or the last active pengurus', function () {
    createTestUser('Pengurus KSM', 'pengurus@example.com', 'Pengurus Utama');
    DB::table('roles')->insert([
        'role_id' => 'ROL-PESERTA',
        'role_name' => 'Peserta',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('users')->insert([
        'user_id' => 'USR-MANAGED',
        'role_id' => 'ROL-PESERTA',
        'name' => 'Peserta Dikelola',
        'email' => 'peserta@example.com',
        'password_hash' => Hash::make('password123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $pengurus = \App\Models\User::query()->findOrFail('USR-001');

    $this->actingAs($pengurus)
        ->patch(route('dashboard.pengurus.users.status', 'USR-001'))
        ->assertStatus(409);

    $this->patch(route('dashboard.pengurus.users.status', 'USR-MANAGED'))
        ->assertRedirect(route('dashboard.pengurus.users'));
    $this->assertDatabaseHas('users', ['user_id' => 'USR-MANAGED', 'is_active' => false]);

    auth()->logout();
    $this->post(route('login.submit'), [
        'email' => 'peserta@example.com',
        'password' => 'password123',
        'role' => 'Peserta',
    ])->assertSessionHasErrors('email');

    $this->actingAs($pengurus)
        ->patch(route('dashboard.pengurus.users.status', 'USR-MANAGED'))
        ->assertRedirect(route('dashboard.pengurus.users'));
    $this->assertDatabaseHas('users', ['user_id' => 'USR-MANAGED', 'is_active' => true]);

    $this->patch(route('dashboard.pengurus.users.status', 'USR-MANAGED'))
        ->assertRedirect(route('dashboard.pengurus.users'));
    $inactiveUser = \App\Models\User::query()->findOrFail('USR-MANAGED');
    $this->actingAs($inactiveUser)
        ->get(route('dashboard.peserta'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');
});

test('pengurus can set exactly one active mentoring period from settings', function () {
    createTestUser('Pengurus KSM', 'pengurus@example.com', 'Pengurus Utama');
    DB::table('mentoring_periods')->insert([
        [
            'period_id' => 'PERIOD-OLD',
            'period_name' => 'Semester Ganjil 2026',
            'is_active' => true,
            'start_date' => '2026-01-01',
            'end_date' => '2026-06-30',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'period_id' => 'PERIOD-NEW',
            'period_name' => 'Semester Genap 2026',
            'is_active' => false,
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $this->actingAs(\App\Models\User::query()->findOrFail('USR-001'))
        ->get(route('dashboard.pengurus.settings'))
        ->assertOk()
        ->assertSee('Pengaturan')
        ->assertSee('Periode Mentoring Aktif')
        ->assertSee('Semester Ganjil 2026')
        ->assertSee('Semester Genap 2026');

    $this->put(route('dashboard.pengurus.settings.period'), ['period_id' => 'PERIOD-NEW'])
        ->assertRedirect(route('dashboard.pengurus.settings'))
        ->assertSessionHas('status', 'Periode mentoring aktif berhasil diperbarui.');

    $this->assertDatabaseHas('mentoring_periods', ['period_id' => 'PERIOD-OLD', 'is_active' => false]);
    $this->assertDatabaseHas('mentoring_periods', ['period_id' => 'PERIOD-NEW', 'is_active' => true]);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => 'USR-001',
        'action' => 'ACTIVE_PERIOD_CHANGED',
    ]);
});

test('a mentor can review a queued project and submit a weighted rubric', function () {
    [$mentor, , $projectId] = createMentorReviewFixture();

    $this->actingAs($mentor)
        ->get(route('dashboard.mentor.project.review', $projectId))
        ->assertOk()
        ->assertSee('Review Project')
        ->assertSee('Petualangan Nusantara')
        ->assertSee('Semester Genap 2026')
        ->assertSee('Unity')
        ->assertSee('Dokumentasi.pdf')
        ->assertSee('Memperbarui dokumentasi dan memperbaiki bug.')
        ->assertSee('40%')
        ->assertSee('30%')
        ->assertSee('Rekomendasikan ke Pengurus');

    $this->post(route('dashboard.mentor.project.review.store', $projectId), [
        'rubric' => [
            'technical_quality' => 'exceptional',
            'creative_quality' => 'meets_standard',
            'publication_readiness' => 'needs_improvement',
        ],
        'comment' => 'Rapikan struktur kode dan lengkapi dokumentasi sebelum publikasi.',
        'decision' => 'Revision Required',
    ])
        ->assertRedirect(route('dashboard.mentor'))
        ->assertSessionHas('status', 'Review project berhasil disimpan.');

    $this->assertDatabaseHas('projects', [
        'project_id' => $projectId,
        'status' => 'Revision Required',
    ]);
    $review = DB::table('reviews')->where('project_id', $projectId)->first();
    $this->assertNotNull($review);
    $this->assertSame('USR-001', $review->reviewer_id);
    $this->assertSame('Rapikan struktur kode dan lengkapi dokumentasi sebelum publikasi.', $review->comment);
    $this->assertSame('3', $review->rating);
    $rubricScores = json_decode($review->rubric_scores, true, flags: JSON_THROW_ON_ERROR);
    $this->assertSame(40, $rubricScores['technical_quality']['weight']);
    $this->assertSame(30, $rubricScores['creative_quality']['weight']);
    $this->assertSame(1, $rubricScores['publication_readiness']['score']);
});

test('authenticated users can view submitted project thumbnails without exposing private project files', function () {
    [$mentor, $participant, $projectId] = createMentorReviewFixture();
    $thumbnailPath = "projects/{$projectId}/thumbnail.png";
    $privateFilePath = "projects/{$projectId}/documents/private.pdf";

    DB::table('users')->insert([
        'user_id' => 'USR-OTHER-PARTICIPANT',
        'role_id' => 'ROL-002',
        'name' => 'Peserta Lain',
        'email' => 'peserta-lain@example.com',
        'password_hash' => Hash::make('password123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('roles')->insert([
        'role_id' => 'ROL-003',
        'role_name' => 'Pengurus KSM',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('users')->insert([
        'user_id' => 'USR-PENGURUS',
        'role_id' => 'ROL-003',
        'name' => 'Pengurus',
        'email' => 'pengurus@example.com',
        'password_hash' => Hash::make('password123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Storage::fake('public');
    Storage::disk('public')->put($thumbnailPath, 'thumbnail-content');
    Storage::disk('public')->put($privateFilePath, 'private-content');
    DB::table('projects')
        ->where('project_id', $projectId)
        ->update(['thumbnail_url' => $thumbnailPath]);

    $this->actingAs($mentor)
        ->get(route('dashboard.mentor.project.review', $projectId))
        ->assertOk()
        ->assertSee(route('media.show', ['path' => $thumbnailPath], false), false)
        ->assertSee('Thumbnail Petualangan Nusantara');

    Storage::disk('public')->assertExists($thumbnailPath);

    $this->get(route('media.show', ['path' => $thumbnailPath]))
        ->assertOk();

    $this->actingAs($participant)
        ->get(route('media.show', ['path' => $thumbnailPath]))
        ->assertOk();

    $this->actingAs(\App\Models\User::query()->findOrFail('USR-002'))
        ->get(route('media.show', ['path' => $thumbnailPath]))
        ->assertOk();

    $this->actingAs(\App\Models\User::query()->findOrFail('USR-OTHER-PARTICIPANT'))
        ->get(route('media.show', ['path' => $thumbnailPath]))
        ->assertOk();

    $this->actingAs(\App\Models\User::query()->findOrFail('USR-PENGURUS'))
        ->get(route('media.show', ['path' => $thumbnailPath]))
        ->assertOk();

    $this->actingAs(\App\Models\User::query()->findOrFail('USR-OTHER-PARTICIPANT'))
        ->get(route('media.show', ['path' => $privateFilePath]))
        ->assertForbidden();

    $this->app['auth']->guard()->logout();
    $this->get(route('media.show', ['path' => $thumbnailPath]))
        ->assertForbidden();

    DB::table('projects')->where('project_id', $projectId)->update(['status' => 'Draft']);

    $this->actingAs(\App\Models\User::query()->findOrFail('USR-002'))
        ->get(route('media.show', ['path' => $thumbnailPath]))
        ->assertOk();

    $this->actingAs(\App\Models\User::query()->findOrFail('USR-OTHER-PARTICIPANT'))
        ->get(route('media.show', ['path' => $thumbnailPath]))
        ->assertForbidden();

    DB::table('projects')->where('project_id', $projectId)->update(['status' => 'Published']);

    $this->get(route('media.show', ['path' => $thumbnailPath]))
        ->assertOk();
});

test('mentor review requires each rubric score, feedback, and a valid decision', function () {
    [$mentor, , $projectId] = createMentorReviewFixture();

    $this->actingAs($mentor)
        ->post(route('dashboard.mentor.project.review.store', $projectId), [
            'rubric' => ['technical_quality' => 'invalid'],
            'comment' => str_repeat('x', 1001),
            'decision' => 'Published',
        ])
        ->assertSessionHasErrors([
            'rubric.technical_quality',
            'rubric.creative_quality',
            'rubric.publication_readiness',
            'comment',
            'decision',
        ]);

    $this->assertDatabaseHas('projects', [
        'project_id' => $projectId,
        'status' => 'Submitted',
    ]);
    $this->assertDatabaseCount('reviews', 0);
});

test('a mentor can recommend a fully reviewed project to the administrator', function () {
    [$mentor, , $projectId] = createMentorReviewFixture('Resubmitted');

    $this->actingAs($mentor)
        ->post(route('dashboard.mentor.project.review.store', $projectId), [
            'rubric' => [
                'technical_quality' => 'exceptional',
                'creative_quality' => 'exceptional',
                'publication_readiness' => 'exceptional',
            ],
            'comment' => 'Project memenuhi kriteria dan siap direkomendasikan.',
            'decision' => 'Approved',
        ])
        ->assertRedirect(route('dashboard.mentor'));

    $this->assertDatabaseHas('projects', [
        'project_id' => $projectId,
        'status' => 'Approved',
    ]);
    $this->assertDatabaseHas('reviews', [
        'project_id' => $projectId,
        'reviewer_id' => 'USR-001',
        'rating' => '5',
    ]);
});

test('only mentors can review projects and projects outside the queue are unavailable', function () {
    [$mentor, $participant, $projectId] = createMentorReviewFixture('Draft');

    $this->actingAs($participant)
        ->get(route('dashboard.mentor.project.review', $projectId))
        ->assertForbidden();

    $this->actingAs($mentor)
        ->get(route('dashboard.mentor.project.review', $projectId))
        ->assertNotFound();

    $this->post(route('dashboard.mentor.project.review.store', $projectId), [
        'rubric' => [
            'technical_quality' => 'exceptional',
            'creative_quality' => 'exceptional',
            'publication_readiness' => 'exceptional',
        ],
        'comment' => 'Siap.',
        'decision' => 'Approved',
    ])->assertNotFound();

    $this->assertDatabaseCount('reviews', 0);
});

test('an authenticated participant is redirected from posting login to landing', function () {
    createTestUser();
    $participant = \App\Models\User::where('email', 'andi@example.com')->firstOrFail();
    $this->actingAs($participant);

    DB::table('roles')->insert([
        'role_id' => 'ROL-002',
        'role_name' => 'Mentor',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('users')->insert([
        'user_id' => 'USR-002',
        'role_id' => 'ROL-002',
        'name' => 'Budi Mentor',
        'email' => 'budi@example.com',
        'password_hash' => Hash::make('password123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->post(route('login.submit'), [
        'email' => 'budi@example.com',
        'password' => 'password123',
        'role' => 'Mentor',
    ])->assertRedirect(route('landing'));

    $this->get(route('landing'))
        ->assertOk()
        ->assertSee('Selamat datang,')
        ->assertSee('Andi Peserta')
        ->assertSee('(Peserta)')
        ->assertDontSee('Budi Mentor')
        ->assertDontSee('(Mentor)');
});

test('the selected role must match the user account role', function () {
    createTestUser('Mentor', 'budi@example.com', 'Budi Mentor');

    $this->from(route('login'))
        ->post(route('login.submit'), [
            'email' => 'budi@example.com',
            'password' => 'password123',
            'role' => 'Peserta',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('role');

    $this->assertGuest();
});

test('invalid credentials do not authenticate a user', function () {
    createTestUser();

    $this->from(route('login'))
        ->post(route('login.submit'), [
            'email' => 'andi@example.com',
            'password' => 'wrong-password',
            'role' => 'Peserta',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('logging out ends the authenticated session', function () {
    createTestUser();

    $this->post(route('login.submit'), [
        'email' => 'andi@example.com',
        'password' => 'password123',
        'role' => 'Peserta',
    ]);

    $this->post(route('logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Showcase Capstone')
        ->assertSee('Masuk')
        ->assertDontSee('Keluar');
});

test('the dashboard requires authentication', function () {
    $this->get(route('dashboard.peserta'))
        ->assertRedirect(route('login'));
});

test('a participant can open their dashboard with live project data', function () {
    createTestUser();
    $user = \App\Models\User::where('email', 'andi@example.com')->firstOrFail();

    $this->actingAs($user)
        ->get(route('dashboard.peserta'))
        ->assertOk()
        ->assertSee('Dashboard Peserta')
        ->assertSee('Andi Peserta')
        ->assertSee('Kembali ke Beranda')
        ->assertDontSee('Keluar')
        ->assertSee('Belum ada project yang Anda ajukan.');
});

test('the participant dashboard formats project submission dates', function () {
    createTestUser();
    $user = \App\Models\User::where('email', 'andi@example.com')->firstOrFail();

    DB::table('mentoring_fields')->insert([
        'field_id' => 'FLD-001',
        'field_name' => 'Web Dev',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('mentoring_periods')->insert([
        'period_id' => 'PER-001',
        'period_name' => 'Semester Genap 2026',
        'start_date' => '2026-01-01',
        'end_date' => '2026-06-30',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('projects')->insert([
        'project_id' => 'PRJ-001',
        'field_id' => 'FLD-001',
        'period_id' => 'PER-001',
        'created_by' => $user->user_id,
        'title' => 'Project Peserta',
        'status' => 'Submitted',
        'submitted_at' => '2026-10-24 09:00:00',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard.peserta'))
        ->assertOk()
        ->assertSee('Project Peserta')
        ->assertSee('Web Dev')
        ->assertSee('24 Oct 2026');
});

test('only participants can open the participant dashboard', function () {
    createTestUser('Mentor', 'budi@example.com', 'Budi Mentor');
    $user = \App\Models\User::where('email', 'budi@example.com')->firstOrFail();

    $this->actingAs($user)
        ->get(route('dashboard.peserta'))
        ->assertForbidden();
});

test('a participant can view the project submission form', function () {
    createTestUser();
    $user = \App\Models\User::where('email', 'andi@example.com')->firstOrFail();

    DB::table('mentoring_fields')->insert([
        'field_id' => 'FLD-001',
        'field_name' => 'Web Dev',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('mentoring_periods')->insert([
        'period_id' => 'PRD-001',
        'period_name' => 'Genap 2025/2026',
        'is_active' => true,
        'start_date' => '2026-02-01',
        'end_date' => '2026-07-31',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('users')->insert([
        'user_id' => 'USR-002',
        'role_id' => 'ROL-001',
        'name' => 'Citra Peserta',
        'email' => 'citra@example.com',
        'password_hash' => Hash::make('password123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard.peserta.project.create'))
        ->assertOk()
        ->assertSee('Form Pengajuan Capstone Project')
        ->assertSee('Web Dev')
        ->assertSee('Genap 2025/2026')
        ->assertSee('Andi Peserta')
        ->assertSee('Citra Peserta')
        ->assertSee('Tools &amp; Stack Teknologi', false)
        ->assertSee('thumbnail')
        ->assertSee('submission-progress')
        ->assertSee('Dashboard')
        ->assertSee('Ajukan Project Baru')
        ->assertSee('Project')
        ->assertSee('Profil')
        ->assertSee('Jadwal Mentoring')
        ->assertSee('Panduan & Template', false)
        ->assertSee('Panduan Capstone')
        ->assertSee('Tambahkan minimal satu teknologi saat mengirim.')
        ->assertDontSee('Kembali ke Dashboard')
        ->assertSee(route('dashboard.peserta.project.store'));
});

test('a participant can submit a project using available mentoring data', function () {
    createTestUser();
    $user = \App\Models\User::where('email', 'andi@example.com')->firstOrFail();
    Storage::fake('public');

    DB::table('mentoring_fields')->insert([
        'field_id' => 'FLD-001',
        'field_name' => 'Web Dev',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('mentoring_periods')->insert([
        'period_id' => 'PRD-001',
        'period_name' => 'Genap 2025/2026',
        'is_active' => true,
        'start_date' => '2026-02-01',
        'end_date' => '2026-07-31',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('users')->insert([
        'user_id' => 'USR-002',
        'role_id' => 'ROL-001',
        'name' => 'Citra Peserta',
        'email' => 'citra@example.com',
        'password_hash' => Hash::make('password123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->post(route('dashboard.peserta.project.store'), [
            'submission_action' => 'Submitted',
            'title' => 'Project Multimedia',
            'field_id' => 'FLD-001',
            'period_id' => 'PRD-001',
            'description' => str_repeat('Deskripsi project multimedia yang inovatif. ', 30),
            'project_link' => 'https://example.com/project',
            'members' => [
                ['user_id' => 'USR-002', 'member_role' => 'UI Designer'],
            ],
            'tools' => ['Laravel', 'Figma'],
            'thumbnail' => UploadedFile::fake()->image('cover.png'),
        ])
        ->assertRedirect(route('dashboard.peserta'))
        ->assertSessionHas('status', 'Project berhasil diajukan.');

    $this->assertDatabaseHas('projects', [
        'field_id' => 'FLD-001',
        'period_id' => 'PRD-001',
        'created_by' => $user->user_id,
        'title' => 'Project Multimedia',
        'status' => 'Submitted',
        'project_link' => 'https://example.com/project',
    ]);

    $project = \App\Models\Project::where('title', 'Project Multimedia')->firstOrFail();
    $this->assertNotNull($project->thumbnail_url);
    $this->assertDatabaseHas('project_members', [
        'project_id' => $project->project_id,
        'user_id' => 'USR-002',
        'member_role' => 'UI Designer',
    ]);
    $this->assertDatabaseHas('project_tools', [
        'project_id' => $project->project_id,
        'tool_name' => 'Laravel',
    ]);
    $file = DB::table('project_files')->where('project_id', $project->project_id)->first();
    $this->assertNotNull($file);
    Storage::disk('public')->assertExists($file->file_path);
});

test('a participant can save a project as a draft without a thumbnail', function () {
    createTestUser();
    $user = \App\Models\User::where('email', 'andi@example.com')->firstOrFail();

    DB::table('mentoring_fields')->insert([
        'field_id' => 'FLD-001',
        'field_name' => 'Web Dev',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('mentoring_periods')->insert([
        'period_id' => 'PRD-001',
        'period_name' => 'Genap 2025/2026',
        'is_active' => true,
        'start_date' => '2026-02-01',
        'end_date' => '2026-07-31',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->post(route('dashboard.peserta.project.store'), [
            'submission_action' => 'Draft',
            'title' => 'Draft Project',
            'field_id' => 'FLD-001',
            'period_id' => 'PRD-001',
            'description' => 'Draft deskripsi project yang belum selesai.',
        ])
        ->assertRedirect(route('dashboard.peserta'))
        ->assertSessionHas('status', 'Draft project berhasil disimpan.');

    $this->assertDatabaseHas('projects', [
        'title' => 'Draft Project',
        'status' => 'Draft',
        'submitted_at' => null,
    ]);
});

test('submitting a project requires at least one participant and their role', function () {
    createTestUser();
    $user = \App\Models\User::where('email', 'andi@example.com')->firstOrFail();

    DB::table('mentoring_fields')->insert([
        'field_id' => 'FLD-001',
        'field_name' => 'Web Dev',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('mentoring_periods')->insert([
        'period_id' => 'PRD-001',
        'period_name' => 'Genap 2025/2026',
        'is_active' => true,
        'start_date' => '2026-02-01',
        'end_date' => '2026-07-31',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->from(route('dashboard.peserta.project.create'))
        ->post(route('dashboard.peserta.project.store'), [
            'submission_action' => 'Submitted',
            'title' => 'Project Multimedia',
            'field_id' => 'FLD-001',
            'period_id' => 'PRD-001',
            'description' => str_repeat('Deskripsi project multimedia yang inovatif. ', 30),
            'project_link' => 'https://example.com/project',
            'thumbnail' => UploadedFile::fake()->image('cover.png'),
        ])
        ->assertRedirect(route('dashboard.peserta.project.create'))
        ->assertSessionHasErrors('members');
});

test('submitting a project requires a demo or repository link', function () {
    createTestUser();
    $user = \App\Models\User::where('email', 'andi@example.com')->firstOrFail();

    DB::table('mentoring_fields')->insert([
        'field_id' => 'FLD-001',
        'field_name' => 'Web Dev',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('mentoring_periods')->insert([
        'period_id' => 'PRD-001',
        'period_name' => 'Genap 2025/2026',
        'is_active' => true,
        'start_date' => '2026-02-01',
        'end_date' => '2026-07-31',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->from(route('dashboard.peserta.project.create'))
        ->post(route('dashboard.peserta.project.store'), [
            'submission_action' => 'Submitted',
            'title' => 'Project Multimedia',
            'field_id' => 'FLD-001',
            'period_id' => 'PRD-001',
            'description' => str_repeat('Deskripsi project multimedia yang inovatif. ', 30),
            'members' => [
                ['user_id' => $user->user_id, 'member_role' => 'Project Lead'],
            ],
            'thumbnail' => UploadedFile::fake()->image('cover.png'),
        ])
        ->assertRedirect(route('dashboard.peserta.project.create'))
        ->assertSessionHasErrors('project_link');
});

test('submitting a project requires at least one technology', function () {
    createTestUser();
    $user = \App\Models\User::where('email', 'andi@example.com')->firstOrFail();

    DB::table('mentoring_fields')->insert([
        'field_id' => 'FLD-001',
        'field_name' => 'Web Dev',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('mentoring_periods')->insert([
        'period_id' => 'PRD-001',
        'period_name' => 'Genap 2025/2026',
        'is_active' => true,
        'start_date' => '2026-02-01',
        'end_date' => '2026-07-31',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->from(route('dashboard.peserta.project.create'))
        ->post(route('dashboard.peserta.project.store'), [
            'submission_action' => 'Submitted',
            'title' => 'Project Multimedia',
            'field_id' => 'FLD-001',
            'period_id' => 'PRD-001',
            'description' => str_repeat('Deskripsi project multimedia yang inovatif. ', 30),
            'project_link' => 'https://example.com/project',
            'members' => [
                ['user_id' => $user->user_id, 'member_role' => 'Project Lead'],
            ],
            'thumbnail' => UploadedFile::fake()->image('cover.png'),
        ])
        ->assertRedirect(route('dashboard.peserta.project.create'))
        ->assertSessionHasErrors('tools');
});

test('submitting a project requires a description of at least 150 words', function () {
    createTestUser();
    $user = \App\Models\User::where('email', 'andi@example.com')->firstOrFail();

    DB::table('mentoring_fields')->insert([
        'field_id' => 'FLD-001',
        'field_name' => 'Web Dev',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('mentoring_periods')->insert([
        'period_id' => 'PRD-001',
        'period_name' => 'Genap 2025/2026',
        'is_active' => true,
        'start_date' => '2026-02-01',
        'end_date' => '2026-07-31',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->from(route('dashboard.peserta.project.create'))
        ->post(route('dashboard.peserta.project.store'), [
            'submission_action' => 'Submitted',
            'title' => 'Project Multimedia',
            'field_id' => 'FLD-001',
            'period_id' => 'PRD-001',
            'description' => 'Deskripsi yang masih terlalu singkat.',
            'thumbnail' => UploadedFile::fake()->image('cover.png'),
        ])
        ->assertRedirect(route('dashboard.peserta.project.create'))
        ->assertSessionHasErrors('description');
});

test('only participants can open the project submission form', function () {
    createTestUser('Mentor', 'budi@example.com', 'Budi Mentor');
    $user = \App\Models\User::where('email', 'budi@example.com')->firstOrFail();

    $this->actingAs($user)
        ->get(route('dashboard.peserta.project.create'))
        ->assertForbidden();
});

test('a participant can view their project list and project details', function () {
    createTestUser();
    $user = \App\Models\User::where('email', 'andi@example.com')->firstOrFail();

    DB::table('roles')->insert([
        'role_id' => 'ROL-002',
        'role_name' => 'Mentor',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('users')->insert([
        'user_id' => 'USR-003',
        'role_id' => 'ROL-002',
        'name' => 'Budi Mentor',
        'email' => 'budi.mentor@ksm.ac.id',
        'password_hash' => Hash::make('password123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('mentoring_fields')->insert([
        'field_id' => 'FLD-001',
        'field_name' => 'Web Dev',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('mentoring_periods')->insert([
        'period_id' => 'PRD-001',
        'period_name' => 'Genap 2025/2026',
        'is_active' => true,
        'start_date' => '2026-02-01',
        'end_date' => '2026-07-31',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('projects')->insert([
        'project_id' => 'PRJ-DETAIL',
        'field_id' => 'FLD-001',
        'period_id' => 'PRD-001',
        'created_by' => $user->user_id,
        'title' => 'Project Detail Peserta',
        'description' => 'Deskripsi project untuk halaman detail.',
        'project_link' => 'https://example.com/project',
        'status' => 'In Review',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('project_members')->insert([
        'project_id' => 'PRJ-DETAIL',
        'user_id' => $user->user_id,
        'member_role' => 'Project Lead',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('project_tools')->insert([
        'tool_id' => 'TOOL-DETAIL',
        'project_id' => 'PRJ-DETAIL',
        'tool_name' => 'Laravel',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard.peserta.projects.index'))
        ->assertOk()
        ->assertSee('Project Detail Peserta')
        ->assertSee('Budi Mentor')
        ->assertSee('budi.mentor@ksm.ac.id')
        ->assertSee(route('dashboard.peserta.project.show', 'PRJ-DETAIL'));

    $this->get(route('dashboard.peserta.project.show', 'PRJ-DETAIL'))
        ->assertOk()
        ->assertSee('Informasi Project')
        ->assertSee('Budi Mentor')
        ->assertSee('budi.mentor@ksm.ac.id')
        ->assertSee('Andi Peserta')
        ->assertSee('Project Lead')
        ->assertSee('Laravel')
        ->assertSee('https://example.com/project');

    $this->get(route('dashboard.peserta.project.create'))
        ->assertOk()
        ->assertSee('Budi Mentor')
        ->assertSee('budi.mentor@ksm.ac.id');

    DB::table('users')->insert([
        'user_id' => 'USR-002',
        'role_id' => 'ROL-001',
        'name' => 'Citra Peserta',
        'email' => 'citra@example.com',
        'password_hash' => Hash::make('password123'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $otherParticipant = \App\Models\User::where('email', 'citra@example.com')->firstOrFail();

    $this->actingAs($otherParticipant)
        ->get(route('dashboard.peserta.project.show', 'PRJ-DETAIL'))
        ->assertNotFound();
});

test('a project owner can resubmit a requested revision', function () {
    createTestUser();
    $user = \App\Models\User::where('email', 'andi@example.com')->firstOrFail();

    DB::table('mentoring_fields')->insert([
        'field_id' => 'FLD-001',
        'field_name' => 'Web Dev',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('mentoring_periods')->insert([
        'period_id' => 'PRD-001',
        'period_name' => 'Genap 2025/2026',
        'is_active' => true,
        'start_date' => '2026-02-01',
        'end_date' => '2026-07-31',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('projects')->insert([
        'project_id' => 'PRJ-REVISION',
        'field_id' => 'FLD-001',
        'period_id' => 'PRD-001',
        'created_by' => $user->user_id,
        'title' => 'Project Revisi',
        'status' => 'Revision Required',
        'project_link' => 'https://example.com/old',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->post(route('dashboard.peserta.project.resubmit', 'PRJ-REVISION'), [
            'revision_notes' => 'Memperbaiki validasi dan tampilan project sesuai masukan mentor.',
            'project_link' => 'https://example.com/updated',
            'integrity' => '1',
        ])
        ->assertRedirect(route('dashboard.peserta.project.show', 'PRJ-REVISION'))
        ->assertSessionHas('status', 'Project berhasil dikirim ulang untuk ditinjau.');

    $this->assertDatabaseHas('projects', [
        'project_id' => 'PRJ-REVISION',
        'status' => 'Resubmitted',
        'project_link' => 'https://example.com/updated',
    ]);
    $this->assertDatabaseHas('project_versions', [
        'project_id' => 'PRJ-REVISION',
        'version_number' => 'v1.1',
        'changelog' => 'Memperbaiki validasi dan tampilan project sesuai masukan mentor.',
    ]);
});
