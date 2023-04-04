<?php

namespace LookstarKernel\Application\Tenant\Push\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;
use Composer\Exceptions\ApiErrorCode;
use Composer\Exceptions\ApiException;

class TemplateTask extends Model
{
    protected $table = 'tenant_push_mail_template_task';
    public $taskType = '';

    protected $fillable = [
        'template_id',
        'status',
        'title',
        'send_status',
        'send_time',
        'send_result',
    ];

    protected $casts = [
        'send_result' => 'json',
    ];

    protected $appends = ['send_cnt'];

    public function getSendCntAttribute()
    {
        return TaskUser::where(['task_id' => $this->id, 'task_type' => $this->taskType])->count();
    }

    public function verifyChange()
    {
        if ($this->status !== 0 && $this->status !== 1) {
            throw new ApiException('任务在进行中或已结束不允许操作', ApiErrorCode::VERIFY_CODE_ERROR);
        }
    }
}
