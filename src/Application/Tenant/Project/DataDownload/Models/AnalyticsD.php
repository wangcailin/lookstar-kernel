<?php

namespace LookstarKernel\Application\Tenant\Project\DataDownload\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class AnalyticsD extends Model
{
    protected $connection = 'data_warehouse';
    protected $table = 'ads_project_data_download_d';
}
