<?php

namespace LookstarKernel\Application\Tenant\Contacts;

use LookstarKernel\Application\Tenant\Contacts\Models\Contacts;
use LookstarKernel\Application\Tenant\Contacts\Models\Analytics\AnalyticsOverview;
use Composer\Http\BaseController;
use Illuminate\Http\Request;

class AnalyticsClient extends BaseController
{
    public function getOpenID($id)
    {
        $user = Contacts::findOrFail($id);
        $list = AnalyticsOverview::queryWeChatOpenId($user['phone']);
        return $this->success($list);
    }

    public function getTagTop($id)
    {
        $user = Contacts::findOrFail($id);
        $list = AnalyticsOverview::queryTagTop50($user['phone']);
        return $this->success($list);
    }

    public function getTimeline($id, Request $request)
    {
        $page = $request->input('page', 1);
        $event = $request->input('event', 'all');
        $user = Contacts::findOrFail($id);
        $list = AnalyticsOverview::queryTimeline($user['phone'], $event, $page);
        return $this->success($list);
    }
}
