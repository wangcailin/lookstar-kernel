<?php

namespace LookstarKernel\Application\Tenant\Project\Landing\Models;

use Illuminate\Database\Eloquent\Model;

class ReleaseRuntime extends Model
{
    protected $table = 'tenant_project_landing_release_runtime';

    protected $fillable = [
        'project_id',
        'template_id',
        'data',
        'form',
        'state'
    ];

    protected $casts = [
        'data' => 'json',
        'form' => 'json',
    ];
}
