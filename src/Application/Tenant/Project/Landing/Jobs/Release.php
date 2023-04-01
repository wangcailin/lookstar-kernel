<?php

namespace LookstarKernel\Application\Tenant\Project\Landing\Jobs;

use LookstarKernel\Application\Central\Tenant\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Storage;
use LookstarKernel\Application\Tenant\Project\Models\Project;
use Composer\Support\Aliyun\CodeupClient;
use Composer\Support\Aliyun\DevopsClient;
use LookstarKernel\Application\Tenant\Project\Landing\Models\ReleaseRuntime;
use Illuminate\Support\Facades\Log;

class Release implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $uploadFilePath;
    public $projectId;
    public $tenantId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($projectId, $uploadFilePath)
    {
        if ($projectId && $uploadFilePath) {
            $this->projectId = $projectId;
            $this->uploadFilePath = $uploadFilePath;
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->tenantId = $this->job->payload()['tenant_id'];

        $tenant = Tenant::find($this->tenantId);

        $tenant->run(function () {
            $storage = Storage::disk('uploads');
            $project = Project::firstWhere('id', $this->projectId);

            // 解压文件
            $this->unzip($storage, $project['uuid']);

            // 上传代码库
            $this->uploadCodeFile($this->projectId, $project['uuid'], $storage);

            // 更新状态
            $this->changeReleaseRuntime($this->projectId, 2);
        });
    }

    /**
     * 解压文件
     */
    private function unzip($storage, $uuid)
    {
        $zip = new \ZipArchive();
        // 解压landing下载文件
        $zip->open($this->uploadFilePath);
        $zip->extractTo($storage->path($uuid));
        $zip->close();
    }

    /**
     * 上传代码库
     */
    private function uploadCodeFile($id, $uuid, $storage)
    {
        $files = $storage->allFiles($uuid);

        $codeupClient = new CodeupClient();
        $codeupClient->deleteBranch($uuid);
        $codeupClient->createBranch(['branch_name' => $uuid, 'ref' => 'master']);

        foreach ($files as $key => $value) {
            $body = [
                'branch_name' => $uuid,
                'commit_message' => $this->tenantId,
                'content' => $storage->get($value),
                'file_path' => 'src/pages/' . str_replace($uuid . '/', '', $value),
            ];
            $codeupClient->createFile($body);
        }
        $devopsClient = new DevopsClient();
        $params = [
            'params' => [
                'envs' => [
                    'tenant_id' => tenant('id'),
                    'app_id' => $id
                ],
                'runningBranchs' => [
                    'https://codeup.aliyun.com/61b2f0aba9dfa18f9b46a3ad/lookstar/lookstar-landing-build.git' => $uuid
                ]
            ]
        ];
        $devopsClient->startPipelineRun($params);
    }

    private function changeReleaseRuntime($projectId, $state)
    {
        ReleaseRuntime::where('project_id', $projectId)->update(['state' => $state]);
    }
}
