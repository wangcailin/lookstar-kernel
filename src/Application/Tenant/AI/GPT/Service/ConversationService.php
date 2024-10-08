<?php

namespace LookstarKernel\Application\Tenant\AI\GPT\Service;

use LookstarKernel\Application\Tenant\AI\GPT\Models\PromptConfig;
use LookstarKernel\Application\Tenant\AI\GPT\Models\Project;

class ConversationService
{
    public $saleUserPrefix = 'User：';
    public $explodeSalesGPT = '：';

    public $promptConfig;
    public function __construct($promptConfig)
    {
        $this->promptConfig = $promptConfig;
    }
    /**
     * 根据项目类型获得项目请求的url
     *
     * @param [type] $type
     * @return void
     */
    public function getUrlByProjectType($type)
    {
        $url = '';
        switch ($type) {
            case Project::TYPE_WECHAT:
                $url = '/app/gpt/wechat/stream';
                break;
            case Project::TYPE_SALES:
                $url = '/app/gpt/sales';
                break;
        }
        return $url;
    }

    /**
     * 格式化非流逝输出 返回结果
     *
     * @param [type] $type
     * @param [type] $contents
     * @return void
     */
    public function formatContent($type, $contents)
    {
        $salespersonRole = $this->promptConfig['data']['salesperson_name'] ?? '';
        $data = [];
        switch ($type) {
            case Project::TYPE_WECHAT:
                $data = $contents;
                break;
            case Project::TYPE_SALES:
                if (is_array($contents)) {
                    $response = $contents[count($contents) - 1] ?? '';
                    $data['answer'] = trim(explode($salespersonRole . $this->explodeSalesGPT, $response)[1] ?? '');
                    $data['source_documents'] = [];
                }
                break;
        }
        return $data;
    }

    /**
     * 获得项目历史记录
     *
     * @param [type] $type
     * @param [type] $chatHistories
     * @return void
     */
    public function getProjectChatHistory($type, $chatHistories)
    {
        $history = [];
        if (!($this->promptConfig['is_chat_history'] ?? '')) {
            return $history;
        }
        switch ($type) {
            case Project::TYPE_WECHAT:
                $history = $chatHistories;
                break;
            case Project::TYPE_SALES:
                $salespersonRole = $this->promptConfig['data']['salesperson_name'] ?? '';
                foreach ($chatHistories as $chatHistory) {
                    if ($chatHistory && (count($chatHistory) == 2)) {
                        $history[] = $this->saleUserPrefix . $chatHistory[0];
                        $history[] = $salespersonRole . $this->explodeSalesGPT . $chatHistory[1];
                    }
                }
                break;
        }
        return $history;
    }

    /**
     * 根据项目类型获得prompt
     *
     * @param [type] $project
     * @return void
     */
    public function getProjectPrompt($project)
    {
        $prompt = '';
        switch ($project['type']) {
            case Project::TYPE_WECHAT:
                $prompt = $this->getWechatPromptString($this->promptConfig['data'] ?? []);
                break;
            case Project::TYPE_SALES:
                $prompt = $this->getSalesPromptArr($this->promptConfig['data'] ?? []);
                break;
        }
        return $prompt;
    }

    /**
     * 获得sale prompt
     *
     * @param [type] $promptConfigData
     * @return void
     */
    private function getSalesPromptArr($promptConfigData)
    {
        $prompts = [];
        $fields = ['sales_conversation_chain_prompt', 'stage_analyzer_chain_prompt', 'company_name', 'company_business', 'conversation_purpose', 'salesperson_role', 'company_service', 'salesperson_name', 'conversation_stages'];
        foreach ($fields as $field) {
            $prompts[$field] = $promptConfigData[$field] ?? '';
        }
        return $prompts;
    }

    /**
     * 获得WechatAPI的prompt
     *
     * @param [type] $promptConfigData
     * @return void
     */
    private function getWechatPromptString($promptConfigData)
    {
        $prompts = [];

        $fields = ['role', 'duty', 'language', 'character_limit', 'tone'];
        foreach ($fields as $field) {
            if (!empty($promptConfigData[$field]) && $promptConfigData[$field]) {
                $fieldName = '';
                if ($field == 'character_limit') {
                    $fieldName = '回复长度';
                } else if ($field == 'tone' && ($promptConfigData[$field] == '其他')) {
                    $promptConfigData[$field] = $promptConfigData['tone_other'];
                } else if ($field == 'language') {
                    $fieldName = '回复语言';
                    // if ($promptConfigData[$field] == 'English') {
                    // $promptConfigData['回复语言'] = "请用英文回答问题";
                    // }
                    // if ($promptConfigData[$field] == 'AskLanguage') {
                    $promptConfigData[$field] = "按提问问题的语种进行回复，英文问题使用英文回复，中文问题使用中文回复";
                    // }
                } else if ($field == 'tone') {
                    $promptConfigData[$field] = $promptConfigData[$field];
                    $fieldName = '回复语气';
                } else if ($field == 'role') {
                    $fieldName = '角色';
                } else if ($field == 'duty') {
                    $fieldName = '任务';
                }

                $prompts[] = $fieldName . '：' . $promptConfigData[$field];
            }
        }
        if ($promptConfigData['do_not']['status'] ?? '') {
            $forbidden = '禁止回复的话题：';
            foreach ($promptConfigData['do_not']['keyword'] as $keyword) {
                if (!empty($keyword['text'])) {
                    $forbidden .= $keyword['text'] . '，';
                }
            }
            $prompts[] = trim($forbidden, '，') . '相关的问题。如有问到，请回复' . ($promptConfigData['do_not']['script'] ?? '');
        }
        return implode('，', $prompts);
    }
}
