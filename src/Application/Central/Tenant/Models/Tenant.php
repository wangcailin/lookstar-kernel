<?php

namespace LookstarKernel\Application\Central\Tenant\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Database\Concerns\HasScopedValidationRules;

class Tenant extends BaseTenant
{
    use HasScopedValidationRules;

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'domain',
        ];
    }
}
