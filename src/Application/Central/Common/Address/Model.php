<?php

namespace LookstarKernel\Application\Central\Common\Address;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Model extends Pivot
{
    use CentralConnection;
    protected $table = 'common_address';

    protected $fillable = [
        'parent',
        'value',
        'label',
    ];

    public static function getAddress(array $codes)
    {
        $address = [];
        foreach ($codes as $key => $code) {
            if ($row = self::firstWhere('value', $code)) {
                $address[] = $row['label'];
            } else {
                $address[] = $code;
            }
        }
        return $address;
    }
}
