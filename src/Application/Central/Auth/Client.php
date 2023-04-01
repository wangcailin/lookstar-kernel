<?php

namespace LookstarKernel\Application\Central\Auth;

use LookstarKernel\Application\Central\Auth\Models\User;
use Composer\Support\Auth\Client as AuthClient;

class Client extends AuthClient
{
    public function __construct(User $user)
    {
        $this->model = $user;
    }

    protected function respondWithToken($token)
    {
        return $this->success([
            'access_token' => $token,
        ]);
    }

    protected function getAccessToken()
    {
        $this->token = $this->user->createToken('central')->accessToken;
    }
}
