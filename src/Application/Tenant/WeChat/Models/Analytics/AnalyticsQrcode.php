<?php

namespace LookstarKernel\Application\Tenant\WeChat\Models\Analytics;

use LookstarKernel\Application\Tenant\Analytics\Models\OdsEvents;
use Illuminate\Support\Facades\DB;

class AnalyticsQrcode extends OdsEvents
{
    public static function overview($appid, $sceneStr, $time = null)
    {
        $where = [
            'event' => '$MPMessage',
            'properties_mp_msg_type' => 'event',
            'properties_mp_event_key' => $sceneStr,
            'properties_mp_appid' => $appid,
        ];

        if ($time && is_array($time)) {
            $where[] = ['create_time', '>=', $time[0]];
            $where[] = ['create_time', '<=', $time[1]];
        }

        $select = [
            DB::raw('COUNT(DISTINCT mp_openid) AS uv'),
            DB::raw('COUNT(1) AS pv'),
            DB::raw('COUNT(DISTINCT IF(properties_mp_event = "subscribe",mp_openid,NULL)) AS subscribe')
        ];
        return self::where($where)
            ->select($select)
            ->groupBy('lookstar_tenant_id', 'properties_mp_appid', 'properties_mp_event_key')
            ->first();
    }
}
