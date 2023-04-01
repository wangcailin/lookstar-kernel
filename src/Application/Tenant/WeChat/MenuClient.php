<?php

namespace LookstarKernel\Application\Tenant\WeChat;

use LookstarKernel\Application\Tenant\WeChat\Models\WeChatMenu;
use Composer\Application\WeChat\WeChat;
use Composer\Http\BaseController;
use Illuminate\Http\Request;

class MenuClient extends BaseController
{
    public function get($appid = '', WeChat $weChat)
    {
        $api = $weChat->getOfficialAccount($appid)->getClient();
        $current = $api->get('/cgi-bin/get_current_selfmenu_info')->toArray();
        $list = $api->get('/cgi-bin/menu/get')->toArray();

        foreach ($current['selfmenu_info']['button'] as $key => &$value) {
            if (isset($value['sub_button'])) {
                foreach ($value['sub_button']['list'] as $k => &$v) {
                    $this->getMenuData($v, $appid);
                }
            } else {
                $this->getMenuData($value, $appid);
            }
        }

        return $this->success(['list' => $list, 'current' => $current]);
    }

    public function create(Request $request, WeChat $weChat)
    {
        $data = $request->all();
        $menuData = $data['selfmenu_info'];

        $api = $weChat->getOfficialAccount($data['appid'])->getClient();

        WeChatMenu::where('appid', $data['appid'])->delete();

        foreach ($menuData['button'] as $key => &$value) {
            if (isset($value['sub_button'])) {
                foreach ($value['sub_button'] as $k => &$v) {
                    WeChatMenu::create($this->handleMenuData($v, $data['appid']));
                }
            } else {
                WeChatMenu::create($this->handleMenuData($value, $data['appid']));
            }
        }
        $response = $api->postJson('/cgi-bin/menu/create', $menuData);
        return $this->success($response->toArray());
    }

    protected function handleMenuData(&$menu, $appid)
    {
        $data = [
            'appid' => $appid,
            'name' => $menu['name'],
            'type' => $menu['type']
        ];
        if ($menu['type'] == 'miniprogram') {
            $data['value'] = $menu['pagepath'];
        } elseif ($menu['type'] == 'view') {
            $data['value'] = $menu['url'];
        } else if ($menu['type'] == 'click') {
            if (isset($menu['data'])) {
                $data['data'] = $menu['data'];
                if (!isset($menu['key'])) {
                    $menu['key'] = substr(md5(uniqid(rand(), 1)), 8, 16);
                }
            }
            $data['value'] = $menu['key'];
        } elseif ($menu['type'] == 'article_id') {
            $data['value'] = $menu['article_id'];
        }
        return $data;
    }

    protected function getMenuData(&$menu, $appid)
    {
        if ($menu['type'] == 'click') {
            $model = WeChatMenu::firstWhere(['appid' => $appid, 'value' => $menu['key']]);
            if ($model) {
                $menu['data'] = $model['data'];
            }
        }
    }
}
