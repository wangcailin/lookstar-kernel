<?php

namespace LookstarKernel\Application\Tenant\Project\MicroBook\Models;

use LookstarKernel\Application\Tenant\WeChat\Models\WeChatFreepublish;
use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Article extends Model
{
    protected $table = 'tenant_project_microbook_article';

    protected $fillable = [
        'category_id',
        'freepublish_id',
        'tag_ids',
        'sort',
    ];

    protected $casts = [
        'tag_ids' => 'array',
    ];

    public function freepublish()
    {
        return $this->hasOne(WeChatFreepublish::class, 'id', 'freepublish_id');
    }

    public static function createBatch($categoryId, $freepublishIds)
    {
        static::where('category_id', $categoryId)->delete();
        if ($freepublishIds) {
            foreach ($freepublishIds as $key => $id) {
                static::create([
                    'category_id' => $categoryId,
                    'freepublish_id' => $id,
                    'sort' => $key
                ]);
            }
        }
    }
}
