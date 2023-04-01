<?php

namespace LookstarKernel\Support\HubSpot\Traits;

use Illuminate\Support\Facades\Redis;
use HubSpot\Factory;
use HubSpot\Client\Auth\OAuth\ApiException;

trait AccessToken
{
    protected static $prefixKey = 'hubspot:access_token:';


    public static function getAccessToken()
    {
        $token = Redis::get(self::prefixKey());
        if ($token) {
            $token =  json_decode($token, true);
            if (time() >= $token['expires_in']) {
                $token = self::refreshTokens($token['refresh_token']);
                self::setAccessToken($token);
            }
            return $token['access_token'];
        }
    }

    protected static function setAccessToken($data)
    {
        $data['expires_in'] = time() + $data['expires_in'];
        Redis::set(self::prefixKey(), json_encode($data));
    }

    protected static function prefixKey()
    {
        return self::$prefixKey . tenant('id');
    }

    public static function refreshTokens($refreshToken)
    {
        try {
            $apiResponse = Factory::create()->auth()->oAuth()->tokensApi()->createToken('refresh_token', null, self::getRedirectUri(), self::getClientId(), self::getClientSecret(), $refreshToken);
            return $apiResponse;
        } catch (ApiException $e) {
            echo "Exception when calling tokens_api->create_token: ", $e->getMessage();
        }
    }
}
