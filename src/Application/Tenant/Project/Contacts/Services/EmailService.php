<?php

namespace LookstarKernel\Application\Tenant\Project\Contacts\Services;

use Composer\Support\Crypt\AES;
use Illuminate\Support\Facades\DB;

class EmailService
{
    /**
     * 获得发送邮件的邮件内容
     *
     * @param [type] $config 邮件配置 arr
     * @param [type] $project 项目 arr
     * @param [type] $formsFiled 表单字段 arr
     * @param [type] $contact 联系人信息 arr
     * @return void
     */
    public function getEmailContent($config, $project, $formsFiled, $contact)
    {
        $mailContent = '';
        $configFieldsData = $config['fields_data'];
        if (!$project || !$configFieldsData) {
            return $mailContent;
        }

        $mailContent = $config['content'] . "<br>";
        $mailContent .= "项目名称：" . $project['title'] . "<br/>";
        $contactData = $contact['data'];

        foreach ($formsFiled['data'] as $field) {
            $fieldName = $field['name'];
            if (!in_array($fieldName, $configFieldsData['fields'])) {
                continue;
            }
            $fieldValue = '';
            if (isset($contactData[$fieldName])) {
                $fieldValue = $contactData[$fieldName];
            }

            if ($fieldName == 'phone' || $fieldName == 'email') {
                $fieldValue = AES::decode($fieldValue);
            }
            if (is_array($fieldValue)) {
                $fieldValue = implode(',', $fieldValue);
            }
            $mailContent .= $field['label'] . "：" . $fieldValue . "<br/>";
        }

        if (isset($configFieldsData['system_fields'])) {
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
                $contactSource = $contact['source'];
                $fieldValue = '';
                if ($systemField == 'offiaccount') {
                    $appid = $contactSource['appid'] ?? '';
                    $wechatAuthorizer = (array) DB::table('tenant_wechat_authorizer')->where('appid', $appid)->first();
                    if ($wechatAuthorizer) {
                        $fieldValue = $wechatAuthorizer['nick_name'];
                    }
                } else {
                    $fieldValue = $contactSource[$systemField];
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
