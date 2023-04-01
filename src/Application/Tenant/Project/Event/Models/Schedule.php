<?php

namespace LookstarKernel\Application\Tenant\Project\Event\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Schedule extends Model
{
    protected $table = 'tenant_project_event_schedule';

    protected $fillable = [
        'event_id',
        'sort',
        'start_time',
        'end_time',
        'content',
    ];
}
