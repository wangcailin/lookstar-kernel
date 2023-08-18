<?php

namespace LookstarKernel\Application\Tenant\Project\Contacts\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class EmailLogs extends Model
{
    protected $table = 'tenant_project_contacts_email_logs';

    protected $fillable = [
        'tenant_id',
        'project_id',
        'contacts_id',
    ];
}
