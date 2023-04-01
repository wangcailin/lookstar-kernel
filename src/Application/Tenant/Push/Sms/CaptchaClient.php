<?php

namespace LookstarKernel\Application\Tenant\Push\Sms;

use Composer\Http\BaseController;
use Composer\Support\Crypt\AES;
use Illuminate\Http\Request;
use Composer\Support\Redis\CaptchaClient as RedisCaptchaClient;
use Composer\Support\Aliyun\SmsClient;

class CaptchaClient extends BaseController
{
    public function get(Request $request)
    {
        $input = $request->only(['phone', 'crypt_key', 'action']);
        $input['phone'] = AES::decodeRsa($input['crypt_key'], $input['phone']);

        $code = RedisCaptchaClient::sendSmsCode($input['phone'], $input['action']);

        $result = SmsClient::sendBackendLoginCode($input['phone'], $code);
        return $this->success();
    }
}
