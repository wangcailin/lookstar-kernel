<?php

namespace LookstarKernel\Application\Tenant\DataCenter\LifeCycle;

use LookstarKernel\Application\Tenant\DataCenter\LifeCycle\Models\AnalyticsOverview;
use LookstarKernel\Application\Tenant\DataCenter\LifeCycle\Models\AdsUserLifeCycleD;
use Composer\Http\BaseController;
use Illuminate\Http\Request;

class Client extends BaseController
{
    public function overview()
    {
        $data = AnalyticsOverview::overview();
        return $this->success($data);
    }

    public function timeline(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'star_time' => 'required',
            'end_time' => 'required',
        ]);
        $input = $request->all();
        $where = [
            'type' => $input['type'],
        ];
        $data = AdsUserLifeCycleD::where($where)->where('dt', '>=', $input['star_time'])->where('dt', '<=', $input['end_time'])->orderBy('dt', 'ASC')->get();
        return $this->success($data);
    }
}
