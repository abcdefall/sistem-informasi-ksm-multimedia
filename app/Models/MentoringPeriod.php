<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentoringPeriod extends Model
{
    protected $primaryKey = 'period_id';
    protected $keyType = 'string';
    public $incrementing = false;
}
