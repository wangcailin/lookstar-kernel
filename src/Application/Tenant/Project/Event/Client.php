<?php

namespace LookstarKernel\Application\Tenant\Project\Event;

use LookstarKernel\Application\Tenant\Project\Event\Models\Event;
use LookstarKernel\Application\Tenant\Project\Event\Models\Info;
use Composer\Http\Controller;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;

class Client extends Controller
{
    public function __construct(Event $event)
    {
        $this->model = $event;
        $this->allowedFilters = [
            AllowedFilter::exact('project_id'),
            'title',
        ];

        $this->allowedSorts = ['state', 'created_at'];

        $this->validateRules = [
            'project_id' => 'required',
            'type' => 'required',
            'title' => [
                'required',
            ],
            'start_time' => 'required',
            'end_time' => 'required',
        ];
    }

    public function getInfo($id)
    {
        $row = Info::where('event_id', $id)->first();
        return $this->success($row);
    }

    public function updateOrCreateInfo($id, Request $request)
    {
        $validateData = $request->validate([
            'status' => 'required',
            'content' => 'required',
        ]);
        $row = Info::updateOrCreate(['event_id' => $id], ['content' => $validateData['content']]);
        return $this->success($row);
    }
}
