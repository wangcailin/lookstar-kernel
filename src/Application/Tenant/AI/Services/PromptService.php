<?php

namespace LookstarKernel\Application\Tenant\AI\Services;

use LookstarKernel\Application\Tenant\AI\Models\Prompt;
use Composer\Exceptions\ApiErrorCode;
use Composer\Exceptions\ApiException;

class PromptService
{
    /**
     * 根据prompt的id获取prompt
     *
     * @param [type] $id
     * @param [type] $params
     * @return void
     */
    public static function getPrompt($id, $params)
    {
        $model = Prompt::where('id', $id)->first();
        if (!$model) {
            throw new ApiException('prompt id 错误', ApiErrorCode::VALIDATION_ERROR);
        }
        $promptTemplate = $model['prompt'] ?? '';
        foreach ($params as $key => $value) {
            // 大小写不敏感的
            if (is_array($value)) {
                $value = implode('，', $value);
            }
            $promptTemplate = str_ireplace($key, $value, $promptTemplate);
        }
        return $promptTemplate;
    }
}
