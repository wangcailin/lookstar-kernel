<?php

namespace LookstarKernel\Application\Tenant\WeChat;

use Composer\Application\WeChat\QrcodeClient as Client;
use Composer\Application\WeChat\WeChat;
use Spatie\QueryBuilder\AllowedFilter;
use LookstarKernel\Application\Tenant\WeChat\Models\WeChatQrcode as Qrcode;

class QrcodeClient extends Client
{
    public function __construct(WeChat $weChat, Qrcode $qrcode)
    {
        $this->weChat = $weChat;
        $this->model = $qrcode;
        $this->allowedFilters = [
            AllowedFilter::exact('appid'),
            'name',
        ];
    }

    public function afterBuildFilter()
    {
        $this->model->with('authorizer');
    }
}
