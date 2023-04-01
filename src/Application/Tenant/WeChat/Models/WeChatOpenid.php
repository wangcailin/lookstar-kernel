<?php

namespace LookstarKernel\Application\Tenant\WeChat\Models;

use Composer\Application\User\Models\UserWeChatOpenid;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use LookstarKernel\Application\Tenant\WeChat\Models\WeChatAuthorizer;

class WeChatOpenid extends UserWeChatOpenid
{
    use BelongsToTenant;
    protected $table = 'tenant_wechat_openid';

    protected $fillable = [
        'unionid',
        'appid',
        'openid',
        'subscribe',
        'subscribe_time',
        'subscribe_scene',
        'qr_scene',
        'qr_scene_str',
        'nickname',
        'avatar',
    ];

    public function authorizer()
    {
        return $this->hasOne(WeChatAuthorizer::class, 'appid', 'appid');
    }
}
