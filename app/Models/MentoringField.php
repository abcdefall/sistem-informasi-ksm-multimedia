<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentoringField extends Model {
    protected $primaryKey = 'field_id';
    protected $fillable = ['field_name', 'description'];

    public function projects() {
        return $this->hasMany(Project::class, 'field_id', 'field_id');
    }
}
