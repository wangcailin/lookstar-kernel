<?php

namespace LookstarKernel\Application\Tenant\Push\Mail;

use LookstarKernel\Application\Tenant\Push\Mail\Models\Template;
use LookstarKernel\Application\Tenant\System\Models\Config;
use Composer\Http\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TemplateClient extends Controller
{
    public function __construct(Template $template)
    {
        $this->model = $template;

        $this->validateRules = [
            'title' => ['required', 'max:128'],
            'subject' => ['required', 'max:128'],
            'type' => ['required', Rule::in([1, 2])],
        ];

        $this->validateCreateRules = [
            'file' => ['exclude_unless:type,1', 'required', 'file', 'mimetypes:text/html', 'max:100'],
        ];
    }

    public function afterBuildFilter()
    {
        $this->model->select([
            'id',
            'type',
            'title',
            'subject',
            'body',
            'created_at'
        ])->withCount([
            'task as task_cnt',
            'task as task_success_cnt' => function (Builder $query) {
                $query->where(['status' => 3, 'send_status' => 2]);
            },
            'task as task_processing_cnt' => function (Builder $query) {
                $query->where(['status' => 1]);
            },
        ]);
    }

    public function beforeCreate()
    {
        $this->data = request()->all();
        $this->handleCreateValidate();
        if ($this->data['type'] == 1) {
            $html = request()->file('file')->get();
            $this->data['body'] = $html;
        }
    }

    public function uploadHtml($id, Request $request)
    {
        $request->validate($this->validateCreateRules);
        $html = $request->file('file')->get();
        $row =  $this->model->findOrFail($id);
        $row->update(['body' => $html]);
        return $this->success($row);
    }

    public function updateEdit($id, Request $request)
    {
        $input = $request->validate([
            'body' => 'required',
            'edit_json' => 'required',
        ]);
        $row =  $this->model->findOrFail($id);
        $row->update($input);
        return $this->success($row);
    }

    public function preview($id, Request $request)
    {
        $input = $request->validate([
            'email' => ['required', 'array', 'min:1']
        ]);

        $config = Config::getMailConfig();
        $template  = $this->model::findOrFail($id);
        Job::dispatch($config, $input['email'], $template['subject'], $template['body'])->onQueue('mail');
    }
}
