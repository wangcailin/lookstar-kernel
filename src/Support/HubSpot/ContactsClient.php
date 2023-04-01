<?php

namespace LookstarKernel\Support\HubSpot;

use HubSpot\Client\Auth\OAuth\ApiException;
use Illuminate\Support\Facades\Http;

class ContactsClient
{
    public static function updateOrCreate($properties, $email)
    {
        try {
            $apiResponse = Http::withHeaders([
                'content-type' => 'application/json',
                'authorization' => 'Bearer ' . Client::getAccessToken()
            ])->post('https://api.hubapi.com/contacts/v1/contact/createOrUpdate/email/' . $email, $properties)->json();
            var_dump($apiResponse);
        } catch (ApiException $e) {
            echo "Exception when calling batch_api->update: ", $e->getMessage();
        }
    }
}
