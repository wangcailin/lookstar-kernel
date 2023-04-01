<?php

namespace LookstarKernel\Application\Tenant\Auth\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

use LookstarKernel\Application\Tenant\Auth\Traits\Account;
use Composer\Support\Auth\Traits\Model\Bind;
use Composer\Support\Auth\Traits\Model\Attribute;
use Composer\Support\Captcha\Client as CaptchaClient;

class User extends Authenticatable
{
    use BelongsToTenant;

    use Notifiable;
    use HasRoles;
    use HasApiTokens;

    use Bind;
    use Account;
    use Attribute;


    protected $table = 'tenant_auth_user';
    protected $guarded = [];

    protected $fillable = [
        'tenant_id',
        'nickname',
        'username',
        'password',
        'phone',
        'email',
        'openid',
        'loginfail_time',
        'loginfail_count',
        'logintime',
        'is_admin',
        'is_active',
        'avatar'
    ];

    protected $hidden = [
        'password',
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

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

    public static function verifyAdminCode($action, $code)
    {
        $adminUser = self::firstWhere('is_admin', 1);
        $adminUser->plaintext_phone;
        CaptchaClient::verifySmsCode($adminUser->plaintext_phone, $action, $code);
    }
}
