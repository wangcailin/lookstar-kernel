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
     * 查询的时候是否启用 部门权限字段
     */
    public $departmentPermission = true;
    public $departmentIdField = 'role_id';
    public $departmentIdFieldIsJson = 0;
    public $originalModel;

    /**
     * 给 filter 添加部门权限
     *
     * @return void
     */
    public function beforeBuildFilter()
    {
        $this->queryDepartmentPermission();
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
        if ($this->departmentPermission) {
            $this->createDepartmentId();
        }
    }

    /**
     * 更新数据 前置方法
     *
     * @return void
     */
    public function beforeUpdate()
    {
        $user = $this->getCurrentUser();
        if (!$user->is_admin) {
            $departmentId = $this->getUserDepartmentId($user);
            if ($this->departmentIdFieldIsJson) {
                $this->model = $this->model->whereJsonContains($this->departmentIdField, $departmentId);
            } else {
                $this->model = $this->model->where($this->departmentIdField, $departmentId);
            }
        }
    }

    /**
     * 删除数据 核心方法 验证部门
     *
     * @return void
     */
    public function beforeDelete()
    {
        $user = $this->getCurrentUser();
        if (!$user->is_admin) {
            $departmentId = $this->getUserDepartmentId($user);
            if ($this->departmentIdFieldIsJson) {
                $this->model = $this->model->whereJsonContains($this->departmentIdField, $departmentId);
            } else {
                $this->model = $this->model->where($this->departmentIdField, $departmentId);
            }
        }
    }

    /**
     * 获取单个数据 格式化数据
     *
     * @return void
     */
    public function getDataTransform()
    {
        if ($this->authUserId) {
            $this->createAuthUserId();
        }
        if ($this->departmentPermission) {
            $this->createDepartmentId();
            $this->queryDepartmentPermission();
        }
    }


    /**
     * 创建数据时，添加部门ID
     *
     * @return void
     */
    public function createDepartmentId()
    {
        if ($this->departmentIdFieldIsJson) {
            $this->data[$this->departmentIdField][] = $this->getUserDepartmentId();
        } else {
            $this->data[$this->departmentIdField] = $this->getUserDepartmentId();
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
     * 查询同部门（角色）的数据
     *
     * @return void
     */
    public function queryDepartmentPermission()
    {
        if ($this->departmentPermission) {
            $user = $this->getCurrentUser();
            if (!($user['is_admin'] ?? '')) {
                //非管理员  同部门的用户
                $departmentId = $this->getUserDepartmentId($user);
                if ($departmentId) {
                    if ($this->departmentIdFieldIsJson) {
                        $this->model = $this->model->whereJsonContains($this->departmentIdField, $departmentId);
                    } else {
                        $this->model = $this->model->where($this->departmentIdField, $departmentId);
                    }
                }
            }
        }
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

    /**
     * 获得登录用户的部门（角色）ID
     *
     * @return void
     */
    public function getUserDepartmentId($user = '')
    {
        $departmentId = '';
        if (!$user) {
            $user = $this->getCurrentUser();
        }
        if ($user && $user->roles) {
            $departmentId = $user->roles->first()->id;
        }
        return $departmentId;
    }

    /**
     * 获得验证唯一字段的规则
     *
     * @param [type] $modelString
     * @param [type] $validateField
     * @return void
     */
    public function getUniqueFieldValidate($modelString, $validateField)
    {
        $departmentId = $this->getUserDepartmentId();
        $currentUser = $this->getCurrentUser();
        $isAdmin = $currentUser ? $currentUser->is_admin : 0;
        $isJsonType = $this->departmentIdFieldIsJson;
        return
            tenant()->unique($modelString, $validateField)
            ->when(!$isAdmin, function ($query) use ($departmentId, $isJsonType) {
                if ($isJsonType) {
                    $query->whereJsonContains($this->departmentIdField, $departmentId);
                } else {
                    $query->where($this->departmentIdField, $departmentId);
                }
            });
    }

    /**
     * 验证更新数据
     *
     * @param [type] $modelString
     * @param [type] $validateField
     * @return void
     */
    public function getUpdateUniqueFieldValidate($modelString, $validateField)
    {
        $departmentId = $this->getUserDepartmentId();
        $currentUser = $this->getCurrentUser();
        $isAdmin = $currentUser ? $currentUser->is_admin : 0;
        $isJsonType = $this->departmentIdFieldIsJson;
        return
            tenant()->unique($modelString, $validateField)
            ->ignore($this->id)
            ->when(!$isAdmin, function ($query) use ($departmentId, $isJsonType) {
                if ($isJsonType) {
                    $query->whereJsonContains($this->departmentIdField, $departmentId);
                } else {
                    $query->where($this->departmentIdField, $departmentId);
                }
            });
    }
}
