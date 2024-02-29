<?php

namespace LookstarKernel\Https;

use Illuminate\Support\Facades\Auth;
use Composer\Http\Controller as ComposerController;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class Controller extends ComposerController
{

    /**
     * 数据是否绑定当前管理员部门权限ID
     */
    public $authRoleId = true;

    /**
     * 给 filter 添加部门权限
     *
     * @return void
     */
    public function beforeBuildFilter()
    {
        $this->queryAuthRoleId();
    }

    public function queryAuthRoleId()
    {
        if ($this->authRoleId) {
            $authRoleId = Auth::guard($this->guard)->roles[0]['id'];
            $this->model->where('auth_role_id', $authRoleId);
        }
    }

    /**
     * 创建数据 注入后台用户ID auth_role_id
     *
     * @return void
     */
    public function createAuthRoleId()
    {
        if ($this->guard) {
            $authRoleId = Auth::guard($this->guard)->roles[0]['id'];
            $this->data['auth_role_id'] = $authRoleId;
        }
    }

    /**
     * 格式化创建数据
     *
     * @return void
     */
    public function creatDataTransform()
    {
        if ($this->authUserId) {
            $this->createAuthUserId();
        }
        if ($this->authRoleId) {
            $this->createAuthRoleId();
        }
    }

    /**
     * 创建数据 注入后台用户ID auth_user_id
     *
     * @return void
     */
    public function createAuthUserId()
    {
        $user = $this->getCurrentUser();
        $this->data['auth_user_id'] = $user ? $user->id : '';
    }


    /**
     * 获得当前登录的用户 设置缓存
     *
     * @return
     */
    public function getCurrentUser()
    {
        if ($this->guard) {
            $user = Auth::guard($this->guard)->user();
        } else {
            $user = Auth::user();
        }
        return $user;
    }
}
