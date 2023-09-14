<?php

namespace LookstarKernel\Application\Tenant\AI\GPT\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class PromptConfig extends Model
{
    const DEFAULT_TEMPERATURE = 0.2;
    protected $table = 'tenant_ai_gpt_prompt_config';

    const LLM_TYPE_GPT_TURBO = 'gpt-3.5-turbo';
    const LLM_TYPE_GPT = 'gpt-4';
    const LLM_TYPE_ERNIE_BOT_turbo = 'ERNIE-Bot-turbo';
    const LLM_TYPE_ERNIE_BOT = 'ERNIE-Bot';

    const LLM_LABELS = [
        self::LLM_TYPE_GPT_TURBO,
        self::LLM_TYPE_ERNIE_BOT,
        self::LLM_TYPE_ERNIE_BOT_turbo,
        self::LLM_TYPE_GPT
    ];

    protected $fillable = [
        'tenant_id',
        'project_id',
        'data',
        'temperature',
        'llm_name',
        'is_chat_history',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
