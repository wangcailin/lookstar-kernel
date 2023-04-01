<?php

namespace LookstarKernel\Application\Tenant\Project\MicroBook;

use LookstarKernel\Application\Tenant\Project\MicroBook\Models\Banner;
use Spatie\QueryBuilder\AllowedFilter;
use Composer\Http\Controller;

class BannerClient extends Controller
{
    public function __construct(Banner $banner)
    {
        $this->model = $banner;

        $this->allowedIncludes = ['freepublish'];

        $this->allowedFilters = [
            AllowedFilter::exact('project_id'),
        ];

        $this->defaultSorts = 'sort';

        $this->validateCreateRules = [
            'project_id' => ['numeric'],
        ];
    }

    public function handleCreate()
    {
        $projectId = $this->data['project_id'];
        if ($this->data['data']) {
            $updateIds = [];
            foreach ($this->data['data'] as $key => $value) {
                $value['sort'] = $key;
                $value['project_id'] = $projectId;
                if (isset($value['id'])) {
                    $updateIds[] = $value['id'];
                    $this->model::find($value['id'])->update($value);
                } else {
                    $updateIds[] = $this->model::create($value)['id'];
                }
            }
            $this->model::where('project_id', $projectId)->whereNotIn('id', $updateIds)->delete();
        } else {
            $this->model::where('project_id', $projectId)->delete();
        }
        return $this->success();
    }
}
