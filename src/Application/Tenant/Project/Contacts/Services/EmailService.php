<?php

namespace LookstarKernel\Application\Tenant\Project\Contacts\Services;

use Composer\Support\Crypt\AES;
use Illuminate\Support\Facades\DB;
use LookstarKernel\Application\Tenant\Project\Contacts\Models\EmailLogs;

class EmailService
{
    /**
     * 获得发送邮件的邮件内容
     *
     * @param [type] $config 邮件配置 arr
     * @param [type] $project 项目 arr
     * @param [type] $projectFiled 项目字段 arr
     * @param [type] $projectContactModel 项目联系人信息 arr
     * @return void
     */
    public function getEmailContent($config, $project, $projectFiled, $projectContactModel)
    {
        $mailContent = '';
        $configFieldsData = $this->getArr($config['fields_data'] ?? '');
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
        $projectContactData = $this->getArr($projectContactModel['data']);
        if (!empty($configFieldsData['fields']) && $projectFiled && $projectFiled['data']) {
            $projectFiled['data'] = $this->getArr($projectFiled['data']);
            foreach ($projectFiled['data'] as $data) {
                $fieldName = $data['name'] ?? '';
                if (!in_array($fieldName, $configFieldsData['fields'])) {
                    continue;
                }
                $fieldValue = $projectContactData[$fieldName] ?? '';
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
                    $fieldValue = ($projectContactModel['source'][$systemField] ?? '') ?: ($projectContactData[$systemField] ?? '');
                }
                if (!$fieldValue) {
                    continue;
                }
                $mailContent .= ($systemFieldArr[$systemField] ?? '') . "：" . $fieldValue . "<br/>";
            }
        }
        return $mailContent;
    }

    public function getArr($arrOrString)
    {
        $res = $arrOrString;
        if (is_string($arrOrString)) {
            $res = json_decode($arrOrString, true);
        }
        return $res;
    }
}
