<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_versions', function (Blueprint $table): void {
            $table->string('title_snapshot')->nullable();
            $table->text('description_snapshot')->nullable();
            $table->string('field_id_snapshot', 20)->nullable();
            $table->string('field_name_snapshot')->nullable();
            $table->string('period_id_snapshot', 20)->nullable();
            $table->string('period_name_snapshot')->nullable();
            $table->string('project_link_snapshot')->nullable();
            $table->string('thumbnail_path_snapshot')->nullable();
            $table->json('team_snapshot')->nullable();
            $table->json('tools_snapshot')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->string('submitted_by', 20)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('approved_by', 20)->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_legacy_snapshot')->default(false);

            $table->foreign('submitted_by')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('approved_by')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::create('project_version_files', function (Blueprint $table): void {
            $table->string('version_file_id', 20)->primary();
            $table->string('version_id', 20);
            $table->string('file_id', 20)->nullable();
            $table->string('file_name_snapshot', 255);
            $table->string('file_path', 255);
            $table->timestamps();

            $table->foreign('version_id')
                ->references('version_id')
                ->on('project_versions')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('file_id')
                ->references('file_id')
                ->on('project_files')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::table('reviews', function (Blueprint $table): void {
            $table->string('version_id', 20)->nullable();
            $table->string('decision', 50)->nullable();

            $table->foreign('version_id')
                ->references('version_id')
                ->on('project_versions')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->string('published_version_id', 20)->nullable();

            $table->foreign('published_version_id')
                ->references('version_id')
                ->on('project_versions')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

        $this->markExistingVersionsAsLegacy();
        $this->createLegacySnapshotsForProjectsWithoutVersions();
        $this->restorePublicationApprovals();
        $this->restorePublishedVersionPointers();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropForeign(['published_version_id']);
            $table->dropColumn('published_version_id');
        });

        Schema::table('reviews', function (Blueprint $table): void {
            $table->dropForeign(['version_id']);
            $table->dropColumn(['version_id', 'decision']);
        });

        Schema::dropIfExists('project_version_files');

        Schema::table('project_versions', function (Blueprint $table): void {
            $table->dropForeign(['submitted_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'title_snapshot',
                'description_snapshot',
                'field_id_snapshot',
                'field_name_snapshot',
                'period_id_snapshot',
                'period_name_snapshot',
                'project_link_snapshot',
                'thumbnail_path_snapshot',
                'team_snapshot',
                'tools_snapshot',
                'submitted_at',
                'submitted_by',
                'approved_at',
                'approved_by',
                'is_published',
                'published_at',
                'is_legacy_snapshot',
            ]);
        });
    }

    private function markExistingVersionsAsLegacy(): void
    {
        DB::table('project_versions')
            ->orderBy('version_id')
            ->chunk(100, function ($versions): void {
                foreach ($versions as $version) {
                    DB::table('project_versions')
                        ->where('version_id', $version->version_id)
                        ->update(['is_legacy_snapshot' => true]);

                    if (! $version->file_path) {
                        continue;
                    }

                    $file = DB::table('project_files')
                        ->where('project_id', $version->project_id)
                        ->where('file_path', $version->file_path)
                        ->first();

                    $this->insertVersionFile(
                        $version->version_id,
                        $file?->file_id,
                        $file?->file_name ?? basename($version->file_path),
                        $version->file_path,
                    );
                }
            });
    }

    private function createLegacySnapshotsForProjectsWithoutVersions(): void
    {
        DB::table('projects')
            ->where('status', '<>', 'Draft')
            ->orderBy('project_id')
            ->chunk(100, function ($projects): void {
                foreach ($projects as $project) {
                    if (DB::table('project_versions')->where('project_id', $project->project_id)->exists()) {
                        continue;
                    }

                    $field = DB::table('mentoring_fields')->where('field_id', $project->field_id)->first();
                    $period = DB::table('mentoring_periods')->where('period_id', $project->period_id)->first();
                    $submittedAt = $project->submitted_at ?? $project->created_at ?? now();
                    $versionId = $this->newVersionId();

                    DB::table('project_versions')->insert([
                        'version_id' => $versionId,
                        'project_id' => $project->project_id,
                        'version_number' => 'v1.0',
                        'changelog' => null,
                        'file_path' => null,
                        'title_snapshot' => $project->title,
                        'description_snapshot' => $project->description,
                        'field_id_snapshot' => $project->field_id,
                        'field_name_snapshot' => $field?->field_name,
                        'period_id_snapshot' => $project->period_id,
                        'period_name_snapshot' => $period?->period_name,
                        'project_link_snapshot' => $project->project_link,
                        'thumbnail_path_snapshot' => $project->thumbnail_url,
                        'team_snapshot' => json_encode($this->teamSnapshot($project->project_id), JSON_THROW_ON_ERROR),
                        'tools_snapshot' => json_encode(
                            DB::table('project_tools')->where('project_id', $project->project_id)->orderBy('tool_name')->pluck('tool_name')->all(),
                            JSON_THROW_ON_ERROR,
                        ),
                        'submitted_at' => $submittedAt,
                        'submitted_by' => $project->created_by,
                        'approved_at' => $project->publication_approved_at,
                        'approved_by' => $project->publication_approved_by,
                        'is_published' => $project->published_at !== null,
                        'published_at' => $project->published_at,
                        'is_legacy_snapshot' => true,
                        'created_at' => $submittedAt,
                        'updated_at' => now(),
                    ]);

                    foreach (DB::table('project_files')->where('project_id', $project->project_id)->orderBy('file_id')->get() as $file) {
                        $this->insertVersionFile($versionId, $file->file_id, $file->file_name, $file->file_path);
                    }

                    if ($project->status === 'Published') {
                        DB::table('projects')
                            ->where('project_id', $project->project_id)
                            ->update(['published_version_id' => $versionId]);
                    }
                }
            });
    }

    private function restorePublishedVersionPointers(): void
    {
        DB::table('projects')
            ->where('status', 'Published')
            ->orderBy('project_id')
            ->chunk(100, function ($projects): void {
                foreach ($projects as $project) {
                    $version = $this->latestVersionForProject($project->project_id);

                    if (! $version) {
                        continue;
                    }

                    DB::table('project_versions')
                        ->where('version_id', $version->version_id)
                        ->update([
                            'is_published' => true,
                            'published_at' => $project->published_at,
                            'approved_at' => $project->publication_approved_at ?? $version->approved_at,
                            'approved_by' => $project->publication_approved_by ?? $version->approved_by,
                        ]);
                    DB::table('projects')
                        ->where('project_id', $project->project_id)
                        ->update(['published_version_id' => $version->version_id]);
                }
            });
    }

    private function restorePublicationApprovals(): void
    {
        DB::table('projects')
            ->whereIn('status', ['Approved', 'Published'])
            ->whereNotNull('publication_approved_at')
            ->orderBy('project_id')
            ->chunk(100, function ($projects): void {
                foreach ($projects as $project) {
                    $version = $this->latestVersionForProject($project->project_id);

                    if (! $version) {
                        continue;
                    }

                    DB::table('project_versions')
                        ->where('version_id', $version->version_id)
                        ->update([
                            'approved_at' => $project->publication_approved_at,
                            'approved_by' => $project->publication_approved_by,
                        ]);
                }
            });
    }

    private function latestVersionForProject(string $projectId): ?object
    {
        return DB::table('project_versions')
            ->where('project_id', $projectId)
            ->get()
            ->sort(function (object $left, object $right): int {
                $leftSequence = $this->versionSequence($left->version_number) ?? [-1, -1];
                $rightSequence = $this->versionSequence($right->version_number) ?? [-1, -1];
                $sequenceComparison = [$leftSequence[0], $leftSequence[1]] <=> [$rightSequence[0], $rightSequence[1]];

                if ($sequenceComparison !== 0) {
                    return $sequenceComparison;
                }

                return strcmp(
                    (string) ($left->submitted_at ?? $left->created_at ?? ''),
                    (string) ($right->submitted_at ?? $right->created_at ?? ''),
                );
            })
            ->last();
    }

    /**
     * @return array{int, int}|null
     */
    private function versionSequence(string $versionNumber): ?array
    {
        if (preg_match('/^v?(\d+)(?:\.(\d+))?$/i', $versionNumber, $matches) !== 1) {
            return null;
        }

        return [(int) $matches[1], (int) ($matches[2] ?? 0)];
    }

    private function teamSnapshot(string $projectId): array
    {
        return DB::table('project_members')
            ->join('users', 'users.user_id', '=', 'project_members.user_id')
            ->where('project_members.project_id', $projectId)
            ->orderBy('users.name')
            ->get(['project_members.user_id', 'users.name', 'project_members.member_role'])
            ->map(fn ($member): array => [
                'user_id' => $member->user_id,
                'name' => $member->name,
                'member_role' => $member->member_role,
            ])
            ->all();
    }

    private function insertVersionFile(string $versionId, ?string $fileId, string $fileName, string $filePath): void
    {
        do {
            $versionFileId = 'VF-'.Str::upper(Str::random(17));
        } while (DB::table('project_version_files')->where('version_file_id', $versionFileId)->exists());

        DB::table('project_version_files')->insert([
            'version_file_id' => $versionFileId,
            'version_id' => $versionId,
            'file_id' => $fileId,
            'file_name_snapshot' => $fileName,
            'file_path' => $filePath,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function newVersionId(): string
    {
        do {
            $versionId = 'VER-'.Str::upper(Str::random(15));
        } while (DB::table('project_versions')->where('version_id', $versionId)->exists());

        return $versionId;
    }
};
