<?php

namespace LookstarKernel\Application\Tenant\Project\Contacts\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class ContactsFields extends Model
{
    protected $table = 'tenant_project_contacts_fields';

    protected $fillable = [
        'project_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public static function getProjectFields($projectId)
    {
        return self::firstWhere('project_id', $projectId)['data'];
    }
}
