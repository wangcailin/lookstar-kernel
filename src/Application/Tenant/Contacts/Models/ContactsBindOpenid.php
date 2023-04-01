<?php

namespace LookstarKernel\Application\Tenant\Contacts\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class ContactsBindOpenid extends Model
{
    protected $table = 'tenant_contacts_bind_openid';

    protected $fillable = [
        'phone',
        'openid',
    ];

    public static function bind($phone, $openid)
    {
        $data = [
            'phone' => $phone,
            'openid' => $openid
        ];
        if ($row = self::where($data)->first()) {
            $row->updated_at = date('Y-m-d H:i:s');
            return $row->save();
        } else {
            return self::create($data);
        }
    }
}
