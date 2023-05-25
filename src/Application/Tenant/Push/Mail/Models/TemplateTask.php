<?php

namespace LookstarKernel\Application\Tenant\Push\Mail\Models;

use LookstarKernel\Application\Tenant\Push\Models\TemplateTask as BaseTemplateTask;

class TemplateTask extends BaseTemplateTask
{
    protected $table = 'tenant_push_mail_template_task';
    public $taskType = 'edm';
}
