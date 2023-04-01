<?php

namespace LookstarKernel\Support\Aliyun;

use AlibabaCloud\SDK\Devops\V20210625\Devops;
use AlibabaCloud\SDK\Devops\V20210625\Models\StartPipelineRunRequest;
use \Exception;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\Tea\Utils\Utils;

use Darabonba\OpenApi\Models\Config;

class DevopsClient
{
    public static $client;

    public static function createClient()
    {
        if (!self::$client) {
            $config = new Config([
                'accessKeyId' => config('composer.aliyun_access_key_id'),
                'accessKeySecret' => config('composer.aliyun_access_key_secret')
            ]);
            // 访问的域名
            $config->endpoint = "devops.cn-hangzhou.aliyuncs.com";
            self::$client = new Devops($config);
        }
        return self::$client;
    }

    public function startPipelineRun($organizationId, $pipelineId, $params)
    {
        try {
            self::createClient();
            $request = new StartPipelineRunRequest([
                'params' => json_encode($params['params'])
            ]);
            self::$client->startPipelineRun($organizationId, $pipelineId, $request);
        } catch (Exception $error) {
            if (!($error instanceof TeaError)) {
                $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
            }
            // 如有需要，请打印 error
            Utils::assertAsString($error->message);
        }
    }
}
