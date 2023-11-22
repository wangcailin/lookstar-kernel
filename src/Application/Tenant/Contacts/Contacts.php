<?php

namespace LookstarKernel\Application\Tenant\Contacts\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;
use Composer\Support\Database\Models\Traits\UuidPrimaryKey;

class Contacts extends Model
{
    use UuidPrimaryKey;

    protected $table = 'tenant_contacts';

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'company',
        'industry',
        'job',
        'country',
        'province',
        'city',
        'district',
        'full_address',
        'source',
        'channel',
        'star',
    ];

    protected $casts = [
        'source' => 'json',
    ];

    protected $appends = ['mask_data', 'lookstar_score'];
}
