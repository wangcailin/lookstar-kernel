<?php

namespace LookstarKernel\Application\Tenant\System;

use Composer\Exceptions\ApiErrorCode;
use Composer\Exceptions\ApiException;
use Composer\Http\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

class OpenApiClient extends BaseController
{
    public function getClient()
    {
        $client = Passport::client();
        $row =  $client->where('user_id', Auth::id())->first();
        return $this->success($row);
    }

    public function createClient(ClientRepository $clients)
    {
        $client = Passport::client();
        if ($client->where('user_id', Auth::id())->first()) {
            throw new  ApiException('Client已存在！', ApiErrorCode::VALIDATION_ERROR);
        }
        $client = $clients->create(
            Auth::id(),
            tenant('id'),
            ''
        );
        return $this->success(['secret' => $client->secret]);
    }

    public function resetClientSecret(ClientRepository $clients, Request $request)
    {
        $clientId = $request->input('client_id');
        $client = $clients->findForUser($clientId, Auth::id());
        $client =  $clients->regenerateSecret($client);
        return $this->success(['secret' => $client->secret]);
    }
}
