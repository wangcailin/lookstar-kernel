<?php

namespace LookstarKernel\Application\Tenant\AI\GPT\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class RepositoryData extends Model
{
    const TYPE_WECHAT_FREPUBLISH = 'wechat_freepublish';

    const SOURCE_TYPE_WECHAT_FREPUBLISH = 'wechat_freepublish';
    const SOURCE_TYPE_UPLOAD = 'upload';

    const UPLOAD_HTML = '/app/gpt/wechat/files/html';
    const UPLOAD_FILE = '/app/gpt/wechat/files/url';
    const DELETE_URL = '/app/gpt/wechat/files/url';

    const TRANSFORM_WORD_URL = '/app/gpt/documents/transform/word';

    protected $table = 'tenant_ai_gpt_repository_data';

    protected $fillable = [
        'tenant_id',
        'project_id',
        'openid',
        'type',
        'source_type',
        'title',
        'url',
        'metadata',
        'status',
        'state',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];
}
