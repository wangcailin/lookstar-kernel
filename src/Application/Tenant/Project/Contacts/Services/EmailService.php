<?php

namespace LookstarKernel\Application\Tenant\Project\Contacts\Services;

use Composer\Support\Crypt\AES;
use Illuminate\Support\Facades\DB;
use LookstarKernel\Application\Tenant\Project\Contacts\Models\EmailLogs;

class EmailService
{
    public function getEmailContent($config, $projectContactModel)
    {
        $mailContent = '';
        $projectId = $config['project_id'] ?? '';
        $project = (array) DB::table('tenant_project')->where('id', $projectId)->first();
        $configFieldsData = json_decode($config['fields_data'] ?? '', true);
        if (!$project || !$configFieldsData) {
            return $mailContent;
        }

        $contactsId = $projectContactModel['id'];
        $emailLogModel = EmailLogs::where([
            'tenant_id' => $config['tenant_id'],
            'project_id' => $config['project_id'],
            'contacts_id' => $contactsId,
        ])->first();
        if ($emailLogModel) {
            return $mailContent;
        }
        $mailContent = $config['content'] . "<br>";
        //添加活动名称
        $mailContent .= !empty($project['title']) ? "项目名称：" . $project['title'] . "<br/>" : '';
        //添加活动字段
        $field = (array) DB::table('tenant_project_contacts_fields')->where(['project_id' => $projectId])->first();
        if (!empty($configFieldsData['fields']) && $field && $field['data']) {
            foreach ($field['data'] as $data) {
                $fieldName = $data['name'] ?? '';
                if (!in_array($fieldName, $configFieldsData['fields'])) {
                    continue;
                }
                $fieldValue = $projectContactModel['data'][$fieldName] ?? '';
                if (!$fieldValue) {
                    continue;
                }
                if ($fieldName == 'phone' || $fieldName == 'email') {
                    $fieldValue = AES::decode($fieldValue);
                }
                if (is_array($fieldValue)) {
                    $fieldValue = implode(',', $fieldValue);
                }
                $mailContent .= ($data['label'] ?? '') . "：" . $fieldValue . "<br/>";
            }
        }

        if (!empty($configFieldsData['system_fields'])) {
            $systemFieldArr = [
                'utm_campaign' => '活动名称',
                'utm_source' => '广告来源',
                'utm_medium' => '广告媒介',
                'utm_term' => '广告关键词',
                'utm_content' => '广告内容',
                'utm_content' => '广告内容',
                'openid' => 'openid',
                'unionid' => 'unionid',
                'offiaccount' => '公众号',
            ];
            foreach ($configFieldsData['system_fields'] as $systemField) {
                $fieldValue = '';
                if ($systemField == 'offiaccount') {
                    $appid = $projectContactModel['source']['appid'] ?? '';
                    $wechatAuthorizer = (array) DB::table('tenant_wechat_authorizer')->where('appid', $appid)->first();
                    if ($wechatAuthorizer) {
                        $fieldValue = $wechatAuthorizer['nick_name'];
                    }
                } else {
                    $fieldValue = ($projectContactModel['source'][$systemField] ?? '') ?: ($projectContactModel['data'][$systemField] ?? '');
                }
                if (!$fieldValue) {
                    continue;
                }
                $mailContent .= ($systemFieldArr[$systemField] ?? '') . "：" . $fieldValue . "<br/>";
            }
        }
        return $mailContent;
    }
}
