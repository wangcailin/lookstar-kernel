<?php

namespace LookstarKernel\Application\Tenant\WeChat;

use LookstarKernel\Application\Tenant\WeChat\Models\WeChatReply as Reply;
use Spatie\QueryBuilder\AllowedFilter;
use Composer\Application\WeChat\ReplyClient as Client;

class ReplyClient extends Client
{
    public function __construct(Reply $reply)
    {
        $this->model = $reply;
        $this->allowedFilters = [
            AllowedFilter::exact('appid'),
            AllowedFilter::exact('type'),
            'text',
        ];
    }
}
