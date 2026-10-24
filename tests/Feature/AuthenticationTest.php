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

test('the public landing page stays public when a user is authenticated', function () {
    createTestUser();
    $user = \App\Models\User::where('email', 'andi@example.com')->firstOrFail();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('ARSIP AKADEMIK & SHOWCASE MULTIMEDIA', false)
        ->assertDontSee('Selamat datang, Andi Peserta')
        ->assertSee('Masuk')
        ->assertDontSee('Andi Peserta (Peserta)')
        ->assertDontSee('Keluar');
});

test('an authenticated user can open the login form to switch accounts', function () {
    createTestUser();
    $user = \App\Models\User::where('email', 'andi@example.com')->firstOrFail();

    $this->actingAs($user)
        ->get(route('login'))
        ->assertOk()
        ->assertSee('Masuk ke Akun');
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
        ->assertSee('Panduan Penilaian')
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

test('logging in as a mentor replaces the previous participant session', function () {
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
        ->assertSee('Budi Mentor')
        ->assertSee('Budi Mentor')
        ->assertSee('(Mentor)')
        ->assertDontSee('Andi Peserta')
        ->assertDontSee('(Peserta)');
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
