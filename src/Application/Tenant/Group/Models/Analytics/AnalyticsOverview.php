<?php

namespace LookstarKernel\Application\Tenant\Group\Models\Analytics;

use LookstarKernel\Application\Tenant\Analytics\Models\OdsEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AnalyticsOverview extends OdsEvents
{
    public function queryTotal($filter)
    {
        $sql = $this->querySql($filter, 'total');
        var_dump($sql);
        die;
        $data = DB::connection('data_warehouse')->selectOne($sql);
        return $data;
    }

    public function saveList($filter, $groupId)
    {
        $this->deleteGroup($groupId);

        $sql = $this->querySql($filter, 'list', $groupId);
        DB::connection('data_warehouse')->select($sql);

        $tenantId = tenant()->getTenantKey();
        $querySql = "WITH ods AS ( SELECT * FROM ads_group_user_info WHERE tenant_id = '{$tenantId}' AND group_id = {$groupId} ) SELECT
a.wechat_cnt,
b.mobile_cnt,
b.email_cnt
FROM
	(
	SELECT
		1 AS id,
		CONCAT( '[', GROUP_CONCAT( JSON_OBJECT( 'appid', appid, 'openid_cnt', openid_cnt )), ']' ) AS wechat_cnt 
	FROM
		( SELECT '' AS wechat_cnt, appid, COUNT( DISTINCT openid ) AS openid_cnt FROM ods WHERE appid IS NOT NULL GROUP BY appid ) 
	) AS a
	FULL OUTER JOIN ( SELECT 1 AS id, COUNT( DISTINCT mobile ) AS mobile_cnt, COUNT( DISTINCT email ) AS email_cnt FROM ods WHERE mobile IS NOT NULL ) AS b ON (
	a.id = b.id)";
        $data = DB::connection('data_warehouse')->selectOne($querySql);

        return $data;
    }

    public function deleteGroup($groupId)
    {
        AdsGroupUserInfo::where('group_id', $groupId)->delete();
    }

    protected function querySql($filter, $type, $groupId = null)
    {
        Validator::make(
            $filter,
            [
                'filter' => ['required', 'array', 'min:1', 'max:10'],
                'filter.*.conditions' => ['required', Rule::in(['AND', 'OR'])],
                'filter.*.filter' => ['required', 'array', 'min:1', 'max:10'],
                'filter.*.filter.*.type' => ['required', Rule::in(['lifecycle', 'tag', 'utm_source'])],
                'filter.*.filter.*.conditions' => ['required', Rule::in(['AND', 'OR'])],
                'filter.*.filter.*.filter' => ['required', Rule::in(['in', 'any', 'not', 'not_in', 'is'])],
                'filter.*.filter.*.value' => ['exclude_unless:filter.*.filter.*.type,lifecycle', 'required', 'array', 'min:1'],
            ],
        )->validate();

        $tenantId = tenant()->getTenantKey();

        $head = '';
        $footer = '';
        if ($type == 'list') {
            $head = 'INSERT INTO ads_group_user_info (tenant_id, group_id, appid, openid, mobile, email )';
            $footer = " SELECT
    '{$tenantId}' AS tenant_id,
    {$groupId} AS group_id,
    appid,
    openid,
    mobile,
    email 
FROM
    filter_table 
WHERE
    IF ( subscribe = 0, mobile IS NOT NULL, TRUE ) 
    AND
    IF ( appid IS NULL, mobile IS NOT NULL, TRUE )";
        } elseif ($type == 'total') {
            $footer = " SELECT
	a.wechat_cnt,
    b.mobile_cnt,
    b.email_cnt
FROM
	(
	SELECT
		1 AS id,
		CONCAT( '[', GROUP_CONCAT( JSON_OBJECT( 'appid', appid, 'openid_cnt', openid_cnt )), ']' ) AS wechat_cnt 
	FROM
		(
		SELECT
			'' AS wechat_cnt,
			appid,
			COUNT( DISTINCT openid ) AS openid_cnt 
		FROM
			filter_table 
		WHERE
			appid IS NOT NULL 
			AND subscribe = 1 
		GROUP BY
			appid
        ORDER BY
            openid_cnt DESC
		) 
	) AS a
	FULL OUTER JOIN ( SELECT 1 AS id, COUNT( DISTINCT mobile ) AS mobile_cnt, COUNT( DISTINCT email ) AS email_cnt FROM filter_table WHERE mobile IS NOT NULL ) AS b ON (
	a.id = b.id)";
        }
        $sql = $head . " WITH ods AS (
        SELECT
            distinct_id,
            IF( mp_openid = '' OR mp_openid IS NULL, NULL, mp_openid ) AS openid,
            IF( mobile = '' OR mobile IS NULL, NULL, mobile ) AS mobile,
            lookstar_tag,
            properties_utm_source,
            properties_utm_campaign,
            properties_utm_medium,
            properties_utm_term,
            properties_utm_content
        FROM
            ods_events 
        WHERE
            lookstar_tenant_id = '{$tenantId}' 
            AND (
                ( mp_openid != '' AND mp_openid IS NOT NULL ) 
            OR ( mobile != '' AND mobile IS NOT NULL )) 
	),
    ods_openid AS ( SELECT DISTINCT openid FROM ods WHERE openid IS NOT NULL ),
    dim_contacts AS ( SELECT phone AS mobile, email FROM dim_tenant_contacts WHERE tenant_id = '{$tenantId}' ),
	dim_wechat_openid AS ( SELECT openid, subscribe, appid FROM dim_tenant_wechat_openid WHERE tenant_id = '{$tenantId}' ),
    ods_full_openid AS ( SELECT openid FROM dim_wechat_openid UNION SELECT openid FROM ods_openid ),
	register_openid AS ( SELECT openid, mobile FROM ods WHERE mobile IS NOT NULL AND openid IS NOT NULL GROUP BY openid, mobile),
	subscribe_openid AS ( SELECT openid FROM dim_wechat_openid WHERE subscribe = 1 ),
	ods_user_tag_openid AS (
        SELECT
            openid,
            tag_id,
            COUNT( 1 ) AS total_cnt 
        FROM
            (
            SELECT
                openid,
                split ( lookstar_tag, ',' ) AS lookstar_tag_arr 
            FROM
                ods 
            WHERE
                lookstar_tag != '' 
                AND regexp_replace ( lookstar_tag, ',', '' ) REGEXP '^[0-9]*$' 
                AND openid IS NOT NULL 
            )
            CROSS JOIN UNNEST ( lookstar_tag_arr ) AS temp_table ( tag_id ) 
        GROUP BY
            openid,
            tag_id 
	),
    user_tag_openid AS ( SELECT DISTINCT openid AS openid FROM ods_user_tag_openid ),
	user_not_tag_openid AS (
        SELECT
            ods_full_openid.openid 
        FROM
            ods_full_openid
            LEFT JOIN ( SELECT openid FROM user_tag_openid ) AS tag ON ( ods_full_openid.openid = tag.openid ) 
        WHERE
            tag.openid IS NULL 
	),
    filter_table AS ( 
        SELECT
                filter.openid,
                dim.appid,
                dim.subscribe,
                register_openid.mobile,
                dim_contacts.email 
            FROM (";
        $groupSql = '';
        $groupSqlHead = '';
        $groupCount = count($filter['filter']);
        foreach ($filter['filter'] as $key => $value) {
            $groupSql .= 'SELECT * FROM (';
            $filterSql = '';
            $filterSqlHead = '';
            $filterCount = count($value['filter']);
            foreach ($value['filter'] as $k => $v) {
                if ($v['type'] == 'lifecycle') {
                    $filterSql .= $this->getOpenIDLifecycleSql($v['value']);
                } elseif ($v['type'] == 'tag') {
                    $filterSql .= $this->getOpenIDTagSql($v);
                } elseif (strpos($v['type'], 'utm_') !==  false) {
                    $filterSql .= $this->getUtmSql($v);
                }
                if ($k > 0) {
                    $filterSql .= ')';
                    $filterSqlHead .= '(';
                }
                $filterSql .= $this->getConditionSql($value['filter'], $filterCount, $k);
            }
            $filterSql = ($filterSqlHead . $filterSql);

            $groupSql .= $filterSql;
            $groupSql .= ')';
            if ($key > 0) {
                $groupSql .= ')';
                $groupSqlHead .= '(';
            }
            $groupSql .= $this->getConditionSql($filter['filter'], $groupCount, $key);
        }
        $groupSql = ($groupSqlHead . $groupSql);

        $sql .= $groupSql . ") AS filter LEFT JOIN dim_wechat_openid AS dim ON ( filter.openid = dim.openid )
		LEFT JOIN register_openid ON ( filter.openid = register_openid.openid )
	LEFT JOIN dim_contacts ON ( register_openid.mobile = dim_contacts.mobile ))";

        $sql .= $footer;
        return $sql;
    }

    protected function getOpenIDLifecycleSql($value)
    {
        $baseSql = 'SELECT openid FROM ';
        $sql = [];
        foreach ($value as $key => $lifecycle) {
            if ($lifecycle == 'anonymous') {
                $sql[] = $baseSql  . 'ods_full_openid';
            } elseif ($lifecycle == 'subscribe') {
                $sql[] = $baseSql  . 'subscribe_openid';
            } elseif ($lifecycle == 'register') {
                $sql[] = $baseSql  . 'register_openid';
            } elseif ($lifecycle == 'tag') {
                $sql[] = $baseSql  . 'user_tag_openid';
            }
        }
        $sql = implode(' UNION ', $sql);
        return $sql;
    }

    protected function getOpenIDTagSql($filter)
    {
        $sql = '';
        $tagSql = 'SELECT DISTINCT openid FROM ods_user_tag_openid';
        $notTagSql = 'SELECT openid FROM user_not_tag_openid';
        if ($filter['filter'] == 'in') {
            $sql .= ($tagSql . ' WHERE tag_id IN ( ' . implode(',', $filter['value']) . ' )');
        } elseif ($filter['filter'] == 'not_in') {
            $sql .= ($tagSql . ' WHERE tag_id NOT IN ( ' . implode(',', $filter['value']) . ' )');
        } elseif ($filter['filter'] == 'any') {
            $sql .= $tagSql;
        } elseif ($filter['filter'] == 'not') {
            $sql .= $notTagSql;
        }
        return $sql;
    }

    protected function getConditionSql($filter, $count, $key)
    {
        $sql = '';
        $lastKey = $key + 1;
        if ($count > $lastKey) {
            if ($filter[$lastKey]['conditions'] == 'OR') {
                $sql .= ' UNION ';
            } elseif ($filter[$lastKey]['conditions'] == 'AND') {
                $sql .= ' INTERSECT ';
            }
        }
        return $sql;
    }

    protected function getUtmSql($filter)
    {
        $sql = 'SELECT DISTINCT openid FROM ods WHERE ';
        $key = 'properties_' . $filter['type'];
        $sql .= ($key . ' = "' . $filter['value'] . '"');
        return $sql;
    }
}
