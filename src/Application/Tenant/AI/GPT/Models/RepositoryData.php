<?php

namespace LookstarKernel\Application\Tenant\AI\GPT\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class RepositoryData extends Model
{
    const TYPE_WECHAT_FREPUBLISH = 'wechat_freepublish';

    const SOURCE_TYPE_WECHAT_FREPUBLISH = 'wechat_freepublish';
    const SOURCE_TYPE_UPLOAD = 'upload';
    const SOURCE_TYPE_CREATE = 'create';

    const UPLOAD_PREFIX_URL = '/app/gpt/repository/';
    const UPLOAD_HTML = '/app/gpt/repository/files/html';
    const UPLOAD_FILE = '/app/gpt/repository/files/url';
    const DELETE_URL = '/app/gpt/repository/files/url';
    const DELETE_FILES = '/app/gpt/repository/files';

    const TRANSFORM_WORD_URL = '/app/gpt/documents/transform/word';

    protected $table = 'tenant_ai_gpt_repository_data';

    protected $fillable = [
        'tenant_id',
        'project_id',
        'source_project_id',
        'openid',
        'type',
        'source_type',
        'title',
        'url',
        'metadata',
        'status',
        'state',
        'tag_ids_download',
        'lookstar_score_download',
        'content',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];
}
