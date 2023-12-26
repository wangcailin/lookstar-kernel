<?php

namespace LookstarKernel\Application\Tenant\AI\GPT\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Repository extends Model
{
    const TYPE_WECHAT = 'wechat';
    protected $table = 'tenant_ai_gpt_repository';

    protected $fillable = [
        'tenant_id',
        'project_id',
        'repository_id',
        'old_repository_id',
        'state',
    ];

    public static function getRepositoryId($projectId)
    {
        $repositoryModel = self::firstOrCreate(['project_id' => $projectId], ['repository_id' => uniqid()]);
        return $repositoryModel->repository_id;
    }
}
