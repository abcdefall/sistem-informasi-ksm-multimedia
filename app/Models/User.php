<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {
    protected $primaryKey = 'user_id';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['role_id', 'name', 'email', 'password_hash', 'profile_data'];
    protected $hidden = ['password_hash']; // Laravel Auth bawaan butuh penyesuaian jika kolom bukan 'password'
    
    // Override password getter untuk sistem Auth Laravel jika menggunakan nama kustom
    public function getAuthPassword() {
        return $this->password_hash;
    }

    public function role() {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function createdProjects() {
        return $this->hasMany(Project::class, 'created_by', 'user_id');
    }
}
