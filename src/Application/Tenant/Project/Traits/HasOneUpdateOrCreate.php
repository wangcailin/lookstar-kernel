<?php

namespace LookstarKernel\Application\Tenant\Project\Traits;

use Illuminate\Http\Request;

trait HasOneUpdateOrCreate
{
    public function updateOrCreate(Request $request)
    {
        $input = $request->all();
        $this->row = $this->model->updateOrCreate(['project_id' => $request['project_id']], $input);
        return $this->success($this->row);
    }
}
