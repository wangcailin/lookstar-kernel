<?php

namespace LookstarKernel\Application\Tenant\Ads\Tencent\Audiences\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Custom extends Model
{

    protected $table = 'tenant_ads_tencent_audiences_custom';

    protected $fillable = [
        'account_id',
        'audience_id',
        'custom_name',
        'name',
        'type',
        'description',
    ];
}
