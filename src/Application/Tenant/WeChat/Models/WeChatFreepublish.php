<?php

namespace LookstarKernel\Application\Tenant\WeChat\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class WeChatFreepublish extends Model
{
    protected $table = 'tenant_wechat_freepublish';

    protected $fillable = [
        'article_id',
        'appid',
        'title',
        'url',
        'author',
        'digest',
        'thumb_media_id',
        'show_cover_pic',
        'is_deleted',
        'thumb_url',
        'update_time',
        'create_time',
    ];
}
