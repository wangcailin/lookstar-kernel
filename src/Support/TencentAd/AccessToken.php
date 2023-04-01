<?php

namespace LookstarKernel\Support\TencentAd;

use TencentAds\TencentAds;
use TencentAds\Exception\TencentAdsResponseException;
use TencentAds\Exception\TencentAdsSDKException;

class AccessToken
{
    public static $tads;
    public static $CLIENT_ID          = 'YOUR CLIENT ID';
    public static $CLIENT_SECRET      = 'YOUR CLIENT SECRET';
    public static $AUTHORIZATION_CODE = 'YOUR AUTHORIZATION CODE';
    public static $REDIRECT_URI       = 'YOUR REDIRECT URI';

    public function init()
    {
        $tads = TencentAds::init([
            'is_debug'     => true,
        ]);
        $tads->useProduction(); // oauth/token不提供沙箱环境
        static::$tads = $tads;

        return $tads;
    }

    public function main()
    {
        try {
            /* @var TencentAds $tads */
            $tads = static::$tads;

            $token = $tads->oauth()
                ->token([
                    'client_id'          => static::$CLIENT_ID,
                    'client_secret'      => static::$CLIENT_SECRET,
                    'grant_type'         => 'authorization_code',
                    'authorization_code' => static::$AUTHORIZATION_CODE,
                    'redirect_uri'       => static::$REDIRECT_URI,
                ]);

            // 从返回里获得AccessToken并设置到$tads中
            $tads->setAccessToken($token->getAccessToken());
            // echo 'Access token expires in: ' . $token->getAccessTokenExpiresIn() . PHP_EOL;
            // echo 'Refresh token: ' . $token->getRefreshToken() . PHP_EOL;
            // echo 'Refresh token expires in: ' . $token->getRefreshTokenExpiresIn() . PHP_EOL;
        } catch (TencentAdsResponseException $e) {
            // When Api returns an error
            echo 'Tencent ads returned an error: ' . $e->getMessage() . PHP_EOL;
            throw $e;
        } catch (TencentAdsSDKException $e) {
            // When validation fails or other local issues
            echo 'Tencent ads SDK returned an error: ' . $e->getMessage() . PHP_EOL;
            throw $e;
        } catch (Exception $e) {
            echo 'Other exception: ' . $e->getMessage() . PHP_EOL;
            throw $e;
        }
    }
}
