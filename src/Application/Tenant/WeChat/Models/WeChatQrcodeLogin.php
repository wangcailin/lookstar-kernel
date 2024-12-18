<?php

namespace LookstarKernel\Application\Tenant\WeChat\Models;

use Composer\Application\WeChat\Models\Qrcode;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class WeChatQrcodeLogin extends Qrcode
{
    use BelongsToTenant;

    protected $table = 'tenant_wechat_qrcode_login';

    protected $fillable = [
        'login_code',
        'openid',
    ];
}
