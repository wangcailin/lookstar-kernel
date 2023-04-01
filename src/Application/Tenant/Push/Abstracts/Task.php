<?php

namespace LookstarKernel\Application\Tenant\Push\Abstracts;

use Composer\Http\Controller;
use Composer\Exceptions\ApiErrorCode;
use Composer\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

use LookstarKernel\Application\Tenant\Auth\Models\User as AuthUser;
use Composer\Support\Crypt\AES;

abstract class Task extends Controller
{
    public $userModel;
    public $userModelTableName;
    public $dateTime;

    public function __construct()
    {
        $this->validateRules = [
            'title' => ['required', 'max:128'],
            'send_time' => ['required', 'date', 'after:+4 minutes']
        ];

        $this->validateMessage = [
            'after' => '请修改发送时间为5分钟后在进行操作',
        ];

        $this->dateTime = date('Y-m-d H:i:s');
    }

    public function beforeUpdate()
    {
        $this->data = request()->all();
        $this->handleUpdateValidate();
        $this->verifyStatus($this->id);
    }

    public function beforeDelete()
    {
        $this->verifyStatus($this->id);
    }

    protected function verifyStatus($id)
    {
        if (!$this->model->firstWhere(['id' => $id, 'status' => 0])) {
            throw new ApiException('任务在进行中或已结束不允许操作', ApiErrorCode::VERIFY_CODE_ERROR);
        }
    }

    public function changeStatus($id, Request $request)
    {
        $input = $request->validate([
            'status' => ['required', Rule::in([0, 1])],
            'send_time' => ['exclude_unless:status,1', ...$this->validateRules['send_time']],
            'code' => ['exclude_unless:status,1', 'required']
        ], $this->validateMessage);

        if ($input['status'] == '1') {
            AuthUser::verifyAdminCode($this->model->taskType, $input['code']);
        }

        $task = $this->model->findOrFail($id);
        $task->verifyChange();

        $data = ['status' => $input['status']];
        if ($input['status'] == '1') {
            $data['send_time'] = $input['send_time'];
        }

        $task->update($data);
        return $this->success();
    }

    public function upload($id, Request $request)
    {
        $input = $request->validate([
            'type' => ['required', Rule::in(['append', 'cover'])],
            'source_type' => ['required', Rule::in(['upload', 'group', 'custom'])],
            'group_id' => ['exclude_unless:source_type,group', 'required'],
            'file' => ['exclude_unless:source_type,upload', 'required', 'file', 'mimetypes:text/csv,application/vnd.ms-excel,text/plain', 'max:2048']
        ]);

        $this->verifyStatus($id);

        $row = [
            'task_id' => $id,
        ];

        if ($input['type'] == 'cover') {
            $this->userModel::where($row)->delete();
        }

        if ($input['source_type'] == 'upload') {
            $this->handleUpload($input['file'], $id);
        } elseif ($input['source_type'] == 'group') {
            $this->handleGroup($input['group_id'], $id);
        } elseif ($input['source_type'] == 'custom') {
            $this->handleCustom($id);
        }

        return $this->success();
    }

    protected function handleUpload($file, $taskId)
    {
        $fileName = $file->storeAs('', $file->getClientOriginalName(), 'uploads');
        $filePath = Storage::disk('uploads')->path($fileName);

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new ApiException('文件错误', ApiErrorCode::VERIFY_CODE_ERROR);
        }
        $row = [
            'task_id' => $taskId,
            'task_type' => $this->model->taskType,
        ];
        while (($data = fgetcsv($handle)) !== false) {
            if (is_array($data) && $data[0]) {
                $row['value'] = $this->hanldeUploadValue($data[0]);
                $this->userModel::firstOrCreate($row);
            }
        }
    }

    public function hanldeUploadValue($value)
    {
        return AES::encode($value);
    }

    abstract protected function handleGroup($groupId, $taskId);
    abstract protected function handleCustom($taskId);

    public function export($id)
    {
        $values = $this->userModel::where('task_id', $id)->pluck('value');

        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        foreach ($values as $key => $value) {
            $worksheet->setCellValue('A' . strval($key + 1), $value);
        }

        header('Content-type:text/csv');
        header('Cache-Control: max-age=0');
        header('Content-Disposition: attachment;filename=UserID-Task-' . $id . date('Y-m-d') . '.csv');
        $objWriter = IOFactory::createWriter($spreadsheet, 'Csv');
        return $objWriter->save('php://output');
    }
}
