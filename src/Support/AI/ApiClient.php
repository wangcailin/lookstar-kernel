<?php

namespace LookstarKernel\Support\AI;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ApiClient
{
    public static $domain = 'https://ai.lookstar.com.cn';

    public static function ssePostStream($path, $data, $header = ['Content-Type' => 'application/json'])
    {
        $client = new Client();
        $response = $client->post(self::$domain . $path, [
            'stream' => true,  // This is crucial for handling the stream
            'json' => $data
        ]);
        $stream = $response->getBody();
        return $stream;
    }


    public static function post($path, $data, $header = ['Content-Type' => 'application/json',])
    {
        $response = Http::timeout(60)->withHeaders($header)->post(
            self::$domain . $path,
            $data
        );
        if ($response->successful()) {
            return $response->json();
        } else {
            $statusCode = $response->status(); // 获取 HTTP 状态码
            $body = $response->body(); // 获取响应内容
            Log::info('******************************************************************');
            Log::info($body);
            Log::info($statusCode);
            Log::info('------------------------------------------------------------------');
            return false;
        }
    }

    public static function delete($path, $data, $header = ['Content-Type' => 'application/json',])
    {
        $response = Http::timeout(60)->withHeaders($header)->delete(
            self::$domain . $path,
            $data
        );
        return $response->json();
    }
}
