<?php

namespace LookstarKernel\Application\Tenant\Project\Event\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Speaker extends Model
{
    protected $table = 'tenant_project_event_speaker';

    protected $fillable = [
        'event_id',
        'sort',
        'username',
        'avatar',
        'job',
        'desc'
    ];
}
