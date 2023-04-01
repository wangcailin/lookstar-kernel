<?php

namespace LookstarKernel\Application\Tenant\Ads\Tencent\Audiences\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class CustomFiles extends Model
{

    protected $table = 'tenant_ads_tencent_audiences_custom_files';

    protected $fillable = [
        'account_id',
        'audience_id',
        'custom_audience_file_id',
        'user_id_type',
        'operation_type',
        'open_app_id',
        'custom_name',
        'line_count',
        'user_count',
        'size',
    ];
}
