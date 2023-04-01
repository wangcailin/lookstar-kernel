<?php

namespace LookstarKernel\Application\Tenant\HubSpot\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class ContactsFieldHubspot extends Model
{
    protected $table = 'tenant_contacts_field_hubspot';

    protected $fillable = [
        'contacts_field_name',
        'hubspot_field_name',
        'hubspot_field_type',
        'hubspot_field_label',
    ];
}
