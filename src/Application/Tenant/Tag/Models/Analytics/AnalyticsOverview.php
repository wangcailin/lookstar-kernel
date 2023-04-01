<?php

namespace LookstarKernel\Application\Tenant\Tag\Models\Analytics;

use LookstarKernel\Application\Tenant\Analytics\Models\OdsEvents;
use Illuminate\Support\Facades\DB;

class AnalyticsOverview extends OdsEvents
{

    public static function wordCloud()
    {
        $tenantId = tenant()->getTenantKey();
        $data = DB::connection('data_warehouse')->select("SELECT
	dim.`name`,
	dwd.total AS `value` 
FROM
	(
	SELECT
		tag_id,
		COUNT( 1 ) AS total 
	FROM
		(
		SELECT
			distinct_id,
			split ( lookstar_tag, ',' ) AS lookstar_tag_arr 
		FROM
			ods_events 
		WHERE
            lookstar_tenant_id = '{$tenantId}'
			AND lookstar_tag != '' 
			AND regexp_replace ( lookstar_tag, ',', '' ) REGEXP '^[0-9]*$' 
		)
		CROSS JOIN UNNEST ( lookstar_tag_arr ) AS temp_table ( tag_id ) 
	GROUP BY
		tag_id 
	ORDER BY
		total DESC 
		LIMIT 50 
	) AS dwd
	JOIN dim_tenant_tag AS dim ON dim.id = dwd.tag_id 
ORDER BY
	total DESC");
        return $data;
    }
}
