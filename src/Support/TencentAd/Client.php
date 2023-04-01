<?php

namespace LookstarKernel\Support\TencentAd;

use TencentAds\TencentAds;

class Client
{
    public static $tads;
    public static $ACCESS_TOKEN = 'e91a2fcf10d4f7737242f6b132520388';
    public static $ACCOUNT_ID   = '25878882';

    public static function init()
    {
        $tads = TencentAds::init([
            'access_token' => static::$ACCESS_TOKEN,
            'is_debug'     => false,
        ]);
        $tads->useProduction(); // 默认访问沙箱环境，如访问正式环境，请切换为$tads->useProduction()
        static::$tads = $tads;

        return $tads;
    }
}
