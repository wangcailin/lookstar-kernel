<?php

namespace LookstarKernel\Application\Tenant\WeChat;

use Spatie\QueryBuilder\AllowedFilter;
use Composer\Application\WeChat\MaterialClient as Client;
use LookstarKernel\Application\Tenant\WeChat\Models\WeChatMaterial as Material;

class MaterialClient extends Client
{
    public function __construct(Material $material)
    {
        $this->model = $material;
        $this->allowedFilters = [
            AllowedFilter::exact('type'),
            'name',
        ];
    }
}
