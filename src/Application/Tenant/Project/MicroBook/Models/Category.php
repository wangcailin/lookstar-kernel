<?php

namespace LookstarKernel\Application\Tenant\Project\MicroBook\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Category extends Model
{
    protected $table = 'tenant_project_microbook_category';

    protected $fillable = [
        'project_id',
        'name',
        'state',
        'sort',
    ];

    public function article()
    {
        return $this->hasMany(Article::class, 'category_id', 'id');
    }
}
