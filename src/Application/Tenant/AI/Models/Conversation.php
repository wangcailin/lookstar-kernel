<?php

namespace LookstarKernel\Application\Tenant\AI\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $table = 'ai_conversation';

    protected $fillable = [
        'message',
        'result',
        'prompt_id',
        'openid',
        'distinct_id',
        'source',
    ];
}
