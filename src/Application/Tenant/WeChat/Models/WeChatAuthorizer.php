<?php

namespace LookstarKernel\Application\Tenant\WeChat\Models;

use Composer\Application\WeChat\Models\Authorizer;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class WeChatAuthorizer extends Authorizer
{
    use BelongsToTenant;

    protected $table = 'tenant_wechat_authorizer';

    public function reply()
    {
        return $this->hasOne(WeChatAIReply::class, 'appid', 'appid');
    }
}
