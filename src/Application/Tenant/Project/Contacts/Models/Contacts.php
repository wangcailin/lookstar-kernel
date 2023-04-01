<?php

namespace LookstarKernel\Application\Tenant\Project\Contacts\Models;

use LookstarKernel\Application\Tenant\Contacts\Traits\GetData;
use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Contacts extends Model
{
    use GetData;
    protected $table = 'tenant_project_contacts';

    protected $fillable = [
        'project_id',
        'data',
        'data->phone',
        'data->email',
        'source',
    ];

    protected $casts = [
        'data' => 'json',
        'source' => 'json',
    ];

    protected $appends = ['mask_data'];

    public function getMaskDataAttribute()
    {
        return $this->getMaskData($this->data);
    }

    public function getPlaintextDataAttribute()
    {
        return $this->getPlaintextData($this->data);
    }

    public static function addressMap($address)
    {
        if (is_array($address)) {
            $data = [];
            $map = ['province', 'city', 'district', 'full_address'];
            $len = count($address) - 1;
            for ($i = 0; $i <= $len; $i++) {
                $data[$map[$i]] = $address[$i];
            }
            return $data;
        }
    }
}
