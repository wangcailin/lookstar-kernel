<?php

namespace LookstarKernel\Application\Tenant\System\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Resource extends Model
{
    protected $table = 'tenant_system_resource';

    protected $fillable = [
        'title',
        'mime_type',
        'extension',
        'size',
        'url',
        'app_source'
    ];
}
