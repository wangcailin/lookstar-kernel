<?php

namespace LookstarKernel\Support\Eloquent;

use Spatie\Permission\Models\Role;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class TenantRoleModel extends Role
{
    use BelongsToTenant;
}
