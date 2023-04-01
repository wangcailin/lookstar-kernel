<?php

namespace LookstarKernel\Application\Central\Tenant\WeChat\Response;

use Composer\Application\WeChat\Response\Client as BaseClient;
use LookstarKernel\Application\Central\Tenant\Models\Tenant;
use LookstarKernel\Application\Tenant\WeChat\Models\WeChatAuthorizer;
use Composer\Application\WeChat\WeChat;
use Illuminate\Support\Facades\Redis;
use LookstarKernel\Application\Central\Tenant\WeChat\Response\Traits;
use LookstarKernel\Application\Tenant\WeChat\Models\WeChatMenu;
use LookstarKernel\Application\Tenant\WeChat\Models\WeChatOpenid;
use LookstarKernel\Application\Tenant\WeChat\Models\WeChatQrcode as Qrcode;
use LookstarKernel\Application\Tenant\WeChat\Models\WeChatReply as Reply;
use LookstarKernel\Application\Tenant\WeChat\Template\Models\Template;
use EasyWeChat\OfficialAccount\Application as OfficialAccountApplication;
use Illuminate\Support\Facades\Log;

class Client extends BaseClient
{
    public $response = '';
    public $appId = '';
    public $tenantId = '';
    public $tag = '';
    public $itemKey = '';
    public ?OfficialAccountApplication $app = null;

    use Traits\ReplyMaterial;
    use Traits\BindTags;
    use Traits\BindItemKey;

    public function response($appId = '', WeChat $weChat)
    {
        $this->appId = $appId;
        $this->tenantId = $this->getTenant($appId);
        $tenant = Tenant::find($this->tenantId);
        $tenant->run(function () use ($weChat, $appId) {
            $this->app = $weChat->getOfficialAccount($appId);
            $server = $this->app->getServer();

            $server->with(function ($message, \Closure $next) {
                $message = $message->toArray();
                $body = false;
                switch ($message['MsgType']) {
                    case 'text':
                        $body = $this->textMessageHandler($message);
                        break;
                    case 'event':
                        $body = $this->eventMessageHandler($message);
                        break;
                }
                $this->logHandler($message);
                if ($body === false) {
                    return $next($message);
                }
                return $body;
            });

            $this->response =  $server->serve();
        });
        return $this->response;
    }

    protected function getTenant($appid)
    {
        if ($tenantId = Redis::get('tenant:wechat:authorizer:' . $appid)) {
            return $tenantId;
        } else {
            $tenantId = WeChatAuthorizer::where('appid', $appid)->value('tenant_id');
            if ($tenantId) {
                Redis::set('tenant:wechat:authorizer:' . $appid, $tenantId);
            }
            return $tenantId;
        }
    }

    protected function textMessageHandler($message)
    {
        $where = ['appid' => $this->appId];
        $reply = [];
        if ($reply = Reply::where($where)->where('match', 'EQUAL')->where('text', $message['Content'])->first()) {
        } else if ($reply = Reply::where($where)->where('match', 'CONTAINED')->whereRaw('LOCATE(text, ?) > 0', [$message['Content']])->first()) {
        } else if ($reply = Reply::where($where)->where('match', 'CONTAIN')->where('text', 'like', "%{$message['Content']}%")->first()) {
        } else if ($reply = Reply::where($where)->where('type', 'msg')->first()) {
        }

        if ($reply) {
            if (isset($reply['tag_ids']) && $reply['tag_ids']) {
                $this->bindTags($reply['tag_ids']);
            }
            $this->BindItemKey($reply['id']);
            return $this->replyMaterial($reply['reply_material_id'], $message['FromUserName'], $this->app);
        }
        return false;
    }

