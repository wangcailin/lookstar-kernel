<?php

namespace LookstarKernel\Application\Tenant\WeChat;

use LookstarKernel\Application\Tenant\WeChat\Models\WeChatFreepublish;
use LookstarKernel\Application\Tenant\WeChat\Models\WeChatAuthorizer as Authorizer;
use Composer\Http\Controller;
use Spatie\QueryBuilder\AllowedFilter;
use Composer\Application\WeChat\WeChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FreepublishClient extends Controller
{
    public function __construct(WeChatFreepublish $weChatFreepublish)
    {
        $this->model = $weChatFreepublish;
        $this->allowedFilters = [
            AllowedFilter::exact('appid'),
            'title',
        ];
        $this->allowedSorts = ['create_time'];
    }

    public function getIds(Request $request)
    {
        $ids = $request->input('ids');
        $list = $this->model::whereIn('id', explode(',', $ids))->orderBy(DB::raw('FIELD(id, ' . $ids . ')'), 'ASC')->get();
        return $this->success($list);
    }

    public function asyncDay(WeChat $weChat)
    {
        $dayTime = strtotime(date('Y-m-d'));
        $appidList = Authorizer::where('type', 1)->get();
        foreach ($appidList as $key => $value) {
            $appid = $value['appid'];
            $api = $weChat->getOfficialAccount($appid)->getClient();

            $offset = 0;
            $count = 10;
            do {
                $data = [
                    "offset" => $offset,
                    "count" => $count,
                    "no_content" => 1
                ];
                $result = $api->postJson('/cgi-bin/freepublish/batchget', $data)->toArray();

                if ($result['item']) {
                    foreach ($result['item'] as $key => $item) {
                        $articleId = $item['article_id'];
                        $createTime = $item['content']['create_time'];
                        if ($createTime < $dayTime) {
                            return $this->success();
                        }
                        $updateTime = $item['content']['update_time'];
                        foreach ($item['content']['news_item'] as $k => $v) {
                            $this->model::updateOrCreate([
                                'article_id' => $articleId,
                                'url' => $v['url'],
                                'appid' => $appid
                            ], [
                                'create_time' => $createTime,
                                'update_time' => $updateTime,
                                'title' => $v['title'],
                                'thumb_url' => $v['thumb_url'],
                                'author' => $v['author'],
                                'digest' => $v['digest'],
                                'thumb_media_id' => $v['thumb_media_id'],
                                'show_cover_pic' => $v['show_cover_pic'],
                            ]);
                        }
                    }
                }
                $offset += $count;
            } while ($result['item']);
        }
    }

    public function getArticleId($articleId, WeChat $weChat, Request $request)
    {
        $input = $request->validate([
            'appid' => 'required',
        ]);

        $api = $weChat->getOfficialAccount($input['appid'])->getClient();
        $response = $api->get('/cgi-bin/freepublish/getarticle', ['article_id' => $articleId])->toArray();
        return $this->success($response);
    }
}
