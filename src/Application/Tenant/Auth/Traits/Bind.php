<?php

namespace LookstarKernel\Application\Tenant\Auth\Traits;

use LookstarKernel\Application\Central\Tenant\Models\TenantUser;
use Composer\Exceptions\ApiErrorCode;
use Composer\Exceptions\ApiException;
use Composer\Support\Crypt\AES;
use Illuminate\Support\Facades\Auth;

/**
 * 绑定
 */
trait Bind
{
    public static function bindSms($phone)
    {
        list($user, $phone) = self::getBindUserValue($phone);
        if (self::firstWhere('phone', $phone) || TenantUser::firstWhere('phone', $phone)) {
            throw new ApiException('手机号已经被绑定', ApiErrorCode::ACCOUNT_BIND_PHONE_ERROR);
        }
        $user->phone = $phone;
        $user->save();
    }

    public static function bindEmail($email)
    {
        list($user, $email) = self::getBindUserValue($email);
        if (self::firstWhere('email', $email) || TenantUser::firstWhere('email', $email)) {
            throw new ApiException('邮箱已经被绑定', ApiErrorCode::ACCOUNT_BIND_EMAIL_ERROR);
        }
        $user->email = $email;
        $user->save();
    }


    private static function getBindUserValue($value)
    {
        $user = Auth::user();
        $value = AES::encode($value);
        return [$user, $value];
    }
}
