<?php

namespace LookstarKernel\Application\Tenant\Group;

use LookstarKernel\Application\Tenant\Group\Models\Group;
use Composer\Http\Controller;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Validator;
use LookstarKernel\Application\Tenant\Group\Models\Analytics\AnalyticsOverview;

class Client extends Controller
{
    public function __construct(Group $group)
    {
        $this->model = $group;
        $this->allowedFilters = [
            'title',
            AllowedFilter::exact('type'),
        ];

        $this->validateMessage = [
            'unique' => '请输入唯一的分组名',
        ];
    }

    public function handleValidate()
    {
        Validator::make(
            $this->data,
            [
                'title' => [
                    'required',
                    tenant()->unique(Group::class, 'title'),
                ],
                'type' => 'required',
            ],
            $this->validateMessage
        )->validate();
    }

    public function handleUpdateValidate()
    {
        Validator::make(
            $this->data,
            [
                'title' => [
                    'required',
                    tenant()->unique(Group::class, 'title')->ignore($this->id),
                ],
                'filter' => ['required', 'array', 'min:1']
            ],
            $this->validateMessage
        )->validate();
    }

    public function performBuildFilter()
    {
        $this->model->select(['id', 'type', 'title', 'total', 'created_at', 'updated_at']);
    }

    public function beforeUpdate()
    {
        $this->data = request()->all();
        $this->handleUpdateValidate();

        $this->row = $this->model::findOrFail($this->id);

        $analyticsOverview = new AnalyticsOverview();
        $total = $analyticsOverview->saveList($this->data, $this->id);

        $this->data['total'] = $total;
    }

    public function getSelectList()
    {
        $this->buildFilter();
        $this->list = $this->model->select('id as value', 'title as label')->get();
        return $this->success($this->list);
    }

    public function total(Request $request, AnalyticsOverview $analyticsOverview)
    {
        $input = $request->validate([
            'filter' => ['required', 'array', 'min:1'],
        ]);
        $data = $analyticsOverview->queryTotal($input);
        return $this->success($data);
    }

    public function getTotal($id, AnalyticsOverview $analyticsOverview)
    {
        $row = $this->model->findOrFail($id);
        if ($row['filter'] && $row['type'] == '1') {
            $total = $analyticsOverview->saveList(['filter' => $row['filter']], $id);
            $row->update(['total' => $total]);
            return $this->success($total);
        }
        return $this->success();
    }

    public function afterDelete()
    {
        $analyticsOverview = new AnalyticsOverview();
        $analyticsOverview->deleteGroup($this->id);
    }
}
