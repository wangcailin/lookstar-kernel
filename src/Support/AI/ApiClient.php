<?php

namespace LookstarKernel\Support\AI;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ApiClient
{
    public static $domain = 'https://ai.lookstar.com.cn';
    public const HEADER_AUTHORIZATION = 'whHMKOHrgn90cRAFC75/6AmK+r0k12Hd02q6HAJVaaQSzpzBmeK1bUzQgBLPl9Hz';
    public const HEADER_CONTENT_TYPE = 'application/json';

    public static $header = [
        'Authorization' => self::HEADER_AUTHORIZATION,
        'Content-Type' => self::HEADER_CONTENT_TYPE,
    ];


    public static function ssePostStream($path, $data, $header = [])
    {
        $client = new Client();
        $response = $client->post(self::$domain . $path, [
            'stream' => true,  // This is crucial for handling the stream
            'json' => $data,
            'headers' => $header ?: self::$header,
        ]);
        $stream = $response->getBody();
        return $stream;
    }


    public static function post($path, $data, $header = [])
    {
        $response = Http::timeout(60)->withHeaders($header ?: self::$header)->post(
            self::$domain . $path,
            $data
        );
        Log::info('AI保存或者更新文件，请求的数据是' . json_encode($data));
        if ($response->successful()) {
            return $response->json();
        } else {
            $statusCode = $response->status(); // 获取 HTTP 状态码
            $body = $response->body(); // 获取响应内容
            return false;
        }
    }

    public static function delete($path, $data, $header = [])
    {
        Log::info('AI删除源文件，请求的数据是' . json_encode($data));
        $response = Http::timeout(60)->withHeaders($header ?: self::$header)->delete(
            self::$domain . $path,
            $data
        );
        return $response->json();
    }
}
