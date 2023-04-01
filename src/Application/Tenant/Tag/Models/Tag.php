<?php

namespace LookstarKernel\Application\Tenant\Tag\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Tag extends Model
{
    protected $table = 'tenant_tag';

    protected $fillable = [
        'group_id',
        'name',
        'remark',
        'state',
    ];
}
