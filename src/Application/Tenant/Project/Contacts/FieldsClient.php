<?php

namespace LookstarKernel\Application\Tenant\Project\Contacts;

use LookstarKernel\Application\Tenant\Project\Contacts\Models\ContactsFields;
use Composer\Http\Controller;
use LookstarKernel\Application\Tenant\Project\Traits\HasOneGet;
use LookstarKernel\Application\Tenant\Project\Traits\HasOneUpdateOrCreate;

class FieldsClient extends Controller
{
    use HasOneGet;
    use HasOneUpdateOrCreate;
    public function __construct(ContactsFields $contactsFields)
    {
        $this->model = $contactsFields;
    }
}
