<?php

namespace LookstarKernel\Application\Tenant\Project\DataDownload\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;

class MailTemplate extends Model
{
    protected $table = 'tenant_project_data_download_mail_template';

    protected $fillable = [
        'project_id',
        'subject',
        'from_name',
        'header',
        'footer',
    ];
}
