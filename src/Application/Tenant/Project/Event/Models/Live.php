<?php

namespace LookstarKernel\Application\Tenant\Project\Event\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Live extends Model
{
    protected $table = 'tenant_project_event_live';

    protected $fillable = [
        'event_id',
        'like',
    ];
}
