<?php

namespace LookstarKernel\Application\Tenant\Project\Event\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Event extends Model
{
    protected $table = 'tenant_project_event';

    protected $fillable = [
        'project_id',
        'type',
        'start_time',
        'end_time',
        'banner_img',
        'address',
        'module_status',
    ];

    protected $casts = [
        'module_status' => 'array',
    ];
}
