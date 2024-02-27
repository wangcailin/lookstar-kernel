<?php

namespace LookstarKernel\Application\Tenant\AI\GPT\Models;

use LookstarKernel\Application\Tenant\WeChat\Models\WeChatOpenid;
use LookstarKernel\Support\Eloquent\TenantModel as Model;
use Carbon\Carbon;

class Conversation extends Model
{
    protected $table = 'tenant_ai_gpt_conversation';

    const TYPE_VMS = 'vms';
    const TYPE_CHATGPT = 'ChatGPT';
    const TYPE_SALES_GPT = 'SalesGPT';
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
        'distinct_id',
    ];
    protected $casts = [
        'source_documents' => 'json',
    ];

    public function wechat()
    {
        return $this->hasOne(WeChatOpenid::class, 'openid', 'openid')->select(['appid', 'openid', 'nickname', 'avatar']);
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone('Asia/Shanghai')->format('Y-m-d H:i:s');
    }
}
