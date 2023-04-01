<?php

namespace LookstarKernel\Application\Tenant\WeChat\Models;

use Composer\Application\WeChat\Models\Material;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class WeChatMaterial extends Material
{
    use BelongsToTenant;

    protected $table = 'tenant_wechat_material';
}
