<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $primaryKey = 'review_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $casts = [
        'rubric_scores' => 'array',
        'score' => 'integer',
    ];

    public function getScoreOutOf100Attribute(): ?int
    {
        if ($this->score !== null) {
            return $this->score;
        }

        return $this->rating !== null ? (int) $this->rating * 20 : null;
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(ProjectVersion::class, 'version_id', 'version_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id', 'user_id');
    }
}
