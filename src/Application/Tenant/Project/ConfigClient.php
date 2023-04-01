<?php

namespace LookstarKernel\Application\Tenant\Project;

use LookstarKernel\Application\Tenant\Project\Models\Config;
use LookstarKernel\Application\Tenant\Project\Traits\HasOneGet;
use Composer\Http\Controller;
use Illuminate\Http\Request;

class ConfigClient extends Controller
{
    use HasOneGet;
    public function __construct(Config $config)
    {
        $this->model = $config;
    }

    public function updateOrCreate(Request $request)
    {
        $input = $request->all();
        $row = $this->model->firstOrCreate(['project_id' => $input['project_id']]);
        $fields = ['title', 'color', 'is_banner', 'share', 'logo', 'form', 'banner', 'extend'];
        foreach ($fields as $field) {
            if (isset($input['data'][$field])) {
                $row->update(["data->{$field}" => $input['data'][$field]]);
            }
        }
        return $this->success($this->row);
    }
}
