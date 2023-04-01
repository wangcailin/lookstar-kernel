<?php

namespace LookstarKernel\Application\Central\OAuth;

use Composer\Http\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class Client extends BaseController
{
    public function redirect(Request $request)
    {
        // $request->session()->put('state', $state = Str::random(40));
        $state = 'O0EaICa7nMlP1uaNrkNcoJ1WEG1uyPIcKQS0bwf7';

        $query = http_build_query([
            'client_id' => 'client-id',
            'redirect_uri' => 'http://third-party-app.com/callback',
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
        ]);

        return redirect('http://' . env('APP_URL', 'api.lookstar.com.cn') . '/oauth/authorize?' . $query);
    }

    public function callback(Request $request)
    {
        // $state = $request->session()->pull('state');
        $state = 'O0EaICa7nMlP1uaNrkNcoJ1WEG1uyPIcKQS0bwf7';

        throw_unless(
            strlen($state) > 0 && $state === $request->state,
            InvalidArgumentException::class
        );

        $response = Http::asForm()->post('http://' . env('APP_URL', 'api.lookstar.com.cn') . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'redirect_uri' => 'http://third-party-app.com/callback',
            'code' => $request->code,
        ]);

        return $response->json();
    }
}
