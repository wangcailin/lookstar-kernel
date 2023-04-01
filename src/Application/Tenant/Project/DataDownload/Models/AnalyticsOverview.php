<?php

namespace LookstarKernel\Application\Tenant\Project\DataDownload\Models;

use LookstarKernel\Application\Tenant\Analytics\Models\OdsEvents;
use Illuminate\Support\Facades\DB;

class AnalyticsOverview extends OdsEvents
{
    public static function overview($id)
    {
        $ods =  self::where([
            'lookstar_app_name' => 'data_download',
            'lookstar_app_id' => $id
        ])
            ->select(DB::raw('
        COUNT(DISTINCT IF(event = "$pageview" AND properties_url_path = "/data-download/", distinct_id,NULL)) AS pageview_uv
        ,COUNT(IF(event = "$pageview" AND properties_url_path = "/data-download/", distinct_id,NULL)) AS pageview_pv
        ,COUNT(DISTINCT IF(event = "user_download", distinct_id,NULL)) AS download_uv
        ,COUNT(IF(event = "user_download", distinct_id,NULL)) AS download_pv
        ,COUNT(DISTINCT IF(event = "user_preview", distinct_id,NULL)) AS preview_uv
        ,COUNT(IF(event = "user_preview", distinct_id,NULL)) AS preview_pv
        '))
            ->first();
        return $ods;
    }

    public static function top($id, $type)
    {
        $event = [
            'preview' => 'user_preview',
            'download' => 'user_download',
        ];

        $list = DB::connection('data_warehouse')->select('
SELECT
	cast(item_id AS INT) AS item_id,
	count( 1 ) AS total
FROM
	(
	SELECT
		split ( properties_item_key, "," ) AS properties_item_key,
		distinct_id 
	FROM
		`ods_events` 
	WHERE
		`lookstar_app_name` = "data_download" 
		AND `lookstar_app_id` = ' . $id . ' 
		AND `event` = "' . $event[$type] . '" 
		AND `properties_item_key` IS NOT NULL 
		AND `lookstar_tenant_id` = "' . tenant()->getTenantKey() . '" 
	)
	CROSS JOIN UNNEST ( properties_item_key ) AS temp_table ( item_id ) 
WHERE item_id != 0
GROUP BY
	item_id 
ORDER BY
	total DESC
	LIMIT 10
    ');
        if ($list) {
            $list = json_decode(json_encode($list, true), true);
            foreach ($list as $key => $value) {
                $list[$key]['title'] = DataDownload::where('id', $value['item_id'])->value('title');
            }
        }
        return $list;
    }
}
