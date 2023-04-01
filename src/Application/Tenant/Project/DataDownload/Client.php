<?php

namespace LookstarKernel\Application\Tenant\Project\DataDownload;

use LookstarKernel\Application\Tenant\Project\DataDownload\Models\DataDownload;
use LookstarKernel\Application\Tenant\Project\DataDownload\Models\MailTemplate;
use LookstarKernel\Application\Tenant\System\Models\Config;
use LookstarKernel\Application\Tenant\Push\Mail\Job;
use Composer\Http\Controller;
use Composer\Support\Crypt\AES;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;

class Client extends Controller
{
    public function __construct(DataDownload $dataDownload)
    {
        $this->model = $dataDownload;

        $this->allowedFilters = [
            AllowedFilter::exact('project_id'),
            'title',
            AllowedFilter::exact('type'),
        ];

        $this->allowedIncludes = ['user'];
        $this->defaultSorts = ['sort', '-id'];
    }

    public function getTreeList()
    {
        $this->buildFilter();
        $list = $this->model->get();
        $this->list = self::_generateTree($list);
        return $this->success($this->list);
    }

    public static function _generateTree($data, $pid = 0)
    {
        $tree = [];
        foreach ($data as $v) {
            if ($v['parent_id'] == $pid) {
                if ($children = self::_generateTree($data, $v['id'])) {
                    $v['children'] = $children;
                    $v['tree_type'] = 'group';
                } else {
                    $v['tree_type'] = 'child';
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }

    public function sendMail(Request $request)
    {
        $input = $request->validate([
            'email' => ['required'],
            'crypt_key' => ['required'],
            'ids' => ['required', 'array', 'min:1'],
            'project_id' => ['required']
        ]);

        $email = AES::decodeRsa($input['crypt_key'], $input['email']);

        $trackCode = $this->trackCode($input['project_id'], $email);

        $data = $this->model->whereIn('id', $input['ids'])->get();
        $mailTemplate = MailTemplate::firstWhere('project_id', $input['project_id']);
        $mailContent = '<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tbody>
    <tr>
      <td align="center" valign="middle">
        <table
          width="800"
          border="0"
          cellpadding="0"
          cellspacing="0"
          style="font-size: 14px; line-height: 22px; background-color: #ffffff"
        >
          <tbody>
            <tr>
              <td align="left">';

        $mailContent .= $mailTemplate['header'];

        $mailContent .= '<table width="800"  border="0" align="center" cellSpacing="0" cellPadding="0">
  <tbody>';
        foreach ($data as $key => $value) {
            $mailContent .= '<tr>
      <td width="33" colSpan="1" rowSpan="1">
        <img
          src="https://lookstar-landing.oss-cn-beijing.aliyuncs.com/uploads/tenant/202205/eidt-image-upload-1651739482717.png"
          alt="eidt-image-upload-1651739482717"
          data-href="https://lookstar-landing.oss-cn-beijing.aliyuncs.com/uploads/tenant/202205/eidt-image-upload-1651739482717.png"
          style="width: 10px;display: block;" />
      </td>
      <td colSpan="1" rowSpan="1" style="font-size: 14px;line-height: 1.5;padding: 10px 0;">
        ' . $value['title'] . '
        &nbsp;&nbsp;
        <a href="' . $value['file'][0]['url'] . '"
          style="text-decoration:underline" rel="noopener" target="_blank">
          点击下载
        </a>
      </td>
    </tr>';
        }
        $mailContent .= '</tbody>
</table>
<p></p>';
        $mailContent .= $mailTemplate['footer'];
        $mailContent .= ('</td>
            </tr>
          </tbody>
        </table>
      </td>
    </tr>
  </tbody>
</table>
' . $trackCode);

        $subject = $mailTemplate['subject'];
        $config = Config::getMailConfig();
        $config['username_from'] = $mailTemplate['from_name'];
        Job::dispatch($config, $email, $subject, $mailContent)->onQueue('mail');
        return $this->success();
    }

    protected function trackCode($id, $email)
    {
        $trackId = mt_rand();
        $data = [
            'lookstar_tenant_id' => tenant()->getTenantKey(),
            'lookstar_app_id' => $id,
            'lookstar_app_name' => 'data_download',
            'distinct_id' => $email,
            'properties_lib' => 'server',
            'type' => 'track',
            'event' => '$email',
            '_track_id' => $trackId,
            'properties_title' => '预览资料',
        ];
        $url = 'https://analytics.lookstar.com.cn/email.gif?data=' . base64_encode(json_encode($data));
        return "<img height='0px' src='{$url}' />";
    }
}
