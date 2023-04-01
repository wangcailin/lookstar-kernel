<?php

namespace LookstarKernel\Application\Tenant\WeChat\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class WeChatOpenidTagRelation extends Pivot
{
    protected $table = 'tenant_wechat_openid_tag_relation';

    protected $fillable = [
        'openid',
        'tag_id',
        'source',
    ];

    protected $casts = [
        'source' => 'json',
    ];

    public static function batchCreate($openid, $tagIds, $source = [])
    {
        foreach ($tagIds as $k => $tagId) {
            $data = ['openid' => $openid, 'tag_id' => $tagId, 'source' => $source];
            self::create($data);
        }
    }
}
