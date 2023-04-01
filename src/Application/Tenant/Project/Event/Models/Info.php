<?php

namespace LookstarKernel\Application\Tenant\Project\Event\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Info extends Model
{
    protected $table = 'tenant_project_event_info';

    protected $fillable = [
        'event_id',
        'content',
    ];
}
