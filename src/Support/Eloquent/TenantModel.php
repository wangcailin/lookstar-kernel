<?php

namespace LookstarKernel\Support\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

abstract class TenantModel extends Model
{
    use BelongsToTenant;
}
