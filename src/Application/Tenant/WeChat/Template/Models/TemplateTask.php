<?php

namespace LookstarKernel\Application\Tenant\WeChat\Template\Models;

use LookstarKernel\Application\Tenant\Push\Models\TemplateTask as BaseTemplateTask;

class TemplateTask extends BaseTemplateTask
{
    protected $table = 'tenant_wechat_template_task';
    public $taskType = 'template';
}
