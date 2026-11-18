<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Project extends Model
{
    protected $primaryKey = 'project_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $casts = [
        'submitted_at' => 'datetime',
        'published_at' => 'datetime',
        'publication_approved_at' => 'datetime',
        'archived_at' => 'datetime',
        'is_featured' => 'boolean',
    ];

    protected $fillable = [
        'project_id', 'field_id', 'period_id', 'title', 'description', 'thumbnail_url',
        'project_link', 'status', 'submitted_at', 'published_at', 'created_by',
        'publication_approved_by', 'publication_approved_at', 'is_featured',
        'archived_from_status', 'archived_at', 'archived_by', 'published_version_id',
    ];

    public function getThumbnailDisplayUrlAttribute(): ?string
    {
        return $this->mediaUrl($this->thumbnail_url);
    }

    public function getPublishedThumbnailUrlAttribute(): ?string
    {
        return $this->mediaUrl($this->published_snapshot['thumbnail_path']);
    }

    private function mediaUrl(?string $thumbnail): ?string
    {
        if (! is_string($thumbnail) || $thumbnail === '' || $thumbnail === '0') {
            return null;
        }

        if (Str::startsWith($thumbnail, ['http://', 'https://'])) {
            return $thumbnail;
        }

        return route('media.show', ['path' => $thumbnail], false);
    }

    public function getPublishedSnapshotAttribute(): array
    {
        $version = $this->publishedVersion;
        $hasSnapshot = $version !== null && $version->title_snapshot !== null;

        return [
            'title' => $hasSnapshot ? $version->title_snapshot : $this->title,
            'description' => $hasSnapshot ? $version->description_snapshot : $this->description,
            'field_id' => $hasSnapshot ? $version->field_id_snapshot : $this->field_id,
            'field_name' => $hasSnapshot ? $version->field_name_snapshot : $this->mentoringField?->field_name,
            'period_id' => $hasSnapshot ? $version->period_id_snapshot : $this->period_id,
            'period_name' => $hasSnapshot ? $version->period_name_snapshot : $this->period?->period_name,
            'project_link' => $hasSnapshot ? $version->project_link_snapshot : $this->project_link,
            'thumbnail_path' => $hasSnapshot ? $version->thumbnail_path_snapshot : $this->thumbnail_url,
            'team' => $hasSnapshot ? $version->team_snapshot : null,
            'tools' => $hasSnapshot ? $version->tools_snapshot : null,
        ];
    }

    public function mentoringField(): BelongsTo
    {
        return $this->belongsTo(MentoringField::class, 'field_id', 'field_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(MentoringPeriod::class, 'period_id', 'period_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class, 'project_id', 'project_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ProjectVersion::class, 'project_id', 'project_id');
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by', 'user_id');
    }

    public function publishedVersion(): BelongsTo
    {
        return $this->belongsTo(ProjectVersion::class, 'published_version_id', 'version_id');
    }
}
