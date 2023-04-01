<?php

namespace LookstarKernel\Application\Tenant\Contacts\Models\Analytics;

use LookstarKernel\Support\Eloquent\TenantModel as Model;
use LookstarKernel\Application\Tenant\Contacts\Traits\GetData;
use Composer\Support\Database\Models\Traits\UuidPrimaryKey;

class DimContacts extends Model
{
    protected $connection = 'data_warehouse';
    protected $table = 'dim_tenant_contacts';

    use GetData;
    use UuidPrimaryKey;


    protected $appends = ['mask_data'];

    public function getMaskDataAttribute()
    {
        return $this->getMaskData($this->attributes);
    }

    public function getPlaintextDataAttribute()
    {
        return $this->getPlaintextData($this->attributes);
    }
}
