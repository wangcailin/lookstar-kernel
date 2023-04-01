<?php

namespace LookstarKernel\Application\Tenant\Project\Event;

use LookstarKernel\Application\Tenant\Project\Event\Models\Speaker;
use Composer\Http\Controller;
use Spatie\QueryBuilder\AllowedFilter;

class SpeakerClient extends Controller
{
    public function __construct(Speaker $speaker)
    {
        $this->model = $speaker;
        $this->allowedFilters = [
            AllowedFilter::exact('event_id'),
        ];
    }
}
