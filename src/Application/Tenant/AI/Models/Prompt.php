<?php

namespace LookstarKernel\Application\Tenant\AI\Models;

use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    protected $table = 'ai_prompt';

    protected $fillable = [
        'prompt',
        'type',
    ];
}
