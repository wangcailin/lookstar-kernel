<?php

namespace LookstarKernel\Application\Tenant\Contacts;

use Illuminate\Validation\Rule;
use LookstarKernel\Application\Tenant\Contacts\Models\Field;
use LookstarKernel\Application\Central\Contacts\Models\FieldRule;
use Composer\Http\Controller;
use Illuminate\Http\Request;

class FieldClient extends Controller
{
    public function __construct(Field $field)
    {
        $this->model = $field;

        $this->allowedFilters = ['label'];
        $this->allowedIncludes = ['rule'];
        $this->allowedSorts = ['created_at'];

        $this->validateCreateRules = [
            'form_type' => [
                'required',
                Rule::in(Field::$EnumFormType),
            ],
            'label' => ['required', 'string', 'max:64'],
            'placeholder' => ['sometimes', 'string', 'max:64'],
        ];
    }

    public function update($name)
    {
        $this->data = request()->only(['label', 'rule_name', 'options', 'placeholder']);
        $this->row = $this->model::firstWhere('name', $name);
        $this->row->update($this->data);
        $this->afterUpdate();
        return $this->success($this->row);
    }

    public function getSelectList()
    {
        $this->buildFilter();
        $this->list = $this->model->whereNotIn('name', ['country', 'province', 'city', 'district', 'full_address'])->select('name as value', 'label', 'type')->get();
        return $this->success($this->list);
    }

    public function getRuleList()
    {
        $this->list = FieldRule::get();
        return $this->success($this->list);
    }

    public function getSelectRuleList(Request $request)
    {
        $name = $request->input('name');
        $systemFieldsRule = $this->model::$SystemFieldsRule;
        $this->list = FieldRule::select('name as value', 'label');
        if (in_array($name, ['phone', 'email'])) {
            $this->list->whereIn('name', $systemFieldsRule[$name]);
        }
        return $this->success($this->list->get());
    }

    public function get($name)
    {
        $this->row = $this->model::firstWhere('name', $name);
        return $this->success($this->row);
    }

    public function getSystemFieldsRule()
    {
        $list = $this->model::$SystemFieldsRule;
        return $this->success($list);
    }
}
