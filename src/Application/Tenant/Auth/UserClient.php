<?php

namespace LookstarKernel\Application\Tenant\Auth;

use LookstarKernel\Application\Tenant\Auth\Models\User;
use Composer\Application\Auth\UserClient as BaseUserClient;
use Composer\Exceptions\ApiErrorCode;
use Composer\Exceptions\ApiException;

class UserClient extends BaseUserClient
{
    public function __construct(User $user)
    {
        $this->model = $user;

        $this->allowedIncludes = ['roles'];

        $this->validateRules = [
            'username' => 'required',
            'password' => 'required',
        ];
    }

    public function beforeCreate()
    {
        $this->data = request()->all();
        $this->data['username'] = $this->data['username'] . '@' . tenant('domain');

        if ($this->authUserId) {
            $this->createAuthUserId();
        }
        $this->handleCreateValidate();
    }

    public function beforeDelete()
    {
        if ($this->model::findOrFail($this->id)['is_admin'] == '1') {
            throw new ApiException('不能删除超级管理员', ApiErrorCode::VALIDATION_ERROR);
        }
    }
}
