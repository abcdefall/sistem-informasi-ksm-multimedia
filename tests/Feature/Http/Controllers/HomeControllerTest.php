<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function draftWorkflowUser(string $userId, string $name): User
{
    DB::table('roles')->updateOrInsert(
        ['role_id' => 'ROLE-PESERTA'],
        ['role_name' => 'Peserta', 'created_at' => now(), 'updated_at' => now()],
    );

    DB::table('users')->insert([
        'user_id' => $userId,
        'role_id' => 'ROLE-PESERTA',
        'name' => $name,
        'email' => strtolower($userId).'@example.test',
        'password_hash' => bcrypt('password'),
        'profile_data' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return User::query()->findOrFail($userId);
}

function draftWorkflowReferences(): void
{
    DB::table('mentoring_fields')->insert([
        'field_id' => 'FIELD-TEST',
        'field_name' => 'Web Dev',
        'description' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('mentoring_periods')->insert([
        'period_id' => 'PERIOD-TEST',
        'period_name' => 'Periode Test',
        'is_active' => true,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function draftWorkflowProject(User $owner, string $status = 'Draft', ?string $thumbnailUrl = null): Project
{
    return Project::query()->create([
        'project_id' => 'PROJ-'.str_pad(substr($owner->user_id, -10), 10, '0', STR_PAD_LEFT),
        'field_id' => 'FIELD-TEST',
        'period_id' => 'PERIOD-TEST',
        'created_by' => $owner->user_id,
        'title' => 'Draft awal',
        'description' => 'Deskripsi draft',
        'thumbnail_url' => $thumbnailUrl,
        'project_link' => null,
        'status' => $status,
        'submitted_at' => null,
    ]);
}

function draftWorkflowData(string $title = 'Draft baru'): array
{
    return [
        'submission_action' => 'Draft',
        'title' => $title,
        'field_id' => 'FIELD-TEST',
        'period_id' => 'PERIOD-TEST',
        'description' => 'Deskripsi draft yang dapat dilengkapi sebelum dikirim.',
    ];
}

function homeControllerTestPngUpload(string $fileName = 'cover.png'): UploadedFile
{
    $contents = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC');

    return UploadedFile::fake()->createWithContent($fileName, $contents);
}

it('creates one draft and offers a link to continue it', function () {
    $this->withoutVite();
    draftWorkflowReferences();
    $participant = draftWorkflowUser('USER-PESERTA-01', 'Peserta Satu');
    $this->actingAs($participant);

    $response = $this->post(route('dashboard.peserta.project.store'), draftWorkflowData());

    $project = Project::query()->where('created_by', $participant->user_id)->firstOrFail();
    $response->assertRedirect(route('dashboard.peserta'));
    $response->assertSessionHas('status', 'Draft project berhasil disimpan.');
    expect($project->status)->toBe('Draft');

    $this->get(route('dashboard.peserta'))
        ->assertOk()
        ->assertSee('Lanjutkan Draft')
        ->assertSee(route('dashboard.peserta.project.edit', $project->project_id))
        ->assertSee('Progres Project')
        ->assertDontSee('Daftar Project')
        ->assertSee('Belum ada aktivitas.');

    $this->get(route('dashboard.peserta.projects.index'))
        ->assertRedirect(route('dashboard.peserta.project.edit', $project->project_id));

    $this->get(route('dashboard.peserta.project.create'))
        ->assertRedirect(route('dashboard.peserta.project.edit', $project->project_id));

    $this->post(route('dashboard.peserta.project.store'), draftWorkflowData())
        ->assertSessionHasErrors(['project' => 'Kamu sudah memiliki project.']);
});

it('shows the empty dashboard state and redirects the project menu to create', function () {
    $this->withoutVite();
    draftWorkflowReferences();
    $participant = draftWorkflowUser('USER-PESERTA-08', 'Peserta Tanpa Project');
    $this->actingAs($participant);

    $this->get(route('dashboard.peserta'))
        ->assertOk()
        ->assertSee('Belum ada project')
        ->assertSee('Ajukan Project')
        ->assertDontSee('Progres Project');

    $this->get(route('dashboard.peserta.projects.index'))
        ->assertRedirect(route('dashboard.peserta.project.create'));
});

it('saves a draft, replaces its thumbnail, and moves it to mentor review when submitted', function () {
    $this->withoutVite();
    Storage::fake('public');
    draftWorkflowReferences();
    $participant = draftWorkflowUser('USER-PESERTA-02', 'Peserta Dua');
    $oldThumbnailPath = 'projects/PROJ-0000000002/old-cover.jpg';
    Storage::disk('public')->put($oldThumbnailPath, 'old thumbnail');
    $project = draftWorkflowProject(
        $participant,
        thumbnailUrl: Storage::disk('public')->url($oldThumbnailPath),
    );
    DB::table('project_files')->insert([
        'file_id' => 'FILE-OLD-THUMBNAIL',
        'project_id' => $project->project_id,
        'file_name' => 'old-cover.jpg',
        'file_path' => $oldThumbnailPath,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('project_members')->insert([
        'project_id' => $project->project_id,
        'user_id' => $participant->user_id,
        'member_role' => 'Project Lead',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('project_tools')->insert([
        'tool_id' => 'TOOL-EXISTING-01',
        'project_id' => $project->project_id,
        'tool_name' => 'Figma',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $this->actingAs($participant);

    $this->get(route('dashboard.peserta.project.edit', $project->project_id))
        ->assertOk()
        ->assertSee('Lanjutkan Draft Project')
        ->assertSee('old-cover.jpg')
        ->assertSee('src="'.$project->thumbnail_display_url.'"', false)
        ->assertSee('Project Lead')
        ->assertSee('Figma')
        ->assertSee('method="POST"', false)
        ->assertSee('name="_method" value="PUT"', false);

    $legacyThumbnailUrl = "http://localhost:8000/storage/{$oldThumbnailPath}";
    expect(Project::make(['thumbnail_url' => $legacyThumbnailUrl])->thumbnail_display_url)
        ->toBe($legacyThumbnailUrl);

    $this->put(
        route('dashboard.peserta.project.update', $project->project_id),
        [...draftWorkflowData('Draft yang diperbarui'), 'thumbnail' => UploadedFile::fake()->create('new-cover.jpg', 10, 'image/jpeg')],
    )->assertRedirect(route('dashboard.peserta'));

    $project->refresh();
    $newThumbnailPath = DB::table('project_files')
        ->where('project_id', $project->project_id)
        ->value('file_path');
    expect($project->title)->toBe('Draft yang diperbarui')
        ->and($project->status)->toBe('Draft')
        ->and($newThumbnailPath)->not->toBe($oldThumbnailPath);
    Storage::disk('public')->assertMissing($oldThumbnailPath);
    Storage::disk('public')->assertExists($newThumbnailPath);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $participant->user_id,
        'action' => 'DRAFT_SAVED',
        'description' => 'Menyimpan draft project "Draft yang diperbarui".',
    ]);

    $submissionData = [
        'submission_action' => 'Submitted',
        'title' => 'Draft yang diperbarui',
        'field_id' => 'FIELD-TEST',
        'period_id' => 'PERIOD-TEST',
        'description' => str_repeat('project ', 150),
        'project_link' => 'https://example.test/project',
        'members' => [
            ['user_id' => $participant->user_id, 'member_role' => 'Project Lead'],
        ],
        'tools' => ['Laravel'],
    ];

    $this->put(
        route('dashboard.peserta.project.update', $project->project_id),
        $submissionData,
    )->assertRedirect(route('dashboard.peserta'));

    $project->refresh();
    expect($project->status)->toBe('Submitted')
        ->and($project->submitted_at)->not->toBeNull()
        ->and($project->thumbnail_url)->toBe($newThumbnailPath);
    $this->assertDatabaseHas('project_members', [
        'project_id' => $project->project_id,
        'user_id' => $participant->user_id,
        'member_role' => 'Project Lead',
    ]);
    $this->assertDatabaseHas('project_tools', [
        'project_id' => $project->project_id,
        'tool_name' => 'Laravel',
    ]);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $participant->user_id,
        'action' => 'PROJECT_SUBMITTED',
        'description' => 'Mengajukan project "Draft yang diperbarui" dari draft.',
    ]);

    $this->get(route('dashboard.peserta'))
        ->assertOk()
        ->assertSee('aria-current="step" >Review Mentor', false)
        ->assertSee('Mengajukan project "Draft yang diperbarui" dari draft.')
        ->assertSee('oleh Peserta Dua');
    $this->get(route('dashboard.peserta.project.edit', $project->project_id))
        ->assertNotFound();
    $this->get(route('dashboard.peserta.project.create'))
        ->assertRedirect(route('dashboard.peserta'))
        ->assertSessionHas('status', 'Kamu sudah memiliki project.');
});

it('does not expose another participant draft edit page', function () {
    $this->withoutVite();
    draftWorkflowReferences();
    $owner = draftWorkflowUser('USER-PESERTA-03', 'Pemilik Draft');
    $otherParticipant = draftWorkflowUser('USER-PESERTA-04', 'Peserta Lain');
    $project = draftWorkflowProject($owner);
    $this->actingAs($otherParticipant);

    $this->get(route('dashboard.peserta.project.edit', $project->project_id))
        ->assertNotFound();
    $this->put(
        route('dashboard.peserta.project.update', $project->project_id),
        draftWorkflowData(),
    )->assertForbidden();
});

it('shows submitted projects to members but keeps project actions creator-only', function () {
    $this->withoutVite();
    Storage::fake('public');
    draftWorkflowReferences();
    $owner = draftWorkflowUser('USER-PESERTA-09', 'Andi Peserta');
    $member = draftWorkflowUser('USER-PESERTA-10', 'Dina Peserta');

    $this->actingAs($owner)
        ->post(route('dashboard.peserta.project.store'), [
            'submission_action' => 'Submitted',
            'title' => 'Project Andi dan Dina',
            'field_id' => 'FIELD-TEST',
            'period_id' => 'PERIOD-TEST',
            'description' => str_repeat('Project multimedia ', 150),
            'project_link' => 'https://example.test/project',
            'members' => [
                ['user_id' => $member->user_id, 'member_role' => 'UI Designer'],
            ],
            'tools' => ['Laravel'],
            'thumbnail' => homeControllerTestPngUpload(),
        ])
        ->assertRedirect(route('dashboard.peserta'));

    $project = Project::query()->where('created_by', $owner->user_id)->firstOrFail();
    $this->actingAs($member)
        ->get(route('dashboard.peserta'))
        ->assertOk()
        ->assertSee('Project Andi dan Dina');

    $this->get(route('dashboard.peserta.project.show', $project->project_id))
        ->assertOk()
        ->assertSee('Project Andi dan Dina');
    $this->get(route('dashboard.peserta.project.edit', $project->project_id))
        ->assertNotFound();
    $this->put(route('dashboard.peserta.project.update', $project->project_id), draftWorkflowData())
        ->assertForbidden();

    DB::table('projects')
        ->where('project_id', $project->project_id)
        ->update(['status' => 'Revision Required']);
    $this->post(route('dashboard.peserta.project.resubmit', $project->project_id), [
        'revision_notes' => 'Perbaikan draft.',
        'project_link' => 'https://example.test/project',
        'integrity' => '1',
    ])->assertNotFound();

    $this->get(route('dashboard.peserta.project.create'))
        ->assertRedirect(route('dashboard.peserta'))
        ->assertSessionHas('status', 'Kamu sudah memiliki project.');
    $this->from(route('dashboard.peserta.project.create'))
        ->post(route('dashboard.peserta.project.store'), draftWorkflowData())
        ->assertRedirect(route('dashboard.peserta.project.create'))
        ->assertSessionHasErrors(['project' => 'Kamu sudah memiliki project.']);

    $anotherParticipant = draftWorkflowUser('USER-PESERTA-11', 'Citra Peserta');
    $this->actingAs($anotherParticipant)
        ->get(route('dashboard.peserta.project.create'))
        ->assertOk()
        ->assertDontSee($member->user_id);
    $this->from(route('dashboard.peserta.project.create'))
        ->post(route('dashboard.peserta.project.store'), [
            ...draftWorkflowData(),
            'members' => [
                ['user_id' => $member->user_id, 'member_role' => 'UI Designer'],
            ],
        ])
        ->assertRedirect(route('dashboard.peserta.project.create'))
        ->assertSessionHasErrors('members.0.user_id');
});

it('hides a draft from participants who are only its members', function () {
    $this->withoutVite();
    draftWorkflowReferences();
    $owner = draftWorkflowUser('USER-PESERTA-12', 'Pemilik Draft');
    $member = draftWorkflowUser('USER-PESERTA-13', 'Anggota Draft');
    $project = draftWorkflowProject($owner);
    DB::table('project_members')->insert([
        'project_id' => $project->project_id,
        'user_id' => $member->user_id,
        'member_role' => 'UI Designer',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($member)
        ->get(route('dashboard.peserta'))
        ->assertOk()
        ->assertSee('Belum ada project')
        ->assertDontSee('Progres Project');
});

it('serves uploaded thumbnails only to related users until publication', function () {
    $this->withoutVite();
    Storage::fake('public');
    draftWorkflowReferences();
    $owner = draftWorkflowUser('USER-PESERTA-14', 'Pemilik Thumbnail');
    $unrelatedParticipant = draftWorkflowUser('USER-PESERTA-15', 'Peserta Lain');

    $this->actingAs($owner)
        ->post(route('dashboard.peserta.project.store'), [
            'submission_action' => 'Submitted',
            'title' => 'Project dengan Thumbnail',
            'field_id' => 'FIELD-TEST',
            'period_id' => 'PERIOD-TEST',
            'description' => str_repeat('Project multimedia ', 150),
            'project_link' => 'https://example.test/project',
            'members' => [
                ['user_id' => $owner->user_id, 'member_role' => 'Project Lead'],
            ],
            'tools' => ['Laravel'],
            'thumbnail' => homeControllerTestPngUpload(),
        ])
        ->assertRedirect(route('dashboard.peserta'));

    $project = Project::query()->where('created_by', $owner->user_id)->firstOrFail();
    expect($project->thumbnail_url)->toStartWith("projects/{$project->project_id}/");
    expect($project->thumbnail_display_url)->toStartWith('/media/projects/');
    Storage::disk('public')->assertExists($project->thumbnail_url);

    $this->actingAs($owner)
        ->get($project->thumbnail_display_url)
        ->assertOk();
    $this->actingAs($unrelatedParticipant)
        ->get($project->thumbnail_display_url)
        ->assertForbidden();

    $project->update(['status' => 'Published']);
    $this->get($project->thumbnail_display_url)->assertOk();
});

it('marks each workflow status at the correct progress step', function () {
    $this->withoutVite();
    draftWorkflowReferences();
    $participant = draftWorkflowUser('USER-PESERTA-05', 'Peserta Lima');
    $project = draftWorkflowProject($participant);
    $this->actingAs($participant);

    $activeSteps = [
        'Draft' => 'Draft',
        'Submitted' => 'Review Mentor',
        'In Review' => 'Review Mentor',
        'Resubmitted' => 'Review Mentor',
        'Revision Required' => 'Review Mentor',
        'Approved' => 'Disetujui',
        'Published' => 'Dipublikasi',
    ];

    foreach ($activeSteps as $status => $activeStep) {
        DB::table('projects')
            ->where('project_id', $project->project_id)
            ->update(['status' => $status]);

        $response = $this->get(route('dashboard.peserta'))
            ->assertOk();

        if ($status === 'Published') {
            expect(substr_count($response->getContent(), '✓'))->toBe(5);
        } else {
            $response->assertSee('aria-current="step" >'.$activeStep, false);
        }
    }

    DB::table('projects')
        ->where('project_id', $project->project_id)
        ->update(['status' => 'Revision Required']);
    $this->get(route('dashboard.peserta'))->assertSee('Perlu Revisi');

    DB::table('projects')
        ->where('project_id', $project->project_id)
        ->update(['status' => 'Archived']);
    $archivedResponse = $this->get(route('dashboard.peserta'))
        ->assertOk()
        ->assertSee('Diarsipkan');
    expect(substr_count($archivedResponse->getContent(), '✓'))->toBe(5);
});

it('displays activity actions as readable title case', function () {
    $this->withoutVite();
    draftWorkflowReferences();
    $participant = draftWorkflowUser('USER-PESERTA-06', 'Peserta Enam');
    $project = draftWorkflowProject($participant, 'Published');
    $otherParticipant = draftWorkflowUser('USER-PESERTA-07', 'Peserta Tujuh');
    $otherProject = draftWorkflowProject($otherParticipant, 'Published');

    foreach (['PROJECT_SUBMITTED', 'PROJECT_PUBLISHED'] as $index => $action) {
        DB::table('audit_logs')->insert([
            'log_id' => 'LOG-ACTIVITY-'.$index,
            'user_id' => $participant->user_id,
            'project_id' => $project->project_id,
            'action' => $action,
            'description' => $action,
            'created_at' => now()->addSeconds($index),
            'updated_at' => now()->addSeconds($index),
        ]);
    }

    DB::table('audit_logs')->insert([
        'log_id' => 'LOG-ACTIVITY-OTHER',
        'user_id' => $participant->user_id,
        'project_id' => $otherProject->project_id,
        'action' => 'OTHER_PROJECT_EVENT',
        'description' => 'Aktivitas project lain.',
        'created_at' => now()->addMinute(),
        'updated_at' => now()->addMinute(),
    ]);

    DB::table('reviews')->insert([
        'review_id' => 'REV-ACTIVITY-01',
        'project_id' => $project->project_id,
        'reviewer_id' => $participant->user_id,
        'comment' => 'Review tersimpan.',
        'rating' => '5',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($participant)
        ->get(route('dashboard.peserta'))
        ->assertOk()
        ->assertSee('Mentor Review')
        ->assertSee('Project Submitted')
        ->assertSee('Project Published')
        ->assertDontSee('Aktivitas project lain.')
        ->assertDontSee('M E N T O R R E V I E W')
        ->assertDontSee('P R O J E C T S U B M I T T E D')
        ->assertDontSee('P R O J E C T P U B L I S H E D');
});
