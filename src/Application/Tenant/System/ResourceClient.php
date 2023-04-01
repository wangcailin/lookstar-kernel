<?php

namespace LookstarKernel\Application\Tenant\System;

use Composer\Http\Controller;
use LookstarKernel\Application\Tenant\System\Models\Resource;
use Composer\Exceptions\ApiErrorCode;
use Composer\Exceptions\ApiException;
use Composer\Support\Aliyun\OssClient;

class ResourceClient extends Controller
{
    public function __construct(Resource $resource)
    {
        $this->model = $resource;
    }

    public function create()
    {
        $request = request();
        $file = $request->file('file');
        $appSource = $request->input('app_source', '');

        if ($file) {
            $fileName = $file->getClientOriginalName();
            $fileMimeType = $file->getClientMimeType();
            $fileExtension = $file->extension();
            $fileSize = $file->getSize();
            $fileUid = uniqid();
            $filePath = 'tenant/uploads/' . date('Ymd') . '/' . $fileUid . '.' . $fileExtension;
            $result = OssClient::putObject($filePath, $file->get());
            if ($result && isset($result['info']) && isset($result['info']['url'])) {
                $data = [
                    'title' => $fileName,
                    'mime_type' => $fileMimeType,
                    'extension' => $fileExtension,
                    'size' => $fileSize,
                    'url' => $result['info']['url'],
                    'app_source' => $appSource,
                ];
                $this->model::create($data);
                return $this->success(['url' => $result['info']['url'], 'uid' => $fileUid]);
            }
        }
        throw new ApiException('上传失败', ApiErrorCode::VALIDATION_ERROR);
    }
}
