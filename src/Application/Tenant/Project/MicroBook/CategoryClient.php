<?php

namespace LookstarKernel\Application\Tenant\Project\MicroBook;

use LookstarKernel\Application\Tenant\Project\MicroBook\Models\Article;
use LookstarKernel\Application\Tenant\Project\MicroBook\Models\Category;
use Spatie\QueryBuilder\AllowedFilter;
use Composer\Http\Controller;

class CategoryClient extends Controller
{
    public function __construct(Category $category)
    {
        $this->model = $category;
        $this->allowedFilters = [
            AllowedFilter::exact('project_id'),
            AllowedFilter::exact('state'),
        ];

        $this->defaultSorts = 'sort';

        $this->allowedIncludes = ['article.freepublish'];

        $this->validateCreateRules = [
            'project_id' => ['numeric'],
        ];
    }

    public function handleCreate()
    {
        $projectId = $this->data['project_id'];
        if ($this->data['category']) {
            $updateIds = [];
            foreach ($this->data['category'] as $key => $value) {
                $value['sort'] = $key;
                $value['project_id'] = $projectId;
                $freepublishIds = $value['freepublish_ids'];
                if (isset($value['id'])) {
                    $updateIds[] = $value['id'];
                    $this->model::where('id', $value['id'])->update(['name' => $value['name'], 'sort' => $value['sort']]);
                } else {
                    $value = $this->model::create($value);
                    $updateIds[] = $value['id'];
                }
                Article::createBatch($value['id'], $freepublishIds);
            }
            $this->model::where('project_id', $this->data['project_id'])->whereNotIn('id', $updateIds)->delete();
        } else {
            $this->model::where('project_id', $this->data['project_id'])->delete();
        }
        return $this->success();
    }
}
