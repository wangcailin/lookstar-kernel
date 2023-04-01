<?php

namespace LookstarKernel\Application\Tenant\Contacts\Models\Analytics;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class AnalyticsUsersIdMapping extends Model
{
    protected $connection = 'data_warehouse';
    protected $table = 'dim_users_id_mapping';
}
