<?php

namespace LookstarKernel\Application\Tenant\WeChat\Template;

use Composer\Application\WeChat\WeChat;
use Composer\Http\Controller;
use Illuminate\Http\Request;
use LookstarKernel\Application\Tenant\WeChat\Template\Models\Template;
use LookstarKernel\Application\Tenant\WeChat\Template\Models\TemplateWeChat;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;

class Client extends Controller
{
    public function __construct(Template $template)
    {
        $this->model = $template;

        $this->validateRules = [
            'appid' => 'required',
            'template_id' => 'required',
            'title' => ['required', 'max:128'],
            'data' => 'required',
            'data.data' => 'required',
        ];

        $this->allowedFilters = [
            AllowedFilter::exact('appid')
        ];
    }

    public function afterBuildFilter()
    {
        $this->model->with(['authorizer'])->withCount([
            'task as task_cnt',
            'task as task_success_cnt' => function (Builder $query) {
                $query->where(['status' => 3, 'send_status' => 2]);
            },
            'task as task_processing_cnt' => function (Builder $query) {
                $query->where(['status' => 1]);
            },
        ]);
    }

    public function syncWeChat(Request $request, WeChat $weChat)
    {
        $input = $request->validate([
            'appid' => 'required',
        ]);

        $api = $weChat->getOfficialAccount($input['appid'])->getClient();
        $list = $api->get('/cgi-bin/template/get_all_private_template');

        TemplateWeChat::where($input)->delete();
        foreach ($list['template_list'] as $key => $value) {
            $value['appid'] = $input['appid'];
            TemplateWeChat::create($value);
        }

        return $this->success();
    }

    public function getWeChat(Request $request)
    {
        $input = $request->validate([
            'appid' => 'required',
        ]);

        $lsit = TemplateWeChat::where($input)->get();
        return $this->success($lsit);
    }

    public function getWeChatTemplate($templateId)
    {
        $row = TemplateWeChat::firstWhere('template_id', $templateId);
        return $this->success($row);
    }

    public function sendTemplatePreviewQrcode(Request $request, WeChat $weChat)
    {
        $input = $request->validate([
            'appid' => 'required',
            'id' => 'required',
        ]);
        $sceneStr = 'template_preview_' . $input['id'];
        $api = $weChat->getOfficialAccount($input['appid'])->getClient();
        $response = $api->postJson('/cgi-bin/qrcode/create', [
            'expire_seconds' => 300,
            'action_name' => 'QR_STR_SCENE',
            'action_info' => [
                'scene' => [
                    'scene_str' => $sceneStr
                ]
            ]
        ]);
        return $this->success($response->toArray());
    }
}
