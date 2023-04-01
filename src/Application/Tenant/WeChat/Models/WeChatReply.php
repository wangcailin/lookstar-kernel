<?php

namespace LookstarKernel\Application\Tenant\WeChat\Models;

use LookstarKernel\Application\Tenant\WeChat\Models\Analytics\AnalyticsReply;
use Composer\Application\WeChat\Models\Reply;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class WeChatReply extends Reply
{
    use BelongsToTenant;

    protected $table = 'tenant_wechat_reply';
    protected $appends = ['overview'];

    public function authorizer()
    {
        return $this->hasOne(WeChatAuthorizer::class, 'appid', 'appid');
    }

    public function getOverviewAttribute()
    {
        return AnalyticsReply::overview($this->appid, $this->id);
    }
}
