<?php

namespace LookstarKernel\Application\Tenant\Contacts\Models\Analytics;

use LookstarKernel\Application\Tenant\Analytics\Models\OdsEvents;
use LookstarKernel\Application\Tenant\Project\DataDownload\Models\DataDownload;
use LookstarKernel\Application\Tenant\Project\Models\Project;
use LookstarKernel\Application\Tenant\Tag\Models\Tag;
use Illuminate\Support\Facades\DB;

class AnalyticsOverview extends OdsEvents
{
    public static function queryWeChatOpenId($mobile)
    {
        $tenantId = tenant()->getTenantKey();
        $list = DB::connection('data_warehouse')->select('
SELECT
	ads.lookstar_tenant_id,
	ads.openid,
	dim_openid.subscribe,
	dim_openid.subscribe_time,
	dim_openid.subscribe_scene,
	dim_openid.nickname,
	dim_openid.avatar,
	dim_authorizer.nick_name AS authorizer_nickname,
	dim_authorizer.head_img AS authorizer_avatar 
FROM
	(
	SELECT
		lookstar_tenant_id,
	    distinct_id AS openid
	FROM
		ods_events 
	WHERE
		ods_events.mobile = "' . $mobile . '"
		AND lookstar_tenant_id = "' . $tenantId . '"
	GROUP BY
		lookstar_tenant_id,
		openid
	) AS ads
	INNER JOIN dim_tenant_wechat_openid AS dim_openid ON ( ads.lookstar_tenant_id = dim_openid.tenant_id AND ads.openid = dim_openid.openid )
	INNER JOIN dim_tenant_wechat_authorizer AS dim_authorizer ON ( ads.lookstar_tenant_id = dim_authorizer.tenant_id AND dim_openid.appid = dim_authorizer.appid );
    ');

        return $list;
    }

    public static function queryTagTop50($mobile)
    {
        $tenantId = tenant()->getTenantKey();
        $list = DB::connection('data_warehouse')->select('
SELECT
	dim.`name`,
	dwd.total AS value
FROM
	(
	SELECT
		tag_id,
		COUNT( 1 ) AS total 
	FROM
		(
		SELECT
			distinct_id,
			split ( lookstar_tag, "," ) AS lookstar_tag_arr 
		FROM
			ods_events 
		WHERE
			lookstar_tenant_id = "' . $tenantId . '" 
			AND lookstar_tag != "" 
			AND regexp_replace ( lookstar_tag, ",", "" ) REGEXP "^[0-9]*$" 
			AND distinct_id IN ( SELECT distinct_id FROM ods_events WHERE lookstar_tenant_id = "' . $tenantId . '" AND mobile = "' . $mobile . '" GROUP BY distinct_id )
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
	total DESC;
    ');

        return $list;
    }

    public static function queryTimeline($mobile, $event, $page)
    {
        $limit = 20;
        $tenantId = tenant()->getTenantKey();
        $evetWhere = ['"$MPMessage"', '"user_register_success"', '"user_search"', '"user_download"', '"user_preview"'];
        if ($event != 'all') {
            $evetWhere = ['"' . $event . '"'];
        }
        $list = DB::connection('data_warehouse')->select('
SELECT
	ads.*,
	dim_openid.nickname,
	dim_openid.avatar,
	dim_qrcode.`name` AS mp_qrcode_name,
    dim_menu.`name` as mp_menu_name,
    dim_authorizer.nick_name as mp_authorizer_nickname
FROM
	(
	SELECT
		lookstar_tenant_id,
		distinct_id,
		`event`,
		lookstar_app_id,
		lookstar_app_name,
		lookstar_tag,
		properties_title,
		properties_item_key,
		properties_mp_appid,
		properties_mp_event,
		properties_mp_content,
		properties_mp_msg_type,
		properties_mp_event_key,
        properties_country,
        properties_province,
		properties_city,
		properties_user_agent_model,
		create_time 
	FROM
		ods_events 
	WHERE
		lookstar_tenant_id = "' . $tenantId . '" 
		AND distinct_id IN ( SELECT distinct_id FROM ods_events WHERE lookstar_tenant_id = "' . $tenantId . '"  AND mobile = "' . $mobile . '" GROUP BY distinct_id )
		AND `event` IN ( ' . implode(',', $evetWhere) . ' ) 
		AND properties_mp_event NOT IN ( "LOCATION", "TEMPLATESENDJOBFINISH" ) 
	ORDER BY
		create_time DESC 
		LIMIT ' . ($page - 1) * $limit . ',
		' . $limit . ' 
	) AS ads
	LEFT JOIN dim_tenant_wechat_openid AS dim_openid ON ( ads.lookstar_tenant_id = dim_openid.tenant_id AND ads.properties_mp_appid = dim_openid.appid AND ads.distinct_id = dim_openid.openid )
	LEFT JOIN dim_tenant_wechat_qrcode AS dim_qrcode ON ( ads.lookstar_tenant_id = dim_qrcode.tenant_id AND ads.properties_mp_event_key = dim_qrcode.scene_str ) 
    LEFT JOIN dim_tenant_wechat_menu AS dim_menu ON ( ads.lookstar_tenant_id = dim_menu.tenant_id AND ads.properties_mp_appid = dim_menu.appid AND ads.properties_mp_event_key = dim_menu.`value` )
    LEFT JOIN dim_tenant_wechat_authorizer AS dim_authorizer ON ( ads.lookstar_tenant_id = dim_authorizer.tenant_id AND ads.properties_mp_appid = dim_authorizer.appid )
ORDER BY
	ads.create_time DESC
    ');
        $list = json_decode(json_encode($list, true), true);
        foreach ($list as $key => $value) {
            if ($value['lookstar_tag']) {
                $tagIds = explode(',', $value['lookstar_tag']);
                $list[$key]['lookstar_tag'] = Tag::whereIn('id', $tagIds)->pluck('name');
            }
            if ($value['lookstar_app_id'] && $value['lookstar_app_name']) { // 观星域内数据
                $list[$key]['lookstar_app_title'] = Project::where('id', $value['lookstar_app_id'])->value('title');
                if ($value['lookstar_app_name'] == 'data_download') {
                    if ($value['event'] == 'user_download' && is_numeric(str_replace(',', '', $value['properties_item_key']))) {
                        $propertiesItemKey = explode(',', $value['properties_item_key']);
                        $list[$key]['properties_item_key'] = DataDownload::whereIn('id', $propertiesItemKey)->pluck('title');
                    } elseif ($value['event'] == 'user_preview') {
                        $list[$key]['properties_item_key'] = DataDownload::where('id', $value['properties_item_key'])->value('title');
                    }
                }
            } else { // 观星域外数据
                if ($value['event'] == 'user_download') {
                    $list[$key]['properties_item_key'] = explode(',', $value['properties_item_key']);
                }
            }
        }

        return $list;
    }
}
