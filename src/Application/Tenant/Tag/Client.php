<?php

namespace LookstarKernel\Application\Tenant\Tag;

use LookstarKernel\Application\Tenant\Tag\Models\Tag;
use LookstarKernel\Application\Tenant\Tag\Models\TagGroup;
use Composer\Application\User\Models\Relation\UserTagCount;
use Composer\Application\User\Models\User;
use Composer\Http\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Client extends Controller
{
    public function __construct(Tag $tag)
    {
        $this->model = $tag;
        $this->allowedFilters = ['id', 'name', 'remark'];
        $this->validateRules = [
            'name' => 'required|max:100',
        ];
    }

    public function afterBuildFilter()
    {
        $this->model->withCount(['user' => function (Builder $query) {
            $query->select(DB::raw('COUNT(DISTINCT id)'));
        }]);
    }

    public function handleCreateValidate()
    {
        Validator::make(
            $this->data,
            [
                'name' => [
                    'required',
                    'max:100',
                    tenant()->unique('LookstarKernel\Application\Tenant\Tag\Models\Tag', 'name'),
                ],
                'group_id' => ['required'],
            ],
        )->validate();
        $this->handleValidate();
    }

    public function getTableList(TagGroup $tagGroup)
    {
        $this->model = $tagGroup;
        $this->buildFilter();
        $this->list = $this->model->with(['tag'])->get();
        return $this->success(['data' => $this->list]);
    }

    public function createGroup(TagGroup $tagGroup)
    {
        $this->data = request()->all();
        if ($this->authUserId) {
            $this->createAuthUserId();
        }

        $this->model = $tagGroup;
        Validator::make(
            $this->data,
            [
                'name' => [
                    'required',
                    'max:100',
                    tenant()->unique('LookstarKernel\Application\Tenant\Tag\Models\TagGroup', 'name'),
                ],
            ],
        )->validate();

        $this->row = $this->model::create($this->data);
        return $this->success($this->row);
    }

    public function updateGroup(TagGroup $tagGroup, $id)
    {
        $row = $tagGroup::findOrFail($id);
        $row->update(request()->all());
        $this->beforeUpdate();
        return $this->success($row);
    }

    public function getTreeSelectList(TagGroup $tagGroup)
    {
        $this->model = $tagGroup;
        $this->buildFilter();
        $this->list = $this->model->with('children')->select(['id', 'name as title'])->get();
        return $this->success($this->list);
    }

    public function getGroupSelectList(TagGroup $tagGroup)
    {
        $this->list = $tagGroup->select(['id AS value', 'name as label'])->orderBy('id', 'DESC')->get();
        return $this->success($this->list);
    }

    public function export(TagGroup $tagGroup)
    {
        $this->model = $tagGroup;
        $this->buildFilter();
        $list = $this->model->with(['tag'])->get();

        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $style = [
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];

        foreach ($list as $key => $value) {
            $startCell = chr($key + 65 + $key);
            $worksheet->mergeCells($startCell . '1:' . chr($key + 66 + $key) . '1');
            $worksheet->getStyle($startCell . '1')->applyFromArray($style)->getFont()->setBold(true);

            $worksheet->setCellValue($startCell . '1', $value['name']);
            foreach ($value['tag'] as $k => $v) {
                $worksheet->setCellValue($startCell . ($k + 2), $v['id']);
                $worksheet->setCellValue(chr($key + 66 + $key) . ($k + 2), $v['name']);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=标签"
            . date('Y-m-d') . '.xlsx');
        header('Cache-Control: max-age=0');
        $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $objWriter->save('php://output');
        exit();
    }

    public function getStatistic(TagGroup $tagGroup, User $user, UserTagCount $userTagCount)
    {
        $tagGroupCount = $tagGroup->count();
        $tagCount = $this->model->whereIn('group_id', $tagGroup->select('id'))->count();
        $tagRan = $userTagCount::whereIn('user_id', $user::select('id'));
        $tagHitCount = $tagRan->select('tag_id')->distinct()->count();
        $tagUserCount = $tagRan->select('user_id')->distinct()->count();

        $userCount = $user::count();
        $tagCount = $this->model::whereIn('group_id', function ($query) {
            return $query->select('id')->from('tag_Group')->get();
        })->count();

        if ($userCount == 0) {
            $tagCover = '0';
        } else {
            $tagCover = (string) (round(($tagCount / $userCount), 4) * 100);
        }

        return $this->success([
            'tag_group_count' => $tagGroupCount,
            'tag_count' => $tagCount,
            'tag_hit_count' => $tagHitCount,
            'tag_user_count' => $tagUserCount,
            'tag_cover' => $tagCover,
        ]);
    }

    public function getWordCloud()
    {
        $list = $this->handleWordCloud();
        return $this->success($list);
    }

    private function handleWordCloud()
    {
        return $this->model::whereIn('group_id', function ($query) {
            return $query->select('id')->from('tag_Group')->get();
        })->has('user')->withCount('user')->orderBy('user_count', 'desc')->get();
    }

    public function exportWordCloud()
    {
        $list = $this->handleWordCloud();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', '标签名称');
        $sheet->setCellValue('B1', '改标签下的用户人数');
        foreach ($list as $key => $value) {
            $sheet->setCellValue('A' . ($key + 2), $value['name']);
            $sheet->setCellValue('B' . ($key + 2), $value['user_count']);
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=用户标签云数据"
            . date('Y-m-d') . '.csv');
        header('Cache-Control: max-age=0');
        $objWriter = IOFactory::createWriter($spreadsheet, 'Csv');
        return $objWriter->save('php://output');
    }

    public function getUserList(Request $request)
    {
        $sourcetype = $request->input('sourcetype');
        $list = $this->handleUserList($sourcetype)->paginate(10);
        return $this->success($list);
    }

    private function handleUserList($sourcetype)
    {
        return User::whereHas('tag', function ($query) use ($sourcetype) {
            if (!empty($sourcetype)) {
                $query->where('user_tag_relation.source_type', $sourcetype);
            }
            $query->where('page_event_type', 'view');
        })->withCount('tag')->with('info')->orderBy('tag_count', 'desc');
    }

    public function exportUserList(Request $request)
    {
        $sourcetype = $request->input('sourcetype');
        $list = $this->handleUserList($sourcetype)->get();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $cell = ['姓名', '公司', '邮箱', '手机号', '沾染标签数'];
        foreach ($cell as $key => $value) {
            $sheet->setCellValue(chr(65 + $key) . '1', $value);
        }

        foreach ($list as $key => $value) {
            $cell = [$value['info'] ? $value['info']['full_name'] : '', $value['info'] ? $value['info']['company'] : '', $value['email'], $value['phone'], $value['tag_count']];
            foreach ($cell as $k => $v) {
                $sheet->setCellValue(chr(65 + $k) . ($key + 2), $v ?: '');
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=用户标签排行数据" . date('Y-m-d') . '.csv');
        header('Cache-Control: max-age=0');
        $objWriter = IOFactory::createWriter($spreadsheet, 'Csv');
        return $objWriter->save('php://output');
    }
}
