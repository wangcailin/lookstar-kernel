<?php

namespace LookstarKernel\Application\Tenant\Project\MicroBook\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class AnalyticsD extends Model
{
    protected $connection = 'data_warehouse';
    protected $table = 'ads_project_microbook_d';
}
