<?php

namespace LookstarKernel\Application\Tenant\HubSpot;

use LookstarKernel\Support\HubSpot\Client;
use Composer\Http\BaseController;
use Illuminate\Http\Request;

class PropertiesClient extends BaseController
{
    public function get(Request $request)
    {
        $objectType = $request->input('object_type', 'contact');
        $result = Client::createFactory()->crm()->properties()->coreApi()->getAll($objectType);
        $data = [];
        foreach ($result['results'] as $key => $value) {
            $data[] = ['label' => $value['label'], 'value' => $value['name']];
        }
        return $this->success($data);
    }
}
