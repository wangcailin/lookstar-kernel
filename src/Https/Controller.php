<?php

namespace LookstarKernel\Https;

use Illuminate\Support\Facades\Auth;
use Composer\Http\Controller as ComposerController;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class Controller extends ComposerController
{
    /**
     * 查询的时候是否启用 部门权限字段
     */
    public $departmentPermission = true;
    public $departmentIdField = 'department_id';

    /**
     * 给 filter 添加部门权限
     *
     * @return void
     */
    public function buildFilter()
    {
        if ($this->departmentPermission) {
            $this->queryDepartmentPermission();
        }
        $this->model = QueryBuilder::for($this->model)
            ->defaultSorts($this->defaultSorts)
            ->allowedFilters($this->allowedFilters)
            ->allowedSorts($this->allowedSorts)
            ->allowedIncludes($this->allowedIncludes);
    }

    /**
     * 创建数据
     *
     * @return void
     */
    public function create()
    {
        $this->data = request()->all();
        if ($this->authUserId) {
            $this->createAuthUserId();
        }
        if ($this->departmentPermission) {
            $this->createDepartmentId();
        }

        $this->handleCreateValidate();

        $this->beforeCreate();
        $this->handleCreate();
        $this->afterCreate();

        return $this->success($this->row);
    }

    /**
     * 更新数据 核心方法
     *
     * @return void
     */
    public function handleUpdate()
    {
        $user = $this->getCurrentUser();
        if ($user->is_admin) {
            $this->row = $this->model::findOrFail($this->id);
        } else {
            $departmentId = $this->getUserDepartmentId($user);
            $this->row = $this->model::where($this->departmentIdField, $departmentId)->findOrFail($this->id);
        }

        if ($this->row) {
            $this->row->update($this->data);
        }
    }

    /**
     * 删除数据 核心方法 验证部门
     *
     * @return void
     */
    public function handleDelete()
    {
        $user = $this->getCurrentUser();
        if ($user->is_admin) {
            $this->model::findOrFail($this->id)->delete();
        } else {
            $departmentId = $this->getUserDepartmentId($user);
            $this->model::where($this->departmentIdField, $departmentId)->findOrFail($this->id)->delete();
        }
    }

    /**
     * 获取单行数据
     *
     * @param [type] $id
     * @return void
     */
    public function get($id)
    {
        $this->id = $id;

        if ($this->authUserId) {
            $this->createAuthUserId();
        }
        if ($this->departmentPermission) {
            $this->createDepartmentId();
            $this->queryDepartmentPermission();
        }

        $this->beforeGet();
        $this->handleGet();
        $this->afterGet();

        return $this->success($this->row);
    }


    /**
     * 创建数据时，添加部门ID
     *
     * @return void
     */
    public function createDepartmentId()
    {
        $this->data[$this->departmentIdField] = $this->getUserDepartmentId();
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
        $user = $this->getCurrentUser();
        if ($this->departmentPermission && !($user['is_admin'] ?? '')) {
            //非管理员  同部门的用户
            $departmentId = $this->getUserDepartmentId($user);
            if ($departmentId) {
                $this->model = $this->model->where('department_id', $departmentId);
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
        $userId = Auth::id();
        $cacheKey = 'backendUser_' . $userId;
        $user = Cache::get($cacheKey);
        if (!$user) {
            if ($this->guard) {
                $user = Auth::guard($this->guard)->user();
            } else {
                $user = Auth::user();
            }
            Cache::put($cacheKey, $user, now()->addMinutes(30));
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
        if ($user->roles) {
            $departmentId = $user->roles->first()->id;
        }
        return $departmentId;
    }

    /**
     * 验证创建数据
     *
     * @param [type] $modelString
     * @param [type] $validateField
     * @return void
     */
    public function doValidate($modelString, $validateField)
    {
        $departmentId = $this->getUserDepartmentId();
        $currentUser = $this->getCurrentUser();
        $isAdmin = $currentUser ? $currentUser->is_admin : 0;
        Validator::make(
            $this->data,
            [
                $validateField => [
                    'required',
                    tenant()->unique($modelString, $validateField)
                        ->when(!$isAdmin, function ($query) use ($departmentId) {
                            $query->where($this->departmentIdField, $departmentId);
                        }),
                ],
                'type' => 'required',
            ],
            $this->validateMessage
        )->validate();
    }

    /**
     * 验证更新数据
     *
     * @param [type] $modelString
     * @param [type] $validateField
     * @return void
     */
    public function doUpdateValidate($modelString, $validateField)
    {
        $departmentId = $this->getUserDepartmentId();
        $currentUser = $this->getCurrentUser();
        $isAdmin = $currentUser ? $currentUser->is_admin : 0;
        Validator::make(
            $this->data,
            [
                $validateField => [
                    'required',
                    tenant()->unique($modelString, $validateField)
                        ->ignore($this->id)
                        ->when(!$isAdmin, function ($query) use ($departmentId) {
                            $query->where($this->departmentIdField, $departmentId);
                        }),
                ]
            ],
            $this->validateMessage
        )->validate();
    }
}
