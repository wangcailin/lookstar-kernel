<?php

namespace LookstarKernel\Application\Tenant\Project\MicroBook;

use LookstarKernel\Application\Tenant\Project\MicroBook\Models\AnalyticsD;
use LookstarKernel\Application\Tenant\Project\MicroBook\Models\AnalyticsOverview;
use LookstarKernel\Application\Tenant\Project\MicroBook\Models\AnalyticsTop;
use Composer\Http\BaseController;
use Illuminate\Http\Request;

class AnalyticsClient extends BaseController
{
    public function overview(Request $request)
    {
        $appId = $request->input('app_id');
        $data = AnalyticsOverview::overview($appId);
        return $this->success($data);
    }

    public function timeline(Request $request)
    {
        $request->validate([
            'app_id' => 'required',
            'star_time' => 'required',
            'end_time' => 'required',
        ]);
        $input = $request->all();
        $where = [
            'lookstar_app_id' => $input['app_id'],
            'type' => $input['type'],
        ];
        $data = AnalyticsD::where($where)->where('dt', '>=', $input['star_time'])->where('dt', '<=', $input['end_time'])->orderBy('dt', 'ASC')->get();
        return $this->success($data);
    }

    public function top(Request $request)
    {
        $validateData = $request->validate([
            'app_id' => ['required'],
            'type' => ['required'],
        ]);

        $data = AnalyticsOverview::top($validateData['app_id'], $validateData['type']);
        return $this->success($data);
    }
}
