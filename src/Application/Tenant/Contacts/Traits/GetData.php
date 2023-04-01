<?php

namespace LookstarKernel\Application\Tenant\Contacts\Traits;

use Composer\Support\Crypt\AES;

trait GetData
{
    public function getMaskData($data)
    {
        $maskData = [];
        if ($data) {
            foreach ($data as $key => $value) {
                if ($key == 'phone' || $key == 'email') {
                    $value = AES::decode($value);
                    $maskData[$key] = desensitize($value);
                }
            }
        }
        return $maskData;
    }

    public function getPlaintextData($data)
    {
        $plaintextData = [];
        if ($data) {
            foreach ($data as $key => $value) {
                if ($key == 'phone' || $key == 'email') {
                    $plaintextData[$key] = AES::decode($value);
                }
            }
        }
        return $plaintextData;
    }
}
