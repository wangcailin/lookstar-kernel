<?php

namespace LookstarKernel\Application\Tenant\Push\Mail\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Template extends Model
{
    protected $table = 'tenant_push_mail_template';

    protected $fillable = [
        'title',
        'subject',
        'type',
        'body',
        'edit_json',
        'auth_role_id',
    ];

    public $appends = ['last_send_time'];

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
