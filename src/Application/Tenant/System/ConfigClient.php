<?php

namespace LookstarKernel\Application\Tenant\System;

use LookstarKernel\Application\Tenant\Push\Mail\PHPMailer;
use Composer\Http\Controller;
use LookstarKernel\Application\Tenant\System\Models\Config;
use Composer\Exceptions\ApiException;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class ConfigClient extends Controller
{
    public function __construct(Config $config)
    {
        $this->model = $config;

        $this->validateCreateRules = [
            'type' => [
                'required',
                Rule::in(Config::$EnumType),
            ],
        ];
    }

    public function get($type)
    {
        $this->beforeGet();
        $this->row = $this->model::firstWhere('type', $type);
        return $this->success($this->row);
    }

    public function updateOrCreate(Request $request)
    {
        $input = $request->all();

        if ($input['type'] == 'mail') {
            $this->checkMail($input['data']);
        }

        $this->row = $this->model->updateOrCreate(['type' => $request['type']], $input);
        return $this->success($this->row);
    }

    protected function checkMail($config)
    {
        $mail = PHPMailer::getClient($config);
        $mail->addAddress($config['username']);
        $mail->setFrom($config['username']);
        $mail->Subject = '验证邮件配置是否成功';
        $mail->Body = 'Success';
        $mail->send();
    }
}
