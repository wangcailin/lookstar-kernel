<?php

namespace LookstarKernel\Application\Tenant\AI\GPT\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LookstarKernel\Application\Tenant\AI\GPT\Models\Repository;
use LookstarKernel\Application\Tenant\AI\GPT\Models\RepositoryData;
use LookstarKernel\Support\AI\ApiClient;
use Illuminate\Support\Facades\Log;

class AIFreepublishDelete implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $repositoryDataId;
    public $projectId;
    public $model;

    /**
     * Create a new job instance.
     */
    public function __construct($repositoryDataId, $projectId)
    {
        $this->repositoryDataId = $repositoryDataId;
        $this->projectId = $projectId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('******************************************************************');
        Log::info($this->repositoryDataId);
        Log::info($this->projectId);
        Log::info('------------------------------------------------------------------');
        $this->model = RepositoryData::where('id', $this->repositoryDataId)->first();
        if ($this->model) {
            $this->model->update(['state' => 0]);
            $this->upload(RepositoryData::DELETE_URL);
        }
    }

    public function upload($postUrl)
    {
        $response = ApiClient::post($postUrl, [
            'repository_id' => Repository::getRepositoryId($this->projectId),
            'metadata' => $this->model['metadata'],
            'url' => $this->model['url'],
        ]);
        if ($response === false) {
            return false;
        }
        return true;
    }
}
