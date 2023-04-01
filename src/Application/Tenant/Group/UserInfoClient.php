<?php

namespace LookstarKernel\Application\Tenant\Group;

use LookstarKernel\Application\Tenant\Group\Models\Analytics\UserInfoMobile;
use Composer\Http\Controller;
use Composer\Support\Crypt\AES;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;

class UserInfoClient extends Controller
{
    public function __construct(UserInfoMobile $userInfoMobile)
    {
        $this->model = $userInfoMobile;
        $this->allowedFilters = [
            AllowedFilter::exact('group.group_id'),
            'full_name',
            'company',
            AllowedFilter::callback('phone', function (Builder $builder, $value) {
                if ($value) {
                    $builder->where('phone', AES::encode($value));
                }
            }),
            AllowedFilter::callback('email', function (Builder $builder, $value) {
                if ($value) {
                    $builder->where('email', AES::encode($value));
                }
            }),
        ];
    }

    public function afterBuildFilter()
    {
        $select  = ['id', 'phone', 'full_name', 'email', 'company', 'job', 'created_at'];
        $this->model->select($select);
    }
}
