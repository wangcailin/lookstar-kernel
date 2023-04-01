<?php

namespace LookstarKernel\Application\Tenant\Project\Models\Analytics;

use LookstarKernel\Application\Tenant\Analytics\Models\OdsEvents;
use Illuminate\Support\Facades\DB;

class AnalyticsOverview extends OdsEvents
{

    public static function campaignRatio($id, $event)
    {
        $where = [
            'lookstar_app_id' => $id,
            'event' => $event,
            ['properties_utm_campaign', '!=', '']
        ];
        $select = [
            DB::raw('properties_utm_campaign AS name'),
            DB::raw('COUNT(1) AS value')
        ];
        $data = self::where($where)->select($select)->groupBy('properties_utm_campaign')->orderBy('value', 'DESC')->get();
        return $data;
    }
}
