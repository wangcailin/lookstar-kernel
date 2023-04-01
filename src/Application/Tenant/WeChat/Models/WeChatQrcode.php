<?php

namespace LookstarKernel\Application\Tenant\WeChat\Models;

use Composer\Application\WeChat\Models\Qrcode;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use LookstarKernel\Application\Tenant\WeChat\Models\Analytics\AnalyticsQrcode;

class WeChatQrcode extends Qrcode
{
    use BelongsToTenant;

    protected $table = 'tenant_wechat_qrcode';
    protected $appends = ['overview'];

    public function authorizer()
    {
        return $this->hasOne(WeChatAuthorizer::class, 'appid', 'appid');
    }

    public function getOverviewAttribute()
    {
        return AnalyticsQrcode::overview($this->appid, $this->scene_str);
    }
}
