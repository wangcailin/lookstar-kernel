<?php

namespace LookstarKernel\Application\Central\Tenant;

use Composer\Http\Controller;

use LookstarKernel\Application\Central\Tenant\Models\Tenant;
use LookstarKernel\Application\Tenant\Auth\Models\User;

class Client extends Controller
{
    public function __construct(Tenant $tenant)
    {
        $this->model = $tenant;
    }

    public function handleCreate()
    {
        $this->row = $this->model::create($this->data);
        tenancy()->initialize($this->row);
        User::createAdminUser('admin@' . $this->data['domain'], 'Lookstar@2023');
    }
}
