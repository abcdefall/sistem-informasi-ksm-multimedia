<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model {
    protected $primaryKey = 'role_id';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['role_name'];

    public function users() {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }
}