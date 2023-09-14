<?php

namespace LookstarKernel\Support\AI;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

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
