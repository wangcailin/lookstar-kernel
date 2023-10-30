<?php

namespace LookstarKernel\Application\Tenant\Project\Contacts\Models;

use LookstarKernel\Support\Eloquent\TenantModel as Model;


class EmailConfig extends Model
{
    const SEND_TYPE_RECEIVE = 1; //接收发送

    protected $table = 'tenant_project_contacts_email_config';

    protected $fillable = [
        'tenant_id',
        'project_id',
        'status',
        'title',
        'alisa_title',
        'emails',
        'cc_emails',
        'send_type',
        'send_time',
        'content',
        'fields_data',
    ];

    protected $casts = [
        'emails' => 'json',
        'cc_emails' => 'json',
        'fields_data' => 'json',
    ];

    public $jsonCasts = ['emails', 'cc_emails', 'fields_data'];
}
