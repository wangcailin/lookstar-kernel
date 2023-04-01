<?php

namespace LookstarKernel\Application\Tenant\WeChat\Models\Analytics;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class AdsWeChatMessageMenuD extends Model
{
    protected $connection = 'data_warehouse';
    protected $table = 'ads_wechat_message_menu_d';
}
