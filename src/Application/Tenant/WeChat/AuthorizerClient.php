<?php

namespace LookstarKernel\Application\Tenant\WeChat;

use Composer\Application\WeChat\Authorizer\Client;
use LookstarKernel\Application\Tenant\WeChat\Models\WeChatAuthorizer as Authorizer;
use Composer\Application\WeChat\WeChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Spatie\QueryBuilder\AllowedFilter;

class AuthorizerClient extends Client
{
    public ?WeChat $weChat;

    public function __construct(WeChat $weChat, Authorizer $authorizer)
    {
        $this->model = $authorizer;
        $this->allowedFilters = [
            AllowedFilter::exact('type'),
            AllowedFilter::exact('app_type'),
        ];
        $this->weChat = $weChat;
    }

    /**
     * Select选择器
     */
    public function getSelectList()
    {
        $this->buildFilter();
        $this->list = $this->model->select('appid as value', 'nick_name as label')->get();
        return $this->success($this->list);
    }

    public function afterCallback($authorization)
    {
        Redis::set('tenant:wechat:authorizer:' . $authorization['authorization_info']['authorizer_appid'], tenant()->id);
    }
}
