<?php

namespace LookstarKernel\Application\Tenant\WeChat\Models;

use Composer\Application\WeChat\Models\Qrcode;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class WeChatMenu extends Qrcode
{
    use BelongsToTenant;

    protected $table = 'tenant_wechat_menu';

    protected $fillable = [
        'appid',
        'name',
        'type',
        'value',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
