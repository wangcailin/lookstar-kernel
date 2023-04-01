<?php

namespace LookstarKernel\Application\Tenant\Project\Landing\Models;

use Illuminate\Database\Eloquent\Model;

class Landing extends Model
{
    protected $table = 'tenant_project_landing';

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'state',
    ];
}
