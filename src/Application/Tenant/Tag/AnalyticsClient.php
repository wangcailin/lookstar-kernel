<?php

namespace LookstarKernel\Application\Tenant\Tag;


use LookstarKernel\Application\Tenant\Tag\Models\Analytics\AnalyticsOverview;
use Composer\Http\BaseController;
use Illuminate\Http\Request;

class AnalyticsClient extends BaseController
{
    public function wordCloud()
    {
        $data = AnalyticsOverview::wordCloud();
        return $this->success($data);
    }
}
