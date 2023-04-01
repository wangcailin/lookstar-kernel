<?php

namespace LookstarKernel\Application\Tenant\WeChat;

use LookstarKernel\Application\Tenant\WeChat\Job\SyncOpenid;
use Composer\Http\Controller;
use LookstarKernel\Application\Tenant\WeChat\Models\WeChatOpenid;
use Spatie\QueryBuilder\AllowedFilter;

class OpenidClient extends Controller
{
    public function __construct(WeChatOpenid $weChatOpenid)
    {
        $this->model = $weChatOpenid;

        $this->allowedFilters = [
            AllowedFilter::exact('openid'),
            AllowedFilter::exact('unionid'),
            AllowedFilter::exact('appid'),
            'nickname',
        ];
    }

    public function sync()
    {
        SyncOpenid::dispatch()->onQueue('wechat');
        return $this->success();
    }
}
