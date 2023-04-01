<?php

namespace LookstarKernel\Application\Tenant\Utm\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Utm extends Model
{
    protected $table = 'tenant_utm';

    protected $fillable = [
        'type',
        'project_id',
        'utm_campaign',
        'utm_source',
        'utm_medium',
        'utm_term',
        'utm_content',
        'appid',
    ];
}
