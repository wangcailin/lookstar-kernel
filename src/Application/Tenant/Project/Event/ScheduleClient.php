<?php

namespace LookstarKernel\Application\Tenant\Project\Event;

use LookstarKernel\Application\Tenant\Project\Event\Models\Schedule;
use Composer\Http\Controller;
use Spatie\QueryBuilder\AllowedFilter;

class ScheduleClient extends Controller
{
    public function __construct(Schedule $schedule)
    {
        $this->model = $schedule;
        $this->allowedFilters = [
            AllowedFilter::exact('event_id'),
        ];
    }
}
