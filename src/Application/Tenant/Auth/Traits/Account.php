<?php

namespace LookstarKernel\Application\Tenant\Auth\Traits;

trait Account
{
    private static function handleCreateUser($username, $password, $email = '', $phone = '', $nickname = '', $isAdmin)
    {
        $nickname = $nickname !== '' ?: $username;
        return self::create([
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'phone' => $phone,
            'nickname' => $nickname,
            'is_admin' => $isAdmin,
            'tenant_id' => 'ab1d620e-a150-4a88-a872-5270f55f8a2a'
        ]);
    }
    public static function createAdminUser($username, $password, $email = '', $phone = '', $nickname = '')
    {
        return self::handleCreateUser($username, $password, $email = '', $phone = '', $nickname = '', 1);
    }
    public static function createUser($username, $password, $email = '', $phone = '', $nickname = '')
    {
        return self::handleCreateUser($username, $password, $email = '', $phone = '', $nickname = '', 0);
    }
}
