<?php

namespace LookstarKernel\Application\Tenant\Project\Landing;

use LookstarKernel\Application\Tenant\Project\Landing\Models\EditRuntime;
use Composer\Http\Controller;
use Spatie\QueryBuilder\AllowedFilter;

class EditRuntimeClient extends Controller
{
    public function __construct(EditRuntime $editRuntime)
    {
        $this->model = $editRuntime;
        $this->defaultSorts = '-created_at';
        $this->allowedFilters = [AllowedFilter::exact('project_id')];

        $this->validateRules = [
            'project_id' => ['numeric'],
        ];
    }

    public function get($id)
    {
        $this->row = $this->model->where('project_id', $id)->orderBy('created_at', 'DESC')->first();
        return $this->success($this->row);
    }
}
