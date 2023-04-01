<?php

namespace LookstarKernel\Application\Central\Tenant\WeChat;

use Composer\Application\WeChat\Authorizer\Client;
use LookstarKernel\Application\Tenant\WeChat\Models\WeChatAuthorizer as Authorizer;
use Composer\Application\WeChat\WeChat;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;

class AuthorizerClient extends Client
{

    public function __construct(Authorizer $authorizer)
    {
        $this->model = $authorizer;
        $this->allowedFilters = [
            AllowedFilter::exact('type'),
            AllowedFilter::exact('app_type'),
        ];
    }

    /**
     * 获取授权URL
     */
    public function view(Request $request)
    {
        $input = $request->validate([
            'tenant' => 'required',
            'auth_user_id' => 'required'
        ]);
        $appUrl = config('app.url');
        $redirectUrl = 'https://' . $appUrl . '/tenant/wechat/authorizer/callback?tenant=' . $input['tenant'] . '&auth_user_id=' . $input['auth_user_id'];
        return view('wechat.authorizer.url', ['redirect_url' => urlencode($redirectUrl), 'url' => $appUrl]);
    }
}
