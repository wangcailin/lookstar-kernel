<?php

namespace LookstarKernel\Application\Tenant\Group\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Group extends Model
{
    protected $table = 'tenant_group';

    protected $fillable = [
        'type',
        'title',
        'filter',
        'total'
    ];

    protected $casts = [
        'filter' => 'array',
        'total' => 'json',
    ];
}
