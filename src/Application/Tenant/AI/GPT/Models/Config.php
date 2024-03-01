<?php

namespace LookstarKernel\Application\Tenant\AI\GPT\Models;

use App\Application\Tenant\Project\Models\Project;
use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Config extends Model
{
    protected $table = 'tenant_ai_gpt_config';

    protected $fillable = [
        'tenant_id',
        'project_id',
        'title',
        'nickname',
        'avatar',
        'description',
        'prompt',
        'preset_question',
        'is_download',
        'is_download_register',
        'repository_project',
        'preset_question',
        'data',
        'data->share',
    ];

    protected $casts = [
        'data' => 'object',
        'preset_question' => 'array',
    ];

    public function project()
    {
        return $this->hasOne(Project::class, 'id', 'project_id');
    }
}
