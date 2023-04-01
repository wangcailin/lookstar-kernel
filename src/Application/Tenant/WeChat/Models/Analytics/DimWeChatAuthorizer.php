<?php

namespace LookstarKernel\Application\Tenant\WeChat\Models\Analytics;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class DimWeChatAuthorizer extends Model
{
    protected $connection = 'data_warehouse';
    protected $table = 'dim_tenant_wechat_authorizer';
}
