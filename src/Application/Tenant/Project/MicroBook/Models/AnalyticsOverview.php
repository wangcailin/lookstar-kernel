<?php

namespace LookstarKernel\Application\Tenant\Project\MicroBook\Models;

use LookstarKernel\Application\Tenant\Analytics\Models\OdsEvents;
use Illuminate\Support\Facades\DB;

class AnalyticsOverview extends OdsEvents
{
    public static function overview($id)
    {
        $ods =  self::where([
            'lookstar_app_name' => 'microbook',
            'lookstar_app_id' => $id,

        ])
            ->select(DB::raw('
        COUNT(DISTINCT IF(event = "$pageview" AND properties_url_path = "/microbook/", distinct_id,NULL)) AS pageview_uv
        ,COUNT(IF(event = "$pageview" AND properties_url_path = "/microbook/", distinct_id,NULL)) AS pageview_pv
        ,COUNT(DISTINCT IF(event = "user_preview", distinct_id,NULL)) AS preview_uv
        ,COUNT(IF(event = "user_preview", distinct_id,NULL)) AS preview_pv
        ,COUNT(DISTINCT IF(event = "$pageview" AND properties_utm_medium = "mp" AND properties_utm_campaign = "share", distinct_id,NULL)) AS share_pv
        ,COUNT(DISTINCT IF(event = "$pageview" AND properties_utm_medium = "mp" AND properties_utm_campaign = "share", properties_utm_source,NULL)) AS share_uv
        '))
            ->first();
        return $ods;
    }

    public static function top($id, $type)
    {
        $totalSql = '';
        if ($type == 'uv') {
            $totalSql = 'COUNT(DISTINCT distinct_id)';
        } else if ($type == 'pv') {
            $totalSql = 'COUNT(1)';
        }

        $ods =  self::where([
            'lookstar_app_name' => 'microbook',
            'lookstar_app_id' => $id,
            'event' => 'user_preview'
        ])
            ->select(DB::raw('properties_item_key as title,' . $totalSql . ' AS total'))
            ->whereNotNull('properties_item_key')
            ->groupBy('properties_item_key')
            ->orderBy('total', 'DESC')
            ->limit(10)
            ->get();
        return $ods;
    }
}
