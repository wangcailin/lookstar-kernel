<?php

namespace LookstarKernel\Application\Tenant\HubSpot;

use LookstarKernel\Application\Tenant\Contacts\Models\Field;
use LookstarKernel\Application\Tenant\HubSpot\Models\ContactsFieldHubspot;
use Composer\Http\BaseController;
use Illuminate\Http\Request;

class ContactsFieldClient extends BaseController
{
    public function getAllList()
    {
        $list = Field::get();
        foreach ($list as $key => $value) {
            if ($hubspot = ContactsFieldHubspot::firstWhere('contacts_field_name', $value['name'])) {
                $list[$key]['hubspot_field_name'] = $hubspot['hubspot_field_name'];
                $list[$key]['hubspot_field_label'] = $hubspot['hubspot_field_label'];
                $list[$key]['hubspot_field_type'] = $hubspot['hubspot_field_type'];
            }
        }
        return $this->success($list);
    }

    public function updateOrCreate(Request $request)
    {
        $contacts = $request->only(['contacts_field_name']);
        $hubspot = $request->only(['hubspot_field_name', 'hubspot_field_label', 'hubspot_field_type']);
        ContactsFieldHubspot::updateOrCreate($contacts, $hubspot);
        return $this->success();
    }
}
