<?php

namespace LookstarKernel\Application\Tenant\Project\DataDownload\Models;

use LookstarKernel\Application\Tenant\Auth\Models\User;
use LookstarKernel\Support\Eloquent\TenantModel as Model;

class DataDownload extends Model
{
    protected $table = 'tenant_project_data_download';

    public $incrementing = false;

    protected $fillable = [
        'project_id',
        'auth_user_id',
        'parent_id',
        'type',
        'file_type',
        'title',
        'tag_ids_prview',
        'tag_ids_download',
        'file',
    ];

    protected $casts = [
        'file' => 'array',
        'tag_ids_prview' => 'array',
        'tag_ids_download' => 'array',
        'file_type' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'auth_user_id', 'id')->select('id', 'username');
    }
}
