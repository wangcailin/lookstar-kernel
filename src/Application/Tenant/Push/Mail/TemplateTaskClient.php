<?php

namespace LookstarKernel\Application\Tenant\Push\Mail;

use LookstarKernel\Application\Tenant\Group\Models\Group;
use LookstarKernel\Application\Tenant\Push\Mail\Models\TemplateTask;
use LookstarKernel\Application\Tenant\Group\Models\Analytics\AnalyticsOverview;
use LookstarKernel\Application\Tenant\Push\Abstracts\Task;
use LookstarKernel\Application\Tenant\Push\Mail\Models\Template;
use LookstarKernel\Application\Tenant\Push\Models\TaskUser;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class TemplateTaskClient extends Task
{
    public function __construct(TemplateTask $templateTask, TaskUser $taskUser)
    {
        parent::__construct();

        $this->userModel = $taskUser;
        $this->userModelTableName = $taskUser->getTable();
        $this->model = $templateTask;

        $this->validateCreateRules = [
            'template_id' => 'required',
            'title' => 'required',
        ];

        $this->allowedFilters = [
            AllowedFilter::exact('template_id'),
            AllowedFilter::exact('status'),
            'title',
        ];
    }

    protected function handleCustom($taskId)
    {
        $tenantId = tenant()->getTenantKey();
        DB::connection('data_warehouse')->statement(
            "REPLACE INTO {$this->userModelTableName} (tenant_id, task_id, task_type, `value`, created_at, updated_at)
                SELECT tenant_id, {$taskId} AS task_id, '{$this->model->taskType}' AS task_type, email AS `value`, '{$this->dateTime}' AS created_at, '{$this->dateTime}' AS updated_at
                FROM dim_tenant_contacts
                WHERE tenant_id = '{$tenantId}' AND email != '' AND email IS NOT NULL"
        );
    }

    protected function handleGroup($groupId, $taskId)
    {
        $group = Group::findOrFail($groupId);
        if ($group['type'] == 1) {
            $analyticsOverview = new AnalyticsOverview();
            $total = $analyticsOverview->saveList(['filter' => $group['filter']], $groupId);
            $group->update(['total' => $total]);
        }

        $task = $this->model->findOrFail($taskId);
        $template = Template::findOrFail($task['template_id']);
        $tenantId = tenant()->getTenantKey();
        DB::connection('data_warehouse')->statement(
            "REPLACE INTO {$this->userModelTableName} (tenant_id, task_id, task_type, `value`, created_at, updated_at)
                SELECT tenant_id, {$taskId} AS task_id, '{$this->model->taskType}' AS task_type,  email AS `value`, '{$this->dateTime}' AS created_at, '{$this->dateTime}' AS updated_at
                FROM ads_group_user_info
                WHERE tenant_id = '{$tenantId}' AND group_id =  {$group['id']}
                AND appid = '{$template['appid']}'"
        );
    }
}