    protected function eventMessageHandler($message)
    {
        switch ($message['Event']) {
            case 'unsubscribe':
                if ($userOpenid = WeChatOpenid::firstWhere('openid', $message['FromUserName'])) {
                    $userOpenid->update(['subscribe' => 0]);
                }
                break;
            case 'subscribe':
                if (empty($message['EventKey'])) {
                    $where = ['appid' => $this->appId];
                    if ($reply = Reply::where($where)->where('type', 'subscribe')->first()) {
                        $this->BindItemKey($reply['id']);
                        return $this->replyMaterial($reply['reply_material_id'], $message['FromUserName']);
                    }
                }
            case 'SCAN':
                $sceneStr = str_replace('qrscene_', '', $message['EventKey']);
                $qrcode = Qrcode::firstWhere('scene_str', $sceneStr);
                if ($qrcode) {
                    if (isset($qrcode['tag_ids']) && $qrcode['tag_ids']) {
                        $this->bindTags($qrcode['tag_ids']);
                    }
                    return $this->replyMaterial($qrcode['reply_material_id'], $message['FromUserName']);
                }
                if (strpos($sceneStr, 'template_preview_') !== false) {
                    $sceneStrArr = explode('_', $sceneStr);
                    $templateId = $sceneStrArr[2];
                    $template = Template::find($templateId);
                    $data = [
                        'touser' => $message['FromUserName'],
                        'template_id' => $template['template_id'],
                    ];
                    $data = array_merge($data, $template['data']);
                    $api = $this->app->getClient();
                    $response = $api->postJson('/cgi-bin/message/template/send', $data);
                }
                break;
            case 'CLICK':
                $key = $message['EventKey'];
                $menu = WeChatMenu::firstWhere('value', $key);
                if ($menu && $menu['data']) {
                    foreach ($menu['data'] as $key => $value) {
                        $message = ['touser' => $message['FromUserName']];
                        if (in_array($value['type'], ['text', 'image', 'voice', 'video'])) {
                            $message['msgtype'] = $value['type'];
                        }

                        if ($value['type'] == 'text') {
                            $message['text']['content'] = $value['value'];
                        } elseif ($value['type'] == 'image') {
                            $message['image']['media_id'] = $value['value'];
                        } elseif ($value['type'] == 'voice') {
                            $message['voice']['media_id'] = $value['value'];
                        } elseif ($value['type'] == 'video') {
                            $message['video'] = [
                                'media_id' => $value['value']['media_id'],
                                'title' => $value['value']['title'],
                                'description' => $value['value']['description'],
                            ];
                        }
                        $api = $this->app->getClient();
                        $response = $api->postJson('/cgi-bin/message/custom/send', $message);
                    }
                }
                break;
        }
        return false;
    }

    protected function logHandler($message)
    {
        $openid = $message['FromUserName'];
        $appid = $this->appId;
        $time = time() * 1000;
        $trackId = mt_rand();
        $dateTime = date('Y-m-d H:i:s');

        $data = [
            'lookstar_tenant_id' => $this->tenantId,
            'mp_openid' => $openid,
            'login_id' => $openid,
            'distinct_id' => $openid,
            'anonymous_id' => $openid,
            'properties_lib' => 'server',
            'properties_mp_appid' => $appid,
            'properties_mp_msg_type' => $message['MsgType'],
            'properties_mp_content' => isset($message['Content']) ? $message['Content'] : null,
            'properties_mp_event' => isset($message['Event']) ? $message['Event'] : null,
            'properties_mp_event_key' => isset($message['EventKey']) ? str_replace('qrscene_', '', $message['EventKey']) : null,
            'type' => 'track',
            'event' => '$MPMessage',
            'time' => $time,
            '_track_id' => $trackId,
            '_flush_time' => $time,
            'create_time' => $dateTime,
            'receive_time' => $dateTime,
            'ds' => date('Ymd')
        ];
        if ($this->tag) {
            $data['lookstar_tag'] = $this->tag;
        }
        if ($this->itemKey) {
            $data['properties_item_key'] = $this->itemKey;
        }
        Log::channel('analytics')->info('', $data);
        return false;
    }
}
