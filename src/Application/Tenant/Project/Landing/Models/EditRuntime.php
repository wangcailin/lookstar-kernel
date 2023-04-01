<?php

namespace LookstarKernel\Application\Tenant\Project\Landing\Models;

use Illuminate\Database\Eloquent\Model;

class EditRuntime extends Model
{
    protected $table = 'tenant_project_landing_edit_runtime';

    public $incrementing = false;

    protected $fillable = [
        'project_id',
        'template_id',
        'data',
    ];

    protected $casts = [
        'data' => 'json',
    ];
}
