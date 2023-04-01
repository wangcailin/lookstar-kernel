<?php

namespace LookstarKernel\Application\Central\Tenant\WeChat\Response\Traits;

use LookstarKernel\Application\Tenant\WeChat\Models\WeChatMaterial as Material;

trait ReplyMaterial
{
    public function replyMaterial($id, $openid)
    {
        $material = Material::firstWhere('id', $id);
        if ($material) {
            switch ($material['type']) {
                case 2:
                    $message = [];
                    $message['touser'] = $openid;
                    $message['msgtype'] = 'link';
                    $message['link'] = [
                        'title' => empty($material['data']['title']) ? '' : $material['data']['title'],
                        'description' => empty($material['data']['description']) ? '' : $material['data']['description'],
                        'url' => $material['data']['url'],
                        'thumb_url' => $material['data']['image'],
                    ];
                    $api = $this->app->getClient();
                    $response = $api->postJson('/cgi-bin/message/custom/send', $message);
                    // $app->customer_service->message(new Raw(json_encode($data)))->to($openid)->send();
                    return;
                    break;
                case 3:
                    return $material['data']['text'];
                    break;
            }
        }
        return;
    }
}
