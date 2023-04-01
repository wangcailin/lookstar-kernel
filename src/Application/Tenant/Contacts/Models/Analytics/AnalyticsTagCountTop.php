<?php

namespace LookstarKernel\Application\Tenant\Contacts\Models\Analytics;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class AnalyticsTagCountTop extends Model
{
    protected $connection = 'data_warehouse';
    protected $table = 'ads_user_tag_count_top50';
}
