<?php

namespace LookstarKernel\Application\Tenant\Tag\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class TagGroup extends Model
{
    protected $table = 'tenant_tag_group';

    protected $fillable = [
        'name',
        'state',
    ];

    public function tag()
    {
        return $this->hasMany(Tag::class, 'group_id', 'id');
    }

    public function children()
    {
        return $this->tag()->select(['id', 'group_id', 'id as value', 'name as title']);
    }
}
