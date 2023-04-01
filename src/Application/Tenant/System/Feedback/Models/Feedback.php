<?php

namespace LookstarKernel\Application\Tenant\System\Feedback\Models;

use LookstarKernel\Application\Tenant\System\Feedback\Mail as FeedbackMail;
use LookstarKernel\Support\Eloquent\TenantModel as Model;
use Illuminate\Support\Facades\Mail;

class Feedback extends Model
{
    protected $table = 'tenant_system_feedback';

    protected $fillable = [
        'score',
        'text',
    ];

    protected static function booting()
    {
        static::created(function ($row) {
            Mail::to(['hao.sun@blue-dot.cn', 'cailin.wang@blue-dot.cn'])->queue(new FeedbackMail($row));
        });
    }
}
