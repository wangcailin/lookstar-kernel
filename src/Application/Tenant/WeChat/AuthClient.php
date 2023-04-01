<?php

namespace LookstarKernel\Application\Tenant\WeChat;

use LookstarKernel\Application\Tenant\WeChat\Models\WeChatOpenid;
use Illuminate\Http\Request;
use Composer\Application\WeChat\WeChat;
use Composer\Application\WeChat\AuthClient as Client;

class AuthClient extends Client
{
    public function __construct(WeChatOpenid $weChatOpenid)
    {
        $this->weChatOpenidModel = $weChatOpenid;
    }

    public function oauth(WeChat $weChat, Request $request)
    {
        $appid = $request->input('appid');
        $tenant = $request->input('tenant');
        $redirectUrl = $request->input('redirect_url');
        $code = $request->input('code');
        $app = $weChat->getOfficialAccount($appid);
        $oauth = $app->getOAuth();

        if (!$code) {
            $oauthRedirectUrl = $oauth->scopes(['snsapi_userinfo'])
                ->redirect($request->getScheme() . '://' . $request->getHost() . '/tenant/wechat/auth/oauth?tenant=' . $tenant . '&appid=' . $appid . '&redirect_url=' . urlencode($redirectUrl));
            return \redirect($oauthRedirectUrl);
        } else {
            $user = $oauth->scopes(['snsapi_userinfo'])->userFromCode($code)->getRaw();
            $link = '?';
            if (strpos($redirectUrl, '?')) {
                $link = '&';
            }
            $this->oauthAfter($appid, $user);
            $query = http_build_query($user);
            return \redirect($redirectUrl . $link . $query);
        }
    }

    public function auth(WeChat $weChat, Request $request)
    {
        $validateData = $request->validate([
            'appid' => 'required',
            'code' => 'required',
        ]);
        $app = $weChat->getMiniProgram($validateData['appid']);
        $openPlatformApp = $weChat->getOpenPlatform();
        $api = $app->getClient();

        $response = $api->get('/sns/component/jscode2session', [
            'appid' => $validateData['appid'],
            'js_code' => $validateData['code'],
            'grant_type' => 'authorization_code',
            'component_appid' => $openPlatformApp->getAccount()->getAppId(),
            'component_access_token' => $openPlatformApp->getComponentAccessToken()->getToken(),
        ]);

        $user = $response->toArray();
        $userInfo = $this->authAfter($validateData['appid'], $user);

        return $this->success($userInfo);
    }

    public function syncUser(Request $request)
    {
        $userBase = $request->validate([
            'appid' => 'required',
            'openid' => 'required',
        ]);

        $userData = $request->only(['nickname', 'avatar', 'unionid', 'subscribe', 'subscribe_time', 'subscribe_scene', 'remark', 'qr_scene', 'qr_scene_str']);

        WeChatOpenid::updateOrCreate($userBase, $userData);

        return $this->success();
    }
}
