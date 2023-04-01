<?php

namespace LookstarKernel\Application\Tenant\WeChat\Template\Models;

use LookstarKernel\Support\Eloquent\TenantModel;

class TemplateWeChat extends TenantModel
{
    protected $table = 'tenant_wechat_template_wechat';

    protected $fillable = [
        'appid',
        'template_id',
        'title',
        'primary_industry',
        'deputy_industry',
        'content',
        'example',
    ];
}
