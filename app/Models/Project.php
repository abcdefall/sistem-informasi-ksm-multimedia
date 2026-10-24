<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model {
    protected $primaryKey = 'project_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'submitted_at' => 'datetime',
        'published_at' => 'datetime',
        'publication_approved_at' => 'datetime',
        'is_featured' => 'boolean',
    ];

    protected $fillable = [
        'project_id', 'field_id', 'period_id', 'title', 'description', 'thumbnail_url',
        'project_link', 'status', 'submitted_at', 'published_at', 'created_by',
        'publication_approved_by', 'publication_approved_at', 'is_featured',
    ];

    public function mentoringField() {
        return $this->belongsTo(MentoringField::class, 'field_id', 'field_id');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function members() {
        return $this->hasMany(ProjectMember::class, 'project_id', 'project_id');
    }
}
