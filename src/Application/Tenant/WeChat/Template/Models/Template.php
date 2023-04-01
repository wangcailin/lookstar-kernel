<?php

namespace LookstarKernel\Application\Tenant\WeChat\Template\Models;

use LookstarKernel\Application\Tenant\WeChat\Models\WeChatAuthorizer;
use LookstarKernel\Support\Eloquent\TenantModel;

class Template extends TenantModel
{
    protected $table = 'tenant_wechat_template';

    protected $fillable = [
        'appid',
        'template_id',
        'title',
        'data',
        'status',
    ];

    protected $casts = [
        'data' => 'json',
    ];

    public $appends = ['last_send_time'];

    public function authorizer()
    {
        return $this->hasOne(WeChatAuthorizer::class, 'appid', 'appid');
    }

    public function task()
    {
        return $this->hasOne(TemplateTask::class, 'template_id', 'id');
    }

    public function getLastSendTimeAttribute()
    {
        $sendTime = TemplateTask::where(['template_id' => $this->id, 'status' => 3])
            ->orderBy('send_time', 'DESC')->limit(1)->value('send_time');
        return $sendTime;
    }
}
