<?php

namespace LookstarKernel\Application\Tenant\Utm;

use LookstarKernel\Application\Tenant\Utm\Models\Utm;
use Composer\Http\Controller;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Validator;

class Client extends Controller
{
    public function __construct(Utm $utm)
    {
        $this->model = $utm;

        $this->allowedFilters = [
            AllowedFilter::exact('type'),
            AllowedFilter::exact('project_id'),
            AllowedFilter::exact('appid'),
            'utm_campaign',
            'utm_source',
            'utm_medium',
            'utm_term',
            'utm_content',
        ];

        $this->validateRules = [
            'type' => ['required', Rule::in(['inside', 'external'])],
            'utm_source' => ['required', 'max:100'],
            'utm_campaign' => ['required', 'max:100'],
        ];
    }

    public function getSelect($name)
    {
        Validator::make(
            ['name' => $name],
            [
                'name' => Rule::in([
                    'utm_campaign',
                    'utm_source',
                    'utm_medium',
                    'utm_term',
                    'utm_content',
                ]),
            ],
        )->validate();
        $list = $this->model::whereNotNull($name)->distinct($name)->select("{$name} as value", "{$name} as label")->get();
        return $this->success($list);
    }
}
