<?php

namespace LookstarKernel\Application\Tenant\Project\Landing;

use LookstarKernel\Application\Tenant\Project\Landing\Models\ReleaseRuntime;
use Composer\Http\Controller;
use LookstarKernel\Application\Tenant\Project\Traits\HasOneUpdateOrCreate;
use LookstarKernel\Application\Tenant\Project\Traits\HasOneGet;

class ReleaseRuntimeClient extends Controller
{
    use HasOneUpdateOrCreate;
    use HasOneGet;
    public function __construct(ReleaseRuntime $releaseRuntime)
    {
        $this->model = $releaseRuntime;
    }
}
