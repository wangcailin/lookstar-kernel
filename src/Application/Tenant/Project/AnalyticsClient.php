<?php

namespace LookstarKernel\Application\Tenant\Project;


use LookstarKernel\Application\Tenant\Project\Models\Analytics\AnalyticsOverview;
use Composer\Http\BaseController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnalyticsClient extends BaseController
{
    public function campaignRatio(Request $request)
    {
        $input = $request->validate([
            'app_id' => ['required'],
            'event' => ['required', Rule::in(['$pageview', 'user_register_success'])],
        ]);
        $data = AnalyticsOverview::campaignRatio($input['app_id'], $input['event']);
        return $this->success($data);
    }
}
