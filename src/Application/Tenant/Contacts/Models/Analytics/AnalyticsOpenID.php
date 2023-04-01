<?php

namespace LookstarKernel\Application\Tenant\Contacts\Models\Analytics;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class AnalyticsOpenID extends Model
{
    protected $connection = 'data_warehouse';
    protected $table = 'dim_user_mobile_openid';
}
