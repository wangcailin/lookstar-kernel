<?php

namespace LookstarKernel\Application\Tenant\WeChat\Job;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

use Composer\Application\WeChat\WeChat;
use LookstarKernel\Application\Central\Tenant\Models\Tenant;
use LookstarKernel\Application\Tenant\WeChat\Models\WeChatAuthorizer;
use LookstarKernel\Application\Tenant\WeChat\Models\WeChatOpenid;

class SyncOpenid implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Dispatchable;

    public $timeout = 43200;

    public function __construct()
    {
    }

    public function handle()
    {
        $tenantId = $this->job->payload()['tenant_id'];
        $tenant = Tenant::find($tenantId);
        $tenant->run(function () use ($tenantId) {
            $weChat = new WeChat();
            $model = new WeChatOpenid();
            $appidList = WeChatAuthorizer::where(['type' =>  1, 'tenant_id' => $tenantId])->get();
            foreach ($appidList as $key => $value) {
                $appid = $value['appid'];
                $app = $weChat->getOfficialAccount($appid);
                $nextOpenid = null;
                do {
                    $result = $app->user->list($nextOpenid);
                    $nextOpenid = $result['next_openid'];
                    $lastOpenid = null;
                    foreach ($result['data']['openid'] as $key => $value) {
                        $lastOpenid = $value;
                        // $user = $app->user->get($value);
                        $where = [
                            'appid' => $appid,
                            'openid' => $value,
                        ];
                        $data = [
                            'subscribe' => 1,
                            // 'subscribe_time' => $user['subscribe_time'],
                            // 'subscribe_scene' => $user['subscribe_scene'],
                            // 'remark' => $user['remark'],
                            // 'qr_scene' => $user['qr_scene'],
                            // 'qr_scene_str' => $user['qr_scene_str'],
                        ];
                        // if (isset($user['unionid'])) {
                        //     $data['unionid'] = $user['unionid'];
                        // }
                        $model::updateOrCreate($where, $data);
                    }
                } while ($nextOpenid != $lastOpenid);
            }
        });
    }
}
