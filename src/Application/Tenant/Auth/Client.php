<?php

namespace LookstarKernel\Application\Tenant\Auth;

use LookstarKernel\Application\Tenant\Auth\Models\User;
use Composer\Support\Auth\Client as AuthClient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Composer\Support\Redis\CaptchaClient;
use Composer\Support\Aliyun\SmsClient;

class Client extends AuthClient
{
    public function __construct(User $user)
    {
        $this->model = $user;
    }

    protected function respondWithToken($token)
    {
        return $this->success([
            'access_token' => $token,
            'X-Tenant' => tenant()->id
        ]);
    }

    protected function getAccessToken()
    {
        $this->token = $this->user->createToken('tenant')->accessToken;
    }

    public function getAdmin()
    {
        $adminUser = $this->model::firstWhere('is_admin', 1);
        $adminUser->append(['mask_phone', 'mask_email']);
        return $this->success($adminUser);
    }

    public function getAdminCaptcha(Request $request)
    {
        $adminUser = $this->model::firstWhere('is_admin', 1);

        $input = $request->validate([
            'action' => ['required', Rule::in(['template', 'edm'])],
            'type' => ['required', Rule::in(['sms'])],
        ]);
        if ($input['type'] == 'sms') {
            $phone =  $adminUser->plaintext_phone;
            $code = CaptchaClient::sendSmsCode($phone, $input['action']);
            $result = SmsClient::sendBackendLoginCode($phone, $code);
        }
        return $this->success();
    }
}
