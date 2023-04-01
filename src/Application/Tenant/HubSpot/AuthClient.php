<?php

namespace LookstarKernel\Application\Tenant\HubSpot;

use LookstarKernel\Application\Tenant\System\Models\Config;
use LookstarKernel\Support\HubSpot\Client;
use Composer\Http\BaseController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AuthClient extends BaseController
{
    public function oauthCallback(Request $request)
    {
        $code = $request->input('code');
        if ($code) {
            Client::oauth($code);
        }
        return $this->success();
    }

    public function config(Request $request)
    {
        $input = $request->validate([
            'status' => [
                'required',
                Rule::in(['0', '1']),
            ]
        ]);
        Config::where('type', 'hubspot')->update(['data->status' => (int) $input['status']]);
        return $this->success();
    }
}
