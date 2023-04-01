<?php

namespace LookstarKernel\Support\HubSpot;

use LookstarKernel\Application\Tenant\System\Models\Config;
use HubSpot\Factory;
use HubSpot\Client\Auth\OAuth\ApiException;
use LookstarKernel\Support\HubSpot\Traits\Base;
use LookstarKernel\Support\HubSpot\Traits\AccessToken;

class Client
{
    use Base;
    use AccessToken;

    public static $client;

    public static function oauth($code)
    {
        $oauthClient = Factory::create()->auth()->oAuth();
        try {
            $apiTokenResponse = $oauthClient->tokensApi()->createToken('authorization_code', $code, self::getRedirectUri(), self::getClientId(), self::getClientSecret());
            self::setAccessToken($apiTokenResponse);

            $apiResponse = $oauthClient->accessTokensApi()->getAccessToken($apiTokenResponse['access_token']);
            Config::updateOrCreate(['type' => 'hubspot'], ['data' => [
                'user' => $apiResponse['user'],
                'user_id' => $apiResponse['user_id'],
                'app_id' => $apiResponse['app_id'],
                'hub_id' => $apiResponse['hub_id'],
                'hub_domain' => $apiResponse['hub_domain'],
                'refresh_token' => $apiTokenResponse['refresh_token'],
            ]]);
        } catch (ApiException $e) {
            echo "Exception when calling tokens_api->create_token: ", $e->getMessage();
        }
    }

    public static function createFactory()
    {
        if (!self::$client) {
            $accessToken = self::getAccessToken();
            self::$client = Factory::createWithAccessToken($accessToken);
        }
        return self::$client;
    }

    public function getProperties()
    {
        self::createFactory()->crm()->properties()->coreApi()->getAll('');
    }
}
