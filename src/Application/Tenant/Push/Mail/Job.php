<?php

namespace LookstarKernel\Application\Tenant\Push\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

class Job implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Dispatchable;

    use JobTrait;

    public $config;
    public $to;
    public $subject;
    public $body;
    public $cc;

    public function __construct($config, $to, $subject, $body, $cc = [])
    {
        $this->config = $config;
        $this->to = $to;
        $this->subject = $subject;
        $this->body = $body;
        $this->cc = $cc;
    }

    public function handle()
    {
        $this->sendMailHandle();
    }
}
