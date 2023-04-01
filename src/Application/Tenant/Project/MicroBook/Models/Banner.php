<?php

namespace LookstarKernel\Application\Tenant\Project\MicroBook\Models;

use LookstarKernel\Application\Tenant\WeChat\Models\WeChatFreepublish;
use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Banner extends Model
{
    protected $table = 'tenant_project_microbook_banner';

    protected $fillable = [
        'project_id',
        'img',
        'type',
        'title',
        'link',
        'freepublish_id',
        'tag_ids',
        'sort',
    ];

    protected $casts = [
        'tag_ids' => 'array',
    ];

    public function freepublish()
    {
        return $this->hasOne(WeChatFreepublish::class, 'id', 'freepublish_id');
    }
}
