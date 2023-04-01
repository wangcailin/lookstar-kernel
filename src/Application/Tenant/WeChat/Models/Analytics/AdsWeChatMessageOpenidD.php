<?php

namespace LookstarKernel\Application\Tenant\WeChat\Models\Analytics;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class AdsWeChatMessageOpenidD extends Model
{
    protected $connection = 'data_warehouse';
    protected $table = 'ads_wechat_message_openid_d';
}
