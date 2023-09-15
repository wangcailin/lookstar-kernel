<?php

namespace App\Application\Tenant\Analytics\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OdsEvents extends Model
{

    protected $connection = 'data_warehouse';
    protected $table = 'ods_events';

    protected static function booting()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $builder->where('lookstar_tenant_id', tenant()->getTenantKey());
        });
    }
}
