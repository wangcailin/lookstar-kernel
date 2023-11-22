<?php

namespace LookstarKernel\Application\Tenant\Contacts\Models;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $table = 'tenant_contacts_field';

    protected $fillable = [
        'tenant_id',
        'rule_name',
        'type',
        'form_type',
        'name',
        'label',
        'placeholder',
        'options',
    ];

    protected $casts = [
        'options' => 'json',
    ];
}
