<?php

namespace LookstarKernel\Application\Tenant\WeChat\Models\Analytics;

use LookstarKernel\Application\Tenant\Analytics\Models\OdsEvents;
use Illuminate\Support\Facades\DB;

class AnalyticsMenu extends OdsEvents
{
    public static function overview($propertiesMpAppid)
    {
        $ads = AdsWeChatMessageMenuOverview::firstWhere(['properties_mp_appid' => $propertiesMpAppid]);
        $ods =  self::where([
            'event' => '$MPMessage',
            'properties_mp_msg_type' => 'event',
            'properties_mp_appid' => $propertiesMpAppid,
        ])
            ->whereIn('properties_mp_event', ['CLICK', 'VIEW', 'view_miniprogram'])
            ->select(DB::raw('lookstar_tenant_id
        ,properties_mp_appid
        ,COUNT(DISTINCT mp_openid) AS uv
        ,COUNT(1) AS pv'))
            ->groupBy('lookstar_tenant_id', 'properties_mp_appid')
            ->first();
        if ($ads && $ods) {
            $ads['uv'] = $ods['uv'];
            $ads['pv'] = $ods['pv'];
        }
        return $ads;
    }
}
