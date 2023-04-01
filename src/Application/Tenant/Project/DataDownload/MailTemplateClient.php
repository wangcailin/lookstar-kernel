<?php

namespace LookstarKernel\Application\Tenant\Project\DataDownload;

use LookstarKernel\Application\Tenant\Project\DataDownload\Models\MailTemplate;
use Composer\Http\Controller;
use LookstarKernel\Application\Tenant\Project\Traits\HasOneGet;
use LookstarKernel\Application\Tenant\Project\Traits\HasOneUpdateOrCreate;

class MailTemplateClient extends Controller
{
    use HasOneGet;
    use HasOneUpdateOrCreate;
    public function __construct(MailTemplate $mailTemplate)
    {
        $this->model = $mailTemplate;
    }
}
