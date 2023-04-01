<?php

namespace LookstarKernel\Application\Tenant\Project\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Project extends Model
{
    protected $table = 'tenant_project';

    protected $fillable = [
        'type',
        'title',
        'description',
        'uuid',
        'state',
        'auth_user_id',
        'tag_ids',
    ];

    protected $casts = [
        'tag_ids' => 'array',
    ];

    protected static function booting()
    {
        static::creating(function ($project) {
            $project->uuid = Str::uuid();
            $project->auth_user_id = Auth::user()->id;
        });
    }

    public function getTypeNameAttribute()
    {
        $data = [
            'landing' => '落地页海报',
            'data_download' => '资料下载',
            'microbook' => '微刊',
            'event' => '活动中心',
        ];
        return $data[$this->type];
    }
}
