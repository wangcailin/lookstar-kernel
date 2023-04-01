<?php

namespace LookstarKernel\Application\Tenant\Project\Contacts;

use LookstarKernel\Application\Tenant\Contacts\Models\Contacts as ModelsContacts;
use LookstarKernel\Application\Tenant\Project\Contacts\Models\ContactsFields;
use LookstarKernel\Application\Tenant\Project\Contacts\Models\Contacts;
use LookstarKernel\Application\Tenant\Project\Models\Project;
use LookstarKernel\Application\Tenant\Project\Models\ProjectChannel;
use Composer\Support\Redis\CaptchaClient;
use Composer\Http\Controller;
use Spatie\QueryBuilder\AllowedFilter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Composer\Support\Crypt\AES;
use Illuminate\Http\Request;
use LookstarKernel\Application\Tenant\Contacts\Models\ContactsBindOpenid;
use LookstarKernel\Application\Tenant\WeChat\Models\WeChatAuthorizer;

class Client extends Controller
{
    public function __construct(Contacts $contacts)
    {
        $this->model = $contacts;
        $this->allowedFilters = [
            AllowedFilter::exact('project_id'),
            AllowedFilter::exact('source->appid'),
            AllowedFilter::exact('source->project_channel'),
            AllowedFilter::exact('source->distinct_id'),
        ];
    }

    public function beforeCreate()
    {
        $data = $this->data;
        $plaintextList = ['phone', 'email'];

        $addressMap = [];

        if (isset($data['data']) && $data['data']) {
            foreach ($data['data'] as $key => $value) {
                if (
                    $key == 'phone' ||  $key == 'email'
                ) {
                    $plaintext = AES::decodeRsa($data['crypt_key'], $value);
                    $ciphertext = AES::encode($plaintext);
                    $data['data'][$key] = $ciphertext;
                    $plaintextList[$key] = $plaintext;
                } elseif ($key == 'address') {
                    $addressMap = Contacts::addressMap($value);
                }
            }
        }
        $this->data = $data;

        $fields = ContactsFields::getProjectFields($this->data['project_id']);
        foreach ($fields as $key => $value) {
            if (isset($value['show']) && $value['show'] == 1 &&  isset($value['rule_name'])) {
                if ($value['rule_name'] == 'regexp_code_email') {
                    CaptchaClient::verifyEmailCode($plaintextList['email'], 'mail_code', $this->data['data']['captcha_code']);
                    unset($this->data['data']['captcha_code']);
                } elseif ($value['rule_name'] == 'regexp_ch_code_phone') {
                    CaptchaClient::verifySmsCode($plaintextList['phone'], 'phone_code', $this->data['data']['captcha_code']);
                    unset($this->data['data']['captcha_code']);
                }
            }
        }
        $contactsData = array_merge($this->data['data'], $addressMap);
        $contactsData['source'] = $this->data['source'];

        $project = Project::find($this->data['project_id']);
        $contactsData['channel'] = $project->type_name . ': ' . $project->title;
        ModelsContacts::sourceCreate($contactsData);
    }

    public function getUser(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
        ]);

        $openid = $request->input('openid');
        $distinctId = $request->input('distinct_id');
        $projectId = $request->input('project_id');

        $where = ['project_id' => $projectId];
        $user = [];

        if ($openid) {
            $user = $this->model::where($where)->where('source->openid', $openid)->orderByDesc('created_at')->first();
        } elseif ($distinctId) {
            $user = $this->model::where($where)->where('source->distinct_id', $distinctId)->orderByDesc('created_at')->first();
        }
        if ($user) {
            $plaintext = $user->plaintext_data;
            $user['phone'] = $plaintext['phone'];
            if (isset($plaintext['email'])) {
                $user['email'] = $plaintext['email'];
            }
            return $this->success(['code' => 1, 'data' => $user]);
        }

        if ($openid) {
            $phone = ContactsBindOpenid::where('openid', $openid)->orderByDesc('updated_at')->first();
            if ($phone) {
                $user = ModelsContacts::where('phone', $phone['phone'])->first();
            }
        } elseif ($distinctId) {
            $user = ModelsContacts::where('source->distinct_id', $distinctId)->first();
        }
        if ($user) {
            $plaintext = $user->plaintext_data;
            $user['phone'] = $plaintext['phone'];
            if (isset($plaintext['email'])) {
                $user['email'] = $plaintext['email'];
            }
            return $this->success(['code' => 0, 'data' => $user]);
        }

        return $this->success(['code' => 0]);
    }


    public function export($id)
    {
        $project = Project::find($id);
        $list = $this->model->where('project_id', $id)->get();

        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $sourceFields = [
            [
                'name' => 'openid',
                'label' => 'OpenID'
            ], [
                'name' => 'unionid',
                'label' => 'UnionID'
            ],  [
                'name' => 'appid',
                'label' => '公众号'
            ],  [
                'name' => 'utm_campaign',
                'label' => '活动名称'
            ],  [
                'name' => 'utm_source',
                'label' => '广告来源'
            ], [
                'name' => 'utm_medium',
                'label' => '广告媒介'
            ], [
                'name' => 'utm_term',
                'label' => '广告关键词'
            ], [
                'name' => 'utm_content',
                'label' => '广告内容'
            ],
        ];
        if ($list) {
            $index = 1;
            $row = ContactsFields::firstWhere('project_id', $id);
            $fieldsData = array_merge($row['data'], $sourceFields);
            foreach ($fieldsData as $key => $value) {
                $startCell = chr($key + 65);
                $worksheet->setCellValue($startCell . $index, $value['label']);
            }

            foreach ($list as $key => $value) {
                foreach ($fieldsData as $k => $field) {
                    if (isset($value['data']) && isset($value['data'][$field['name']])) {
                        $v = $value['data'][$field['name']];
                        if (is_array($v)) {
                            $v = implode(',', $v);
                        }
                        if ($field['name'] == 'phone' || $field['name'] == 'email') {
                            $v = $value->plaintext_data[$field['name']];
                        }
                        $worksheet->setCellValue(chr($k + 65) . ($index + $key + 1), $v);
                    }

                    foreach ($value->source as $j => $v) {
                        if ($j == $field['name']) {
                            if ($field['name'] == 'appid') {
                                $authorizer = WeChatAuthorizer::firstWhere('appid', $v);
                                if ($authorizer) {
                                    $v = $authorizer['nick_name'];
                                }
                            }
                            $worksheet->setCellValue(chr($k + 65) . ($index + $key + 1), $v);
                        }
                    }
                }
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Cache-Control: max-age=0');
        header('Content-Disposition: attachment;filename=' . $project['title'] . '-用户留资表-' . date('Y-m-d') . '.xlsx');
        $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
        return $objWriter->save('php://output');
    }
}
