<?php

namespace LookstarKernel\Application\Tenant\Project\Landing;

use LookstarKernel\Application\Tenant\Project\Landing\Jobs\Release;
use LookstarKernel\Application\Tenant\Project\Models\Project;
use LookstarKernel\Application\Tenant\Project\Landing\Models\ReleaseRuntime;
use LookstarKernel\Application\Tenant\Project\Landing\Models\EditRuntime;
use Composer\Http\Controller;
use Illuminate\Http\Request;
use Composer\Exceptions\ApiException;
use Composer\Exceptions\ApiErrorCode;
use Composer\Support\Aliyun\OssClient;
use LookstarKernel\Support\Aliyun\DevopsClient;

class Client extends Controller
{
    /**
     * 发布
     */
    // public function release(Request $request)
    // {
    //     Validator::make(
    //         $request->all(),
    //         [
    //             'project_id' => ['numeric'],
    //             'data' => 'required',
    //         ],
    //     );
    //     $data = $request->input('data');
    //     $projectId = $request->input('project_id');


    //     $project = Project::findOrFail($projectId);

    //     $this->publishHtml($project['uuid'], $data);

    //     ReleaseRuntime::where('project_id', $project['id'])->update(['state' => 3]);

    //     return $this->success();
    // }

    public function release(Request $request)
    {
        $validateData = $request->validate([
            'project_id' => 'numeric',
            'schema' => 'required',
        ]);

        $schema = $validateData['schema'];
        $projectId = $validateData['project_id'];

        $project = Project::findOrFail($projectId);

        $schema['meta']['name'] = $project['title'];
        $this->publishSchema($project['uuid'], $schema);

        $this->startPipline($project['uuid']);

        $this->changeReleaseRuntime($projectId, 1);

        return $this->success();
    }

    /**
     * https://api.lookstar.com.cn/tenant/project/landing/release-success?tenant=${CI_COMMIT_TITLE}&uuid=${CI_COMMIT_REF_NAME}
     */
    public function releaseSuccess(Request $request)
    {
        $uuid = $request->input('uuid');
        $statusCode = $request->input('task.statusCode');

        $state = 4;
        if ($statusCode == 'SUCCESS') {
            $state = 3;
        }

        $project = Project::firstWhere('uuid', $uuid);
        ReleaseRuntime::where('project_id', $project['id'])->update(['state' => $state]);
        return $this->success();
    }

    protected function changeReleaseRuntime($projectId, $state)
    {
        $lastEditRuntime = EditRuntime::where('project_id', $projectId)->orderBy('created_at', 'DESC')->first();
        ReleaseRuntime::updateOrCreate(['project_id' => $projectId, 'template_id' => $lastEditRuntime['template_id']], ['state' => $state]);
    }

    protected function publishSchema(string $uuid, array $schema)
    {
        $fileName = "schema/{$uuid}/schema.json";

        $client = OssClient::OSSClient();
        $bucket = 'lookstar-landing';
        $client->putObject($bucket, $fileName, json_encode($schema, JSON_UNESCAPED_UNICODE));
    }

    protected function startPipline($uuid)
    {
        $schemaUrl = "https://landing-release.lookstar.com.cn/schema/{$uuid}/schema.json";
        $pipelineId = '1900908';
        $organizationId = '61b2f0aba9dfa18f9b46a3ad';
        $devopsClient = new DevopsClient();
        $params = [
            'params' => [
                'envs' => [
                    'schema_url' => $schemaUrl,
                    'uuid' => $uuid,
                    'tenant_id' => tenant('id')
                ],
            ]
        ];
        $devopsClient->startPipelineRun($organizationId, $pipelineId, $params);
    }
}
