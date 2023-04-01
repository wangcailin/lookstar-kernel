<?php

namespace LookstarKernel\Application\Tenant\Project\Landing\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table = 'project_landing_template';

    protected $fillable = [
        'type',
        'title',
        'img',
        'data',
    ];

    protected $casts = [
        'data' => 'json',
    ];
}
