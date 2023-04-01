<?php

namespace LookstarKernel\Application\Tenant\Project\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Config extends Model
{
    protected $table = 'tenant_project_config';

    protected $fillable = [
        'project_id',
        'data',
        'data->title',
        'data->color',
        'data->is_banner',
        'data->banner',
        'data->share',
        'data->logo',
        'data->form',
        'data->extend',
    ];

    protected $casts = [
        'data' => 'object',
    ];
}
