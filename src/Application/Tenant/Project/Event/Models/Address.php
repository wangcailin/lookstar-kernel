<?php

namespace LookstarKernel\Application\Tenant\Project\Event\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Address extends Model
{
    protected $table = 'tenant_project_event_address';

    protected $fillable = [
        'event_id',
        'view',
        'data',
    ];

    protected $casts = [
        'data' => 'json',
    ];
}
