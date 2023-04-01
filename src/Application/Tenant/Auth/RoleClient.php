<?php

namespace LookstarKernel\Application\Tenant\Auth;

use Composer\Application\Auth\RoleClient as BaseRuleClient;
use Illuminate\Support\Facades\Validator;

class RoleClient extends BaseRuleClient
{
    public function handleValidate()
    {
        Validator::make(
            $this->data,
            [
                'name' => [
                    'required',
                    tenant()->unique(config('permission.models.role'), 'name'),
                ],
            ],
            $this->validateMessage
        )->validate();
    }

    public function handleUpdateValidate()
    {
        Validator::make(
            $this->data,
            ['name' => [
                tenant()->unique(config('permission.models.role'), 'name')->ignore($this->id),
            ]],
            $this->validateMessage
        )->validate();
    }
}
