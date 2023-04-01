<?php

namespace LookstarKernel\Application\Tenant\DataCenter\LifeCycle\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class AdsUserLifeCycleD extends Model
{
    protected $connection = 'data_warehouse';
    protected $table = 'ads_user_lifecycle_d';
}
