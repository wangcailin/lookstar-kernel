<?php

namespace LookstarKernel\Application\Tenant\Group\Models\Analytics;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class AdsGroupUserInfo extends Model
{
    protected $connection = 'data_warehouse';
    protected $table = 'ads_group_user_info';
}
