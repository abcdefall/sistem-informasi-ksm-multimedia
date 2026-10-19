<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model {
    protected $primaryKey = 'project_id';
    protected $fillable = [
        'field_id', 'period_id', 'title', 'description', 'thumbnail_url', 
        'project_link', 'status', 'submitted_at', 'published_at', 'created_by'
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
