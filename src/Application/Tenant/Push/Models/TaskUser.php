<?php

namespace LookstarKernel\Application\Tenant\Push\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class TaskUser extends Model
{
    protected $connection = 'data_warehouse';
    protected $table = 'ads_push_task_user';

    protected $fillable = [
        'task_id',
        'task_type',
        'value',
        'status',
    ];
}
