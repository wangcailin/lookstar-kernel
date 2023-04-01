<?php

namespace LookstarKernel\Application\Tenant\Push\Mail;

use Composer\Http\BaseController;
use Composer\Support\Crypt\AES;
use Illuminate\Http\Request;
use LookstarKernel\Application\Tenant\Push\Mail\Job;
use LookstarKernel\Application\Tenant\System\Models\Config;
use Composer\Support\Redis\CaptchaClient as RedisCaptchaClient;

class CaptchaClient extends BaseController
{
    public function get(Request $request)
    {
        $input = $request->only(['email', 'crypt_key', 'action']);
        $input['email'] = AES::decodeRsa($input['crypt_key'], $input['email']);

        $config = Config::getMailConfig();

        $code = RedisCaptchaClient::sendEmailCode($input['email'], $input['action']);
        $mailTemplate = file_get_contents(resource_path('views/emails/captcha/contacts.blade.php'));
        $mailContent = str_replace('{email}', $input['email'], $mailTemplate);
        $mailContent = str_replace('{code}', $code, $mailContent);
        Job::dispatch($config, $input['email'], '您收到了一个验证码', $mailContent)->onQueue('mail');

        return $this->success();
    }
}
