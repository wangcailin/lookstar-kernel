<?php

namespace LookstarKernel\Application\Tenant\AI\Models;

use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    protected $table = 'prompt';

    protected $fillable = [
        'prompt',
        'type',
    ];
}
