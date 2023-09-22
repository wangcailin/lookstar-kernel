<?php

namespace LookstarKernel\Application\Tenant\AI\GPT\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LookstarKernel\Support\AI\ApiClient;
use LookstarKernel\Application\Tenant\AI\GPT\Models\Conversation;
use LookstarKernel\Application\Tenant\AI\GPT\Models\PromptConfig;
use LookstarKernel\Application\Tenant\AI\GPT\Models\Repository;
use LookstarKernel\Application\Tenant\WeChat\Models\WeChatAIReply;
use Illuminate\Support\Facades\Log;
use Composer\Application\WeChat\WeChat;
use LookstarKernel\Application\Tenant\AI\GPT\Models\Project;
use LookstarKernel\Application\Tenant\AI\GPT\Service\ConversationService;

class AIResponse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $aiReply;
    public $api;
    public $appId;
    public $openid;
    public $text;
    public $row;
    public $tenantId;
    public $promptConfig;
    public $service;

    /**
     * Create a new job instance.
     */
    public function __construct($tenantId, $appId, $openid, $text)
    {
        $this->appId = $appId;
        $this->openid = $openid;
        $this->text = $text;
        $this->tenantId = $tenantId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->aiReply = WeChatAIReply::where(['appid' => $this->appId, 'state' => 1])->with('project')->first();
        $this->promptConfig = PromptConfig::where('project_id', $this->aiReply['project_id'])->first();
        $this->service = new ConversationService($this->promptConfig);
        $this->api = (new Wechat())->getOfficialAccount($this->appId)->getClient();
        if (!$this->aiReply) {
            return;
        }
        try {
            $type = $this->aiReply['project']['type'] ?? '';
            $message['touser'] = $this->openid;
            $message['msgtype'] = 'text';

            $answer = $this->getResponse($type);

            if (!$answer) {
                return;
            }
            $message['text']['content'] = trim($answer);
            $this->addConversationRecord($type, $answer);
            $this->api->postJson('/cgi-bin/message/custom/send', $message);
        } catch (\Exception $e) {
            Log::error('队列任务发生错误: ' . $e->getMessage());
        }
    }

    public function getResponse($type)
    {
        if ($type == Project::TYPE_WECHAT) {
            return $this->getWeChatResponse();
        }

        if ($type == Project::TYPE_SALES) {
            return $this->getSalesWechatResponse();
        }

        return '';
    }

    private function addConversationRecord($type, $answer)
    {
        Conversation::create([
            'project_id' => $this->aiReply['project_id'],
            'openid' => $this->openid,
            'message' => $this->text,
            'tenant_id' => $this->tenantId,
            'result' => $answer,
            'type' => $type,
        ]);
    }

    public function getWeChatResponse()
    {
        $result = ApiClient::post('/app/gpt/wechat', [
            'streaming' => false,
            'message' => $this->text,
            'repository_id' => Repository::getRepositoryId($this->aiReply['project_id']),
            'chat_history' => $this->getHistoryByOpenid(),
            'prompt' => $this->service->getProjectPrompt($this->aiReply['project']),
            'llm_name' => $this->promptConfig['llm_name'] ?? PromptConfig::LLM_TYPE_GPT_TURBO,
            'temperature' => $this->promptConfig['temperature'] ?? PromptConfig::DEFAULT_TEMPERATURE,
        ]);
        return $result;
    }

    public function getSalesWechatResponse()
    {
        $chatHistory = $this->getHistoryByOpenid();
        $contents = ApiClient::post('/app/gpt/sales', [
            'message' => $this->text,
            'repository_id' => Repository::getRepositoryId($this->aiReply['project_id']),
            'streaming' => false,
            'temperature' => $this->promptConfig['temperature'] ?? PromptConfig::DEFAULT_TEMPERATURE,
            'prompt' => $this->service->getProjectPrompt($this->aiReply['project'], $this->promptConfig, $chatHistory),
            'chat_history' => $chatHistory,
        ]);
        $response = $this->service->formatContent(Project::TYPE_SALES, $contents)['answer'] ?? '';
        return $response;
    }

    public function getHistoryByOpenid()
    {
        $timeoutMinutes = $this->promptConfig['data']['timeout_minutes'] ?? 5;
        $time = date('Y-m-d H:i:s', strtotime('now') - $timeoutMinutes * 60);
        $data = [];
        $conversations = Conversation::where(['type' => Project::TYPE_SALES, 'openid' => $this->openid])
            ->where('created_at', '>', $time)
            ->orderBy('id', 'desc')
            ->limit(15)
            ->get()
            ->toArray();
        if ($conversations) {
            $conversations = array_reverse($conversations);
            foreach ($conversations as $conversation) {
                $data[] = $this->service->explodeSalesGPT . $conversation['message'];
                $data[] = ($this->promptConfig['data']['salesperson_name'] ?? '') . ':' . $conversation['result'];
            }
        }
        return $data;
    }
}
