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

class AIFreepublishRestore implements ShouldQueue
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
        $this->model = RepositoryData::where('id', $this->repositoryDataId)->first();
        if ($this->model) {
            if ($this->model['state'] == 0) {
                $this->model->update(['state' => 1]);
                ApiClient::delete(RepositoryData::DELETE_URL, [
                    'repository_id' => Repository::getRepositoryId($this->projectId),
                    'id' => $this->repositoryDataId,
                ]);
            }
        }
    }
}
