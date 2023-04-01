<?php

namespace LookstarKernel\Application\Tenant\Group\Models\Analytics;

use LookstarKernel\Application\Tenant\Contacts\Models\Analytics\DimContacts;

class UserInfoMobile extends DimContacts
{
    public function group()
    {
        return $this->hasOne(AdsGroupUserInfo::class, 'mobile', 'phone');
    }
}
