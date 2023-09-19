<?php

namespace LookstarKernel\Application\Tenant\AI\GPT\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Conversation extends Model
{
    protected $table = 'tenant_ai_gpt_conversation';

    const TYPE_VMS = 'vms';
    const TYPE_CHATGPT = 'ChatGPT';
    public static $excludedResult = ['无法回答该问题。'];
    protected $fillable = [
        'tenant_id',
        'project_id',
        'message',
        'result',
        'source_documents',
        'type',
        'openid',
        'is_timeout_reply',
    ];
    protected $casts = [
        'source_documents' => 'json',
    ];
}
