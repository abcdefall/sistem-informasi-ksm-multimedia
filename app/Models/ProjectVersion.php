<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ProjectVersion extends Model
{
    protected $primaryKey = 'version_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'version_id',
        'project_id',
        'version_number',
        'changelog',
        'file_path',
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
    ];

    protected $casts = [
        'team_snapshot' => 'array',
        'tools_snapshot' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
        'is_published' => 'boolean',
        'is_legacy_snapshot' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by', 'user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProjectVersionFile::class, 'version_id', 'version_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'version_id', 'version_id');
    }

    public static function sortChronologically(Collection $versions): Collection
    {
        return $versions->sort(function (object $left, object $right): int {
            $leftSequence = self::versionSequence((string) ($left->version_number ?? '')) ?? [-1, -1];
            $rightSequence = self::versionSequence((string) ($right->version_number ?? '')) ?? [-1, -1];
            $sequenceComparison = [$leftSequence[0], $leftSequence[1]] <=> [$rightSequence[0], $rightSequence[1]];

            if ($sequenceComparison !== 0) {
                return $sequenceComparison;
            }

            $submissionComparison = strcmp(
                (string) ($left->submitted_at ?? $left->created_at ?? ''),
                (string) ($right->submitted_at ?? $right->created_at ?? ''),
            );
            if ($submissionComparison !== 0) {
                return $submissionComparison;
            }

            return strcmp((string) ($left->version_id ?? ''), (string) ($right->version_id ?? ''));
        })->values();
    }

    public static function versionSequence(string $versionNumber): ?array
    {
        if (preg_match('/^v?(\d+)(?:\.(\d+))?$/i', $versionNumber, $matches) !== 1) {
            return null;
        }

        return [(int) $matches[1], (int) ($matches[2] ?? 0)];
    }
}
