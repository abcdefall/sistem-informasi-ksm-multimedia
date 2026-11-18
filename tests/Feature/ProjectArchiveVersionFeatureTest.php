<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function archiveVersionTestUser(string $suffix, string $roleName): User
{
    $roleId = 'ROLE-'.$suffix;
    $userId = 'USER-'.$suffix;

    DB::table('roles')->insert([
        'role_id' => $roleId,
        'role_name' => $roleName,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('users')->insert([
        'user_id' => $userId,
        'role_id' => $roleId,
        'name' => 'User '.$suffix,
        'email' => strtolower($suffix).'@example.test',
        'password_hash' => Hash::make('password'),
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return User::query()->findOrFail($userId);
}

function archiveVersionTestProject(string $suffix, User $owner, string $status): Project
{
    $fieldId = 'FIELD-'.$suffix;
    $periodId = 'PERIOD-'.$suffix;
    $projectId = 'PROJECT-'.$suffix;

    DB::table('mentoring_fields')->insert([
        'field_id' => $fieldId,
        'field_name' => 'Web Dev',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('mentoring_periods')->insert([
        'period_id' => $periodId,
        'period_name' => 'Periode '.$suffix,
        'is_active' => true,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('projects')->insert([
        'project_id' => $projectId,
        'field_id' => $fieldId,
        'period_id' => $periodId,
        'created_by' => $owner->user_id,
        'title' => 'Project '.$suffix,
        'description' => 'Deskripsi project '.$suffix,
        'thumbnail_url' => null,
        'project_link' => 'https://old.example.test/'.$suffix,
        'status' => $status,
        'submitted_at' => now(),
        'published_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return Project::query()->findOrFail($projectId);
}

function archiveVersionTestInsertVersion(Project $project, string $versionId, string $versionNumber, array $overrides = []): object
{
    $data = [
        'version_id' => $versionId,
        'project_id' => $project->project_id,
        'version_number' => $versionNumber,
        'changelog' => null,
        'file_path' => null,
        'title_snapshot' => $project->title,
        'description_snapshot' => $project->description,
        'field_id_snapshot' => $project->field_id,
        'field_name_snapshot' => 'Web Dev',
        'period_id_snapshot' => $project->period_id,
        'period_name_snapshot' => 'Periode '.$project->project_id,
        'project_link_snapshot' => $project->project_link,
        'thumbnail_path_snapshot' => null,
        'team_snapshot' => json_encode([], JSON_THROW_ON_ERROR),
        'tools_snapshot' => json_encode(['Laravel'], JSON_THROW_ON_ERROR),
        'submitted_at' => now(),
        'submitted_by' => $project->created_by,
        'approved_at' => null,
        'approved_by' => null,
        'is_published' => false,
        'published_at' => null,
        'is_legacy_snapshot' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ];

    DB::table('project_versions')->insert(array_replace($data, $overrides));

    return DB::table('project_versions')->where('version_id', $versionId)->firstOrFail();
}

test('saving a draft creates no version and submitting it creates the first immutable snapshot', function () {
    config(['filesystems.project_media_disk' => 'public']);
    Storage::fake('public');
    $participant = archiveVersionTestUser('DRAFT1', 'Peserta');
    $project = archiveVersionTestProject('DRAFT1', $participant, 'Draft');

    $draftResponse = $this->actingAs($participant)->put(
        route('dashboard.peserta.project.update', $project->project_id),
        [
            'submission_action' => 'Draft',
            'title' => 'Draft capstone',
            'field_id' => $project->field_id,
            'period_id' => $project->period_id,
            'description' => 'Deskripsi draft.',
        ],
    );

    $draftResponse->assertRedirect(route('dashboard.peserta'));
    expect(DB::table('project_versions')->where('project_id', $project->project_id)->count())->toBe(0);

    $description = implode(' ', array_fill(0, 155, 'multimedia'));
    $submitResponse = $this->actingAs($participant)->put(
        route('dashboard.peserta.project.update', $project->project_id),
        [
            'submission_action' => 'Submitted',
            'title' => 'Submitted capstone',
            'field_id' => $project->field_id,
            'period_id' => $project->period_id,
            'description' => $description,
            'project_link' => 'https://example.test/first',
            'members' => [['user_id' => $participant->user_id, 'member_role' => 'Developer']],
            'tools' => ['Laravel'],
            'thumbnail' => UploadedFile::fake()->createWithContent(
                'thumbnail.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lWQAAAAASUVORK5CYII='),
            ),
        ],
    );

    $submitResponse->assertRedirect(route('dashboard.peserta'));
    $version = DB::table('project_versions')->where('project_id', $project->project_id)->firstOrFail();

    expect($version->version_number)->toBe('v1.0')
        ->and($version->title_snapshot)->toBe('Submitted capstone')
        ->and($version->description_snapshot)->toBe($description)
        ->and($version->project_link_snapshot)->toBe('https://example.test/first')
        ->and($version->submitted_by)->toBe($participant->user_id);
    $this->assertDatabaseHas('audit_logs', [
        'project_id' => $project->project_id,
        'user_id' => $participant->user_id,
        'action' => 'PROJECT_SUBMITTED',
    ]);
    expect(DB::table('project_version_files')->where('version_id', $version->version_id)->count())->toBe(1);
});

test('a participant submission through the create route starts at version one', function () {
    config(['filesystems.project_media_disk' => 'public']);
    Storage::fake('public');
    $participant = archiveVersionTestUser('NEW001', 'Peserta');
    DB::table('mentoring_fields')->insert([
        'field_id' => 'FIELD-NEW001',
        'field_name' => 'Web Dev',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('mentoring_periods')->insert([
        'period_id' => 'PERIOD-NEW001',
        'period_name' => 'Periode NEW001',
        'is_active' => true,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($participant)->post(
        route('dashboard.peserta.project.store'),
        [
            'submission_action' => 'Submitted',
            'title' => 'Capstone awal',
            'field_id' => 'FIELD-NEW001',
            'period_id' => 'PERIOD-NEW001',
            'description' => implode(' ', array_fill(0, 155, 'multimedia')),
            'project_link' => 'https://example.test/initial',
            'members' => [['user_id' => $participant->user_id, 'member_role' => 'Developer']],
            'tools' => ['Laravel'],
            'thumbnail' => UploadedFile::fake()->createWithContent(
                'thumbnail.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lWQAAAAASUVORK5CYII='),
            ),
        ],
    );

    $response->assertRedirect(route('dashboard.peserta'));
    $project = Project::query()->where('created_by', $participant->user_id)->firstOrFail();
    $this->assertDatabaseHas('project_versions', [
        'project_id' => $project->project_id,
        'version_number' => 'v1.0',
        'title_snapshot' => 'Capstone awal',
        'project_link_snapshot' => 'https://example.test/initial',
    ]);
    $this->assertDatabaseHas('audit_logs', [
        'project_id' => $project->project_id,
        'action' => 'PROJECT_SUBMITTED',
    ]);
});

test('resubmission preserves prior files and links and ties mentor feedback to the submitted version', function () {
    config(['filesystems.project_media_disk' => 'public']);
    Storage::fake('public');
    $participant = archiveVersionTestUser('REV001', 'Peserta');
    $mentor = archiveVersionTestUser('REV002', 'Mentor');
    $project = archiveVersionTestProject('REV001', $participant, 'Submitted');
    archiveVersionTestProject('EMPTY1', $participant, 'Draft');
    $oldFilePath = 'projects/'.$project->project_id.'/first.pdf';
    Storage::disk('public')->put($oldFilePath, 'old-version-file');
    DB::table('project_files')->insert([
        'file_id' => 'FILE-REV001',
        'project_id' => $project->project_id,
        'file_name' => 'first.pdf',
        'file_path' => $oldFilePath,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    archiveVersionTestInsertVersion($project, 'VER-REV000', 'v1.0');
    $firstVersion = archiveVersionTestInsertVersion($project, 'VER-REV001', 'v1.9', [
        'title_snapshot' => 'Original title',
        'project_link_snapshot' => 'https://example.test/first',
        'file_path' => $oldFilePath,
        'team_snapshot' => json_encode([
            ['user_id' => $participant->user_id, 'name' => 'Historical Team Member', 'member_role' => 'Developer'],
        ], JSON_THROW_ON_ERROR),
    ]);
    DB::table('project_version_files')->insert([
        'version_file_id' => 'VF-REV001',
        'version_id' => $firstVersion->version_id,
        'file_id' => 'FILE-REV001',
        'file_name_snapshot' => 'first.pdf',
        'file_path' => $oldFilePath,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $reviewPayload = [
        'score' => 75,
        'comment' => 'Perlu revisi dokumentasi.',
        'decision' => 'Revision Required',
    ];

    $this->actingAs($mentor)->post(
        route('dashboard.mentor.project.review.store', $project->project_id),
        $reviewPayload,
    )->assertRedirect(route('dashboard.mentor'));
    $this->assertDatabaseHas('reviews', [
        'project_id' => $project->project_id,
        'version_id' => $firstVersion->version_id,
        'reviewer_id' => $mentor->user_id,
        'decision' => 'Revision Required',
    ]);
    $this->assertDatabaseHas('audit_logs', [
        'project_id' => $project->project_id,
        'action' => 'REVISION_REQUESTED',
    ]);

    $response = $this->actingAs($participant)->post(
        route('dashboard.peserta.project.resubmit', $project->project_id),
        [
            'revision_notes' => 'Memperbaiki dokumentasi.',
            'project_link' => 'https://example.test/revised',
            'integrity' => '1',
            'revision_file' => UploadedFile::fake()->create('revised.pdf', 20, 'application/pdf'),
        ],
    );

    $response->assertRedirect(route('dashboard.peserta.project.show', $project->project_id));
    $revisedVersion = DB::table('project_versions')
        ->where('project_id', $project->project_id)
        ->where('version_number', 'v1.10')
        ->firstOrFail();
    expect($firstVersion->project_link_snapshot)->toBe('https://example.test/first')
        ->and($revisedVersion->project_link_snapshot)->toBe('https://example.test/revised')
        ->and($revisedVersion->changelog)->toBe('Memperbaiki dokumentasi.')
        ->and(Storage::disk('public')->exists($oldFilePath))->toBeTrue();
    $this->assertDatabaseHas('project_version_files', [
        'version_id' => $firstVersion->version_id,
        'file_path' => $oldFilePath,
    ]);
    expect(DB::table('project_version_files')
        ->where('version_id', $revisedVersion->version_id)
        ->where('file_path', 'like', 'projects/%/revisions/%')
        ->exists())->toBeTrue();

    $this->actingAs($mentor)
        ->get(route('dashboard.mentor.project.review', $project->project_id))
        ->assertOk()
        ->assertSee('Menilai v1.10')
        ->assertSee('https://example.test/revised');

    $this->actingAs($mentor)->post(
        route('dashboard.mentor.project.review.store', $project->project_id),
        [...$reviewPayload, 'comment' => 'Revisi sudah baik.', 'decision' => 'Approved'],
    )->assertRedirect(route('dashboard.mentor'));

    $this->assertDatabaseHas('reviews', [
        'project_id' => $project->project_id,
        'version_id' => $firstVersion->version_id,
        'decision' => 'Revision Required',
    ]);
    $this->assertDatabaseHas('reviews', [
        'project_id' => $project->project_id,
        'version_id' => $revisedVersion->version_id,
        'reviewer_id' => $mentor->user_id,
        'decision' => 'Approved',
    ]);
    $this->assertDatabaseHas('audit_logs', [
        'project_id' => $project->project_id,
        'action' => 'PROJECT_RESUBMITTED',
    ]);
    $this->assertDatabaseHas('audit_logs', [
        'project_id' => $project->project_id,
        'action' => 'MENTOR_REVIEW',
    ]);
    DB::table('projects')->where('project_id', $project->project_id)->update([
        'title' => 'Current project title',
        'description' => 'Current project description',
        'project_link' => 'https://example.test/current',
    ]);

    $admin = archiveVersionTestUser('REV003', 'Pengurus KSM');
    $projectListResponse = $this->actingAs($admin)->get(route('dashboard.pengurus.version-history'));
    $projectListResponse
        ->assertOk()
        ->assertSee('Current project title')
        ->assertDontSee('Project EMPTY1')
        ->assertDontSee('v1.9')
        ->assertDontSee('Original title');
    expect(substr_count($projectListResponse->getContent(), 'aria-labelledby="project-'.$project->project_id.'"'))->toBe(1);

    $this->actingAs($admin)
        ->get(route('dashboard.pengurus.version-history', ['project_id' => $project->project_id]))
        ->assertOk()
        ->assertSee('Current project title')
        ->assertSee('Lihat Riwayat Versi')
        ->assertDontSee('v1.9')
        ->assertDontSee('Original title')
        ->assertDontSee('Perlu revisi dokumentasi.')
        ->assertDontSee('Nilai 75/100')
        ->assertSee(route('dashboard.pengurus.version-history.project', $project->project_id))
        ->assertSee('2');

    $this->get(route('dashboard.pengurus.version-history.project', $project->project_id))
        ->assertOk()
        ->assertSee('Original title')
        ->assertSee('v1.9')
        ->assertSee('v1.10')
        ->assertSee('Memperbaiki dokumentasi.')
        ->assertDontSee('Perlu revisi dokumentasi.')
        ->assertDontSee('Nilai 75/100')
        ->assertDontSee('Historical Team Member')
        ->assertSee(route('dashboard.pengurus.version-history.show', [$project->project_id, $firstVersion->version_id]));

    $this->get(route('dashboard.pengurus.version-history.show', [$project->project_id, $firstVersion->version_id]))
        ->assertOk()
        ->assertSee('Original title')
        ->assertSee('Deskripsi project REV001')
        ->assertSee('Web Dev')
        ->assertSee('Periode PROJECT-REV001')
        ->assertSee('https://example.test/first')
        ->assertSee('Historical Team Member')
        ->assertSee('Laravel')
        ->assertSee('first.pdf')
        ->assertSee('Perlu revisi dokumentasi.')
        ->assertSee('Nilai 75/100')
        ->assertSee('← Kembali ke Riwayat Versi Project')
        ->assertDontSee('Current project title')
        ->assertDontSee('Current project description')
        ->assertDontSee('https://example.test/current');

    $this->get(route('dashboard.pengurus.version-history.show', ['OTHER-PROJECT', $firstVersion->version_id]))
        ->assertNotFound();
});

test('publication records the exact approved version and showcase renders its snapshot', function () {
    $participant = archiveVersionTestUser('PUB001', 'Peserta');
    $admin = archiveVersionTestUser('PUB002', 'Pengurus KSM');
    $project = archiveVersionTestProject('PUB001', $participant, 'Approved');
    $firstVersion = archiveVersionTestInsertVersion($project, 'VER-PUB001', 'v1.0', [
        'title_snapshot' => 'Old approved version',
        'submitted_at' => now()->subDay(),
        'created_at' => now()->subDay(),
    ]);
    $latestVersion = archiveVersionTestInsertVersion($project, 'VER-PUB002', 'v1.1', [
        'title_snapshot' => 'Published snapshot title',
        'description_snapshot' => 'Published snapshot description',
        'project_link_snapshot' => 'https://example.test/published',
        'submitted_at' => now(),
        'created_at' => now(),
    ]);

    $this->actingAs($admin)->post(
        route('dashboard.pengurus.approval.store', $project->project_id),
        [
            'action' => 'publish',
            'showcase' => '1',
        ],
    )->assertRedirect(route('dashboard.pengurus.approval.show', $project->project_id));

    $this->assertDatabaseHas('projects', [
        'project_id' => $project->project_id,
        'status' => 'Published',
        'published_version_id' => $latestVersion->version_id,
    ]);
    $this->assertDatabaseHas('project_versions', [
        'version_id' => $latestVersion->version_id,
        'approved_by' => $admin->user_id,
        'is_published' => true,
    ]);
    $this->assertDatabaseHas('project_versions', [
        'version_id' => $firstVersion->version_id,
        'is_published' => false,
    ]);
    $this->assertDatabaseHas('audit_logs', [
        'project_id' => $project->project_id,
        'action' => 'PUBLICATION_APPROVED',
    ]);
    $this->assertDatabaseHas('audit_logs', [
        'project_id' => $project->project_id,
        'action' => 'PROJECT_PUBLISHED',
    ]);

    $this->get(route('showcase'))
        ->assertOk()
        ->assertSee('Published snapshot title')
        ->assertDontSee('Project PUB001');

    $this->get(route('dashboard.pengurus.version-history.show', [$project->project_id, $latestVersion->version_id]))
        ->assertOk()
        ->assertSee('Published snapshot title')
        ->assertSee('Published')
        ->assertSee('Disetujui')
        ->assertSee('User PUB002')
        ->assertSee('https://example.test/published');

    $this->get(route('dashboard.pengurus.version-history.project', $project->project_id))
        ->assertOk()
        ->assertSee('Published snapshot title')
        ->assertSee('v1.0')
        ->assertSee('v1.1')
        ->assertSee('Detail Versi');
});

test('archiving preserves project data and restoring a published project requires publication again', function () {
    config(['filesystems.project_media_disk' => 'public']);
    Storage::fake('public');
    $participant = archiveVersionTestUser('ARC001', 'Peserta');
    $mentor = archiveVersionTestUser('ARC002', 'Mentor');
    $admin = archiveVersionTestUser('ARC003', 'Pengurus KSM');
    $project = archiveVersionTestProject('ARC001', $participant, 'Published');
    $thumbnailPath = 'projects/'.$project->project_id.'/published.png';
    Storage::disk('public')->put($thumbnailPath, 'published-thumbnail');
    DB::table('project_files')->insert([
        'file_id' => 'FILE-ARC001',
        'project_id' => $project->project_id,
        'file_name' => 'published.png',
        'file_path' => $thumbnailPath,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $version = archiveVersionTestInsertVersion($project, 'VER-ARC001', 'v1.0', [
        'title_snapshot' => 'Public project title',
        'thumbnail_path_snapshot' => $thumbnailPath,
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);
    DB::table('projects')->where('project_id', $project->project_id)->update([
        'published_version_id' => $version->version_id,
        'published_at' => $version->published_at,
        'publication_approved_by' => $admin->user_id,
        'publication_approved_at' => $version->published_at,
    ]);
    DB::table('project_version_files')->insert([
        'version_file_id' => 'VF-ARC001',
        'version_id' => $version->version_id,
        'file_id' => 'FILE-ARC001',
        'file_name_snapshot' => 'published.png',
        'file_path' => $thumbnailPath,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('reviews')->insert([
        'review_id' => 'REVIEW-ARC001',
        'project_id' => $project->project_id,
        'reviewer_id' => $mentor->user_id,
        'version_id' => $version->version_id,
        'comment' => 'Approved feedback',
        'decision' => 'Approved',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($admin)->post(route('dashboard.pengurus.projects.archive', $project->project_id))
        ->assertRedirect(route('dashboard.pengurus.archives'));

    $archivedProject = Project::query()->findOrFail($project->project_id);
    expect($archivedProject->status)->toBe('Archived')
        ->and($archivedProject->archived_from_status)->toBe('Published')
        ->and(Storage::disk('public')->exists($thumbnailPath))->toBeTrue();
    $this->assertDatabaseHas('project_version_files', ['version_id' => $version->version_id]);
    $this->assertDatabaseHas('reviews', ['review_id' => 'REVIEW-ARC001', 'version_id' => $version->version_id]);
    $this->get(route('dashboard.pengurus.archives'))
        ->assertOk()
        ->assertSee($project->title)
        ->assertSee('User ARC003');
    $this->get(route('dashboard.pengurus.archives.show', $project->project_id))
        ->assertOk()
        ->assertSee('Public project title')
        ->assertSee('v1.0')
        ->assertSee('Approved feedback');
    $this->get(route('showcase'))->assertDontSee('Public project title');

    $this->actingAs($admin)->post(route('dashboard.pengurus.archives.restore', $project->project_id))
        ->assertRedirect(route('dashboard.pengurus.archives'));

    $restoredProject = Project::query()->findOrFail($project->project_id);
    expect($restoredProject->status)->toBe('Approved')
        ->and($restoredProject->published_version_id)->toBeNull()
        ->and($restoredProject->publication_approved_at)->toBeNull()
        ->and($restoredProject->published_at->toDateTimeString())->toBe($version->published_at);
    $this->assertDatabaseHas('project_versions', [
        'version_id' => $version->version_id,
        'is_published' => true,
    ]);
    $this->assertDatabaseHas('audit_logs', [
        'project_id' => $project->project_id,
        'action' => 'PROJECT_ARCHIVED',
    ]);
    $this->assertDatabaseHas('audit_logs', [
        'project_id' => $project->project_id,
        'action' => 'PROJECT_RESTORED',
    ]);
    $this->get(route('showcase'))->assertDontSee('Public project title');
});

test('public media uses the published snapshot thumbnail and archived projects are not publicly served', function () {
    config(['filesystems.project_media_disk' => 'public']);
    Storage::fake('public');
    $participant = archiveVersionTestUser('MED001', 'Peserta');
    $admin = archiveVersionTestUser('MED002', 'Pengurus KSM');
    $project = archiveVersionTestProject('MED001', $participant, 'Published');
    $publishedPath = 'projects/'.$project->project_id.'/published.png';
    $newerUnpublishedPath = 'projects/'.$project->project_id.'/newer.png';
    Storage::disk('public')->put($publishedPath, 'published-content');
    Storage::disk('public')->put($newerUnpublishedPath, 'newer-content');
    $version = archiveVersionTestInsertVersion($project, 'VER-MED001', 'v1.0', [
        'thumbnail_path_snapshot' => $publishedPath,
        'is_published' => true,
        'published_at' => now(),
    ]);
    DB::table('projects')->where('project_id', $project->project_id)->update([
        'thumbnail_url' => $newerUnpublishedPath,
        'published_version_id' => $version->version_id,
    ]);

    $this->get(route('media.show', ['path' => $publishedPath]))->assertOk();
    $this->get(route('media.show', ['path' => $newerUnpublishedPath]))->assertNotFound();

    $this->actingAs($admin)->post(route('dashboard.pengurus.projects.archive', $project->project_id));
    $this->app['auth']->logout();
    $this->get(route('media.show', ['path' => $publishedPath]))->assertNotFound();
});

test('users without the Pengurus role cannot archive or restore projects', function () {
    $participant = archiveVersionTestUser('AUTH01', 'Peserta');
    $project = archiveVersionTestProject('AUTH01', $participant, 'Submitted');
    $version = archiveVersionTestInsertVersion($project, 'VER-AUTH01', 'v1.0');

    $this->actingAs($participant)
        ->get(route('dashboard.pengurus.version-history'))
        ->assertForbidden();
    $this->actingAs($participant)
        ->get(route('dashboard.pengurus.version-history.project', $project->project_id))
        ->assertForbidden();
    $this->actingAs($participant)
        ->get(route('dashboard.pengurus.version-history.show', [$project->project_id, $version->version_id]))
        ->assertForbidden();
    $this->actingAs($participant)
        ->get(route('dashboard.pengurus.archives'))
        ->assertForbidden();
    $this->actingAs($participant)
        ->post(route('dashboard.pengurus.projects.archive', $project->project_id))
        ->assertForbidden();

    DB::table('projects')->where('project_id', $project->project_id)->update([
        'status' => 'Archived',
        'archived_from_status' => 'Submitted',
        'archived_at' => now(),
    ]);
    $this->actingAs($participant)
        ->post(route('dashboard.pengurus.archives.restore', $project->project_id))
        ->assertForbidden();

    expect(Project::query()->findOrFail($project->project_id)->status)->toBe('Archived')
        ->and(DB::table('audit_logs')->where('project_id', $project->project_id)->count())->toBe(0);
});
