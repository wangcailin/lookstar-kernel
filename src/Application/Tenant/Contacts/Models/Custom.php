<?php

namespace LookstarKernel\Application\Tenant\Contacts\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Custom extends Model
{
    protected $table = 'tenant_contacts_custom';

    protected $fillable = [
        'contacts_id',
        'name',
        'value',
    ];
}
