<?php

namespace LookstarKernel\Application\Tenant\WeChat;

use LookstarKernel\Application\Tenant\WeChat\Models\Analytics\AdsWeChatMessageMenuD;
use LookstarKernel\Application\Tenant\WeChat\Models\Analytics\AdsWeChatMessageOpenidD;
use LookstarKernel\Application\Tenant\WeChat\Models\Analytics\AnalyticsMenu;
use LookstarKernel\Application\Tenant\WeChat\Models\Analytics\AdsWeChatMessageOpenidOverview;
use LookstarKernel\Application\Tenant\WeChat\Models\Analytics\AnalyticsQrcode;
use LookstarKernel\Application\Tenant\WeChat\Models\Analytics\DimWeChatOpenid;
use Composer\Http\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AnalyticsClient extends BaseController
{
    public function menuOverview(Request $request)
    {
        $validateData = $request->validate([
            'appid' => 'required',
        ]);
        $row = AnalyticsMenu::overview($validateData['appid']);
        return $this->success($row);
    }

    public function menuTimeline(Request $request)
    {
        $validateData = $request->validate([
            'appid' => 'required',
            'type' => 'required',
            'star_time' => 'required',
            'end_time' => 'required',
        ]);
        $where = [
            'properties_mp_appid' => $validateData['appid'],
            ['dt', '>=', $validateData['star_time']],
            ['dt', '<=', $validateData['end_time']]
        ];
        $query = AdsWeChatMessageMenuD::where($where)->select('name', 'dt');
        switch ($validateData['type']) {
            case 'pv':
                $query->addSelect('pv as value');
                break;
            case 'uv':
                $query->addSelect('uv as value');
                break;
            case 'rate':
                $query->addSelect(DB::raw('CONVERT(pv/uv,DECIMAL(15,2)) as value'));
                break;
        }
        return $this->success($query->orderBy('dt', 'ASC')->get());
    }

    public function openidTimeline(Request $request)
    {
        $validateData = $request->validate([
            'appid' => 'required',
            'type' => Rule::in(['subscribe_cnt', 'unsubscribe_cnt', 'netgain_subscribe_cnt', 'total_subscribe_cnt']),
            'star_time' => 'required',
            'end_time' => 'required',
        ]);
        $where = [
            'properties_mp_appid' => $validateData['appid'],
            ['dt', '>=', $validateData['star_time']],
            ['dt', '<=', $validateData['end_time']]
        ];
        $query = AdsWeChatMessageOpenidD::where($where)->select(['dt', $validateData['type'] . ' AS value']);
        return $this->success($query->orderBy('dt', 'ASC')->get());
    }

    public function qrcodeOverview(Request $request, AnalyticsQrcode $analyticsQrcode)
    {
        $input =  $request->validate([
            'appid' => 'required',
            'scene_str' => 'required',
            'start_time' =>  'required',
            'end_time' =>  'required',
        ]);
        $data = $analyticsQrcode->overview($input['appid'], $input['scene_str'], [$input['start_time'], $input['end_time']]);
        return $this->success($data);
    }

    public function openidOverview(Request $request)
    {
        $validateData = $request->validate([
            'appid' => 'required',
        ]);
        $row = AdsWeChatMessageOpenidOverview::where('properties_mp_appid', $validateData['appid'])->first();
        return $this->success($row);
    }

    public function openidSubscribeScene(Request $request)
    {
        $validateData = $request->validate([
            'appid' => 'required',
        ]);
        $select = [
            'subscribe_scene',
            DB::raw('COUNT( 1 ) AS value')
        ];
        $data = DimWeChatOpenid::where($validateData)->where('subscribe', 1)
            ->select($select)
            ->groupBy('subscribe_scene')
            ->get();
        $enum = DimWeChatOpenid::$subscribeSceneEnum;
        foreach ($data as $key => $value) {
            if (array_key_exists($value['subscribe_scene'], $enum)) {
                $data[$key]['name'] = $enum[$value['subscribe_scene']];
            } else {
                $data[$key]['name'] = '其他';
            }
        }
        return $this->success($data);
    }
}
