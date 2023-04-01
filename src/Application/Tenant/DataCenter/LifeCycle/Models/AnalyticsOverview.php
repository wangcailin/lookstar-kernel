<?php

namespace LookstarKernel\Application\Tenant\DataCenter\LifeCycle\Models;

use LookstarKernel\Application\Tenant\Analytics\Models\OdsEvents;
use LookstarKernel\Application\Tenant\Contacts\Models\Analytics\DimContacts;
use LookstarKernel\Application\Tenant\DataCenter\LifeCycle\Models\AdsUserLifeCycleOverview;
use LookstarKernel\Application\Tenant\WeChat\Models\Analytics\DimWeChatOpenid;
use LookstarKernel\Application\Tenant\WeChat\Models\WeChatOpenid;

class AnalyticsOverview extends OdsEvents
{
    public static function overview()
    {
        $dim = DimWeChatOpenid::select('openid');
        $anonymous = OdsEvents::select('distinct_id')
            ->distinct('distinct_id')
            ->union($dim)
            ->count();

        $subscribe = WeChatOpenid::where('subscribe', 1)->count();

        $tag = OdsEvents::whereNotNull('lookstar_tag')->where('lookstar_tag', '!=', '')->whereRaw('regexp_replace(lookstar_tag,",","") REGEXP "^[0-9]*$"')->distinct('distinct_id')->count();

        $register = DimContacts::count();

        $data = [
            'subscribe' => [
                'total' => $subscribe,
            ],
            'tag' =>
            [
                'total' => $tag,
            ],
            'register' =>
            [
                'total' => $register,
            ],
            'anonymous' =>
            [
                'total' => $anonymous,
            ],
        ];

        $overview =  AdsUserLifeCycleOverview::get();
        if ($overview) {
            foreach ($overview as $key => $value) {
                $data[$value['type']]['new_cnt_yesterday'] = $value['new_cnt_yesterday'];
                $data[$value['type']]['dec_cnt_yesterday'] = $value['dec_cnt_yesterday'];
            }
        }

        return $data;
    }
}
