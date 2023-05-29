<?php

namespace LookstarKernel\Application\Tenant\System\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class Config extends Model
{
    protected $table = 'tenant_system_config';

    protected $fillable = [
        'type',
        'data',
    ];

    protected $casts = [
        'data' => 'json',
    ];

    public static $EnumType = ['mail', 'hubspot'];

    public static function getMailConfig()
    {
        return self::where('type', 'mail')->first()['data'];
    }

    public static function getHubSpotConfig()
    {
        if ($model = self::where('type', 'hubspot')->first()) {
            return $model['data'];
        } else {
            return false;
        }
    }
}
