<?php

namespace LookstarKernel\Application\Tenant\WeChat\Models;

use LookstarKernel\Application\Tenant\AI\GPT\Models\Project;
use LookstarKernel\Support\Eloquent\TenantModel as Model;

class WeChatAIReply extends Model
{
    protected $table = 'tenant_wechat_ai_reply';

    protected $fillable = [
        'appid',
        'project_id',
        'state',
    ];

    public function project()
    {
        return $this->hasOne(Project::class, 'id', 'project_id');
    }
}
