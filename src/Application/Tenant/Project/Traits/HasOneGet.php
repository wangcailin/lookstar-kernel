<?php

namespace LookstarKernel\Application\Tenant\Project\Traits;

trait HasOneGet
{
    public function get($id)
    {
        $row = $this->model->firstWhere('project_id', $id);
        return $this->success($row);
    }
}
