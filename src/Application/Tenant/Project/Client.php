<?php

namespace LookstarKernel\Application\Tenant\Project;

use LookstarKernel\Application\Tenant\Project\Models\Project;
use Composer\Http\Controller;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Validator;

class Client extends Controller
{
    public function __construct(Project $project)
    {
        $this->model = $project;
        $this->allowedFilters = [
            'title',
            AllowedFilter::exact('type'),
            AllowedFilter::exact('state'),
        ];

        $this->allowedSorts = ['state', 'created_at'];

        $this->validateMessage = [
            'unique' => '请输入唯一的项目名',
        ];
    }

    public function handleValidate()
    {
        Validator::make(
            $this->data,
            [
                'title' => [
                    'required',
                    tenant()->unique('LookstarKernel\Application\Tenant\Project\Models\Project', 'title'),
                ],
                'type' => 'required',
            ],
            $this->validateMessage
        )->validate();
    }

    public function handleUpdateValidate()
    {
        Validator::make(
            $this->data,
            ['title' => [
                'required',
                tenant()->unique('LookstarKernel\Application\Tenant\Project\Models\Project', 'title')->ignore($this->id),
            ]],
            $this->validateMessage
        )->validate();
    }

    public function getUuid($uuid)
    {
        $uuid = preg_match('/^[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}$/', $uuid) ? $uuid : null;
        if ($uuid) {
            $this->beforeGet();
            $this->row = $this->model->where('uuid', $uuid)->first();
            $this->afterGet();
        }
        return $this->success($this->row);
    }

    public function getCount()
    {
        $sum = $this->model->count();
        $processing = $this->model->where('state', true)->count();
        return $this->success([
            'sum' => $sum,
            'processing' => $processing,
        ]);
    }
}
