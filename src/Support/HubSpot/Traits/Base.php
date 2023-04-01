<?php

namespace LookstarKernel\Support\HubSpot\Traits;

trait Base
{
    public static function getClientId()
    {
        return '8816418a-fa14-4181-9687-d48fdc74f641';
    }

    public static function getClientSecret()
    {
        return '2552d3a7-a886-4e2e-a659-ae9016ba4255';
    }

    public static function getRedirectUri()
    {
        return 'https://app.lookstar.com.cn/tenant/hubspot/oauth-callback';
    }
}
