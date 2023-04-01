<?php

declare(strict_types=1);

namespace LookstarKernel\Application\Tenant\Contacts\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (!tenancy()->initialized) {
            return;
        }
        $fieldName = $model->qualifyColumn(BelongsToTenant::$tenantIdColumn);
        $builder->whereNull($fieldName)->orWhere($fieldName, tenant()->getTenantKey());
    }

    public function extend(Builder $builder)
    {
        $builder->macro('withoutTenancy', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
