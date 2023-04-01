<?php

namespace LookstarKernel\Application\Tenant\Project\MicroBook;

use LookstarKernel\Application\Tenant\Project\MicroBook\Models\Article;
use Spatie\QueryBuilder\AllowedFilter;
use Composer\Http\Controller;

class ArticleClient extends Controller
{
    public function __construct(Article $article)
    {
        $this->model = $article;

        $this->allowedIncludes = ['freepublish'];

        $this->allowedFilters = [
            AllowedFilter::exact('category_id'),
        ];

        $this->defaultSorts = 'sort';

        $this->validateCreateRules = [
            'ids' => ['required', 'array'],
            'category_id' => ['required', 'numeric'],
        ];
    }

    public function afterGet()
    {
        $this->row->freepublish;
    }

    public function handleCreate()
    {
        $this->model::where('category_id', $this->data['category_id'])->delete();
        foreach ($this->data['ids'] as $key => $id) {
            $this->model::create([
                'category_id' => $this->data['category_id'],
                'freepublish_id' => $id,
                'sort' => $key
            ]);
        }
        return $this->success($this->row);
    }
}
