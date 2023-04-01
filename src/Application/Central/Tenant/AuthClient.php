<?php

namespace LookstarKernel\Application\Central\Tenant;

use LookstarKernel\Application\Tenant\Auth\Models\User;
use LookstarKernel\Application\Central\Tenant\Models\Relations\TenantUserRelation;
use Composer\Exceptions\ApiErrorCode;
use Composer\Exceptions\ApiException;
use Composer\Http\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Composer\Support\Crypt\AES;
use Composer\Support\Captcha\Client as CaptchaClient;

class AuthClient extends BaseController
{
    /**
     * 登录失败次数
     */
    public $loginfailCount = 10;

    public $user;
    public $token;


    /**
     * 登录
     */
    public function login(Request $request)
    {
        $input = $request->all();
        return $this->handleLogin($input);
    }

    /**
     * 获取验证码
     */
    public function getCaptcha(Request $request)
    {
        $input = $request->only(['email', 'phone', 'action', 'crypt_key']);
        if (!empty($input['email'])) {
            $input['email'] = AES::decodeRsa($input['crypt_key'], $input['email']);
            CaptchaClient::sendEmailCode($input['email'], $input['action']);
        } elseif (!empty($input['phone'])) {
            $input['phone'] = AES::decodeRsa($input['crypt_key'], $input['phone']);
            CaptchaClient::sendSmsCode($input['phone'], $input['action']);
        }
    }

    private function handleLogin($input)
    {
        $where = $this->getLoginWhere($input);
        if ($where) {
            $this->user = User::firstWhere($where);
            if (!$this->user) {
                if ($input['type'] == 'account') {
                    $email = $phone = AES::encode($input['username']);
                    $this->user = User::where('phone', $phone)->orWhere('email', $email)->first();
                }
            }
        }

        if ($this->user) {
            return $this->checkTenant($input);
        } else {
            throw new ApiException('用户不存在', ApiErrorCode::ACCOUNT_EMPTY_ERROR);
        }
    }

    private function getLoginWhere($input)
    {
        $where = [];

        switch ($input['type']) {
            case 'account':
                $where['username'] = $input['username'];
                break;
            case 'mobile':
                $where['phone'] = AES::encode(AES::decodeRsa($input['crypt_key'], $input['phone']));
                break;
            case 'mail':
                $where['email'] = AES::encode(AES::decodeRsa($input['crypt_key'], $input['email']));
                break;
        }
        return $where;
    }

    private function checkTenant($input)
    {
        $tenantId = $this->user['tenant_id'];
        $result = Http::withHeaders([
            'X-Tenant' => $tenantId
        ])->post('https://' . config('app.url') . '/tenant/auth/login', $input);
        return $this->success($result->json());
    }
}
