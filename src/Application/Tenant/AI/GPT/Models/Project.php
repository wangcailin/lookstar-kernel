<?php

namespace LookstarKernel\Application\Tenant\AI\GPT\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Project extends Model
{
    protected $table = 'tenant_ai_gpt_project';

    const TYPE_WECHAT = 'wechat';
    const TYPE_SALES = 'sales_gpt';

    protected $fillable = [
        'tenant_id',
        'type',
        'title',
        'description',
        'auth_user_id',
        'state',
    ];

    public function config()
    {
        return $this->hasOne(Config::class, 'project_id', 'id');
    }
}
