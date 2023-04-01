<?php

namespace LookstarKernel\Application\Tenant\Contacts;

use LookstarKernel\Application\Tenant\Contacts\Models\Contacts;
use LookstarKernel\Application\Tenant\Contacts\Models\Custom;
use LookstarKernel\Application\Tenant\Contacts\Models\Field;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Composer\Http\Controller;
use Composer\Exceptions\ApiException;
use Composer\Exceptions\ApiErrorCode;
use Composer\Support\Crypt\AES;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Spatie\QueryBuilder\AllowedFilter;

class Client extends Controller
{
    public function __construct(Contacts $contacts)
    {
        $this->model = $contacts;
        $this->defaultSorts = ['-created_at', '-id'];
        $this->validateRules = [
            'system.phone' => ['required', 'min:10']
        ];

        $this->allowedFilters = [
            'full_name',
            'company',
            AllowedFilter::callback('phone', function (Builder $builder, $value) {
                if ($value) {
                    $builder->where('phone', AES::encode($value));
                }
            }),
            AllowedFilter::callback('email', function (Builder $builder, $value) {
                if ($value) {
                    $builder->where('email', AES::encode($value));
                }
            }),
        ];
    }

    public function create()
    {
        $this->data = request()->all();

        $this->data['system']['channel'] = '后台创建';

        $this->handleCreateValidate();

        if ((!$this->data['system']['phone'] = AES::decodeRsa($this->data['crypt_key'], $this->data['system']['phone'])) || (isset($this->data['system']['email']) && !$this->data['system']['email'] = AES::decodeRsa($this->data['crypt_key'], $this->data['system']['email']))) {
            throw new ApiException('参数错误', ApiErrorCode::VALIDATION_ERROR);
        };

        $this->handleCreate();

        return $this->success($this->row);
    }

    public function openApiCreate()
    {
        $this->data = request()->all();
        $channel = '开放平台';
        if (isset($this->data['system']['channel'])) {
            $channel .= ('：' . $this->data['system']['channel']);
        }
        $this->data['system']['channel'] = $channel;

        $this->handleCreateValidate();

        $this->handleCreate();

        return $this->success($this->row);
    }

    public function handleCreate()
    {
        $this->data['system']['phone'] = AES::encode($this->data['system']['phone']);
        if ($this->model->firstWhere('phone', $this->data['system']['phone'])) {
            throw new ApiException('手机号已存在', ApiErrorCode::ACCOUNT_REPEAT_ERROR);
        }

        if (isset($this->data['system']['email'])) {
            $this->data['system']['email'] = AES::encode($this->data['system']['email']);
        }

        $this->row = $this->model::create($this->data['system']);

        if (isset($this->data['custom'])) {
            foreach ($this->data['custom'] as $key => $value) {
                if (Field::firstWhere('name', $key)) {
                    Custom::updateOrCreate(['contacts_id' => $this->row['id'], 'name' => $key], ['value' => $value]);
                }
            }
        }
    }

    public function getPlaintext($id)
    {
        $this->row = $this->model->findOrFail($id)->append('plaintext_data');
        return $this->success($this->row);
    }

    public function import(Request $request)
    {
        Validator::make(
            $request->all(),
            [
                'file' => ['file'],
            ],
        );
        $file = $request->file('file');

        if (!$file || $file->getClientOriginalExtension() != 'csv') {
            throw new ApiException('参数错误', ApiErrorCode::VALIDATION_ERROR);
        }
        $path = $file->storePublicly('', 'uploads');
        $filePath = base_path() . '/storage/uploads/' . $path;

        $reader = new Csv();
        $reader->setInputEncoding('GB2312');

        $phpExcel = $reader->load($filePath);
        $excelSheet = $phpExcel->getSheet(0);
        $highestRow = $excelSheet->getHighestRow();
        $highestColumn = $excelSheet->getHighestColumn();

        $customField = Field::where('type', 'custom')->orderBy('id', 'ASC')->get();
        $systemField = Field::where('type', 'system')->orderBy('id', 'ASC')->get();

        for ($row = 2; $row <= $highestRow; $row++) {
            $contactsSystemDataIndex = 0;
            $systemFieldLengh =  count($systemField);

            $contactsCustomDataIndex = 0;
            $customFieldLengh =  count($customField);

            for ($col = 'A'; $col <= $highestColumn; $col++) {
                if ($contactsSystemDataIndex < $systemFieldLengh) {
                    $value = $this->getCellValue($excelSheet, $col . $row);
                    $name = $systemField[$contactsSystemDataIndex]['name'];
                    if ($name == 'phone' && $value == '') {
                        break;
                    } elseif (($name == 'phone' || $name == 'email') && $value != '') {
                        $contactsData[$name] = AES::encode($this->getCellValue($excelSheet, $col . $row));
                    } else {
                        $contactsData[$name] = $this->getCellValue($excelSheet, $col . $row);
                    }
                    $contactsSystemDataIndex++;
                } else {
                    break;
                }
            }
            if (isset($contactsData['phone'])) {
                $contactsData = array_filter($contactsData, fn ($value) => !is_null($value) && $value !== '');
                if ($contacts = $this->model->where(['phone' => $contactsData['phone']])->first()) {
                    $contacts->update($contactsData);
                } else {
                    $contactsData['channel'] = '后台导入';
                    $contacts = $this->model::create($contactsData);
                }

                if ($customField) {
                    for ($col; $col <= $highestColumn; $col++) {
                        $value = $this->getCellValue($excelSheet, $col . $row);
                        if ($contactsCustomDataIndex < $customFieldLengh) {
                            if ($value != '') {
                                Custom::updateOrCreate(['contacts_id' => $contacts['id'], 'name' => $customField[$contactsCustomDataIndex]['name']], ['value' => $value]);
                            }
                            $contactsCustomDataIndex++;
                        }
                    }
                }
            }
        }

        return $this->success();
    }

    public function importTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $row = Field::orderBy('id', 'ASC')->get();
        foreach ($row as $key => $value) {
            $startCell = chr($key + 65);
            $worksheet->setCellValue($startCell . '1', $value['label']);
        }

        header('Content-type:text/csv');
        header('Cache-Control: max-age=0');
        header('Content-Disposition: attachment;filename=User data template ' . date('Y-m-d') . '.csv');
        $objWriter = IOFactory::createWriter($spreadsheet, 'Csv');
        return $objWriter->save('php://output');
    }

    protected function getCellValue($excelSheet, $cell)
    {
        $value = $excelSheet->getCell($cell)->getValue();
        return $this->handleStr($value);
    }

    protected function handleStr($str)
    {
        $str = trim($str); //清除字符串两边的空格
        $str = preg_replace("/\t/", "", $str); //使用正则表达式替换内容，如：空格，换行，并将替换为空。
        $str = preg_replace("/\r\n/", "", $str);
        $str = preg_replace("/\r/", "", $str);
        $str = preg_replace("/\n/", "", $str);
        $str = preg_replace("/ /", "", $str);
        $str = preg_replace("/  /", "", $str);  //匹配html中的空格
        return trim($str); //返回字符串
    }
}
