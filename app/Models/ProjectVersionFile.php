<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectVersionFile extends Model
{
    protected $primaryKey = 'version_file_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'version_file_id',
        'version_id',
        'file_id',
        'file_name_snapshot',
        'file_path',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(ProjectVersion::class, 'version_id', 'version_id');
    }
}
