<?php

namespace LookstarKernel\Application\Tenant\Push\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

trait JobTrait
{
    public $mail;
    public $taskModel;

    public function sendMailHandle()
    {
        $this->beforeSend();
        $this->init();
        $this->setFrom();
        $this->addAddress();
        $this->addCC();
        $this->message();
        $this->send();
    }

    protected function init()
    {
        $this->mail = new PHPMailer(true);
        $this->mail->SMTPDebug = SMTP::DEBUG_SERVER; //Enable verbose debug output
        $this->mail->isSMTP(); //Send using SMTP
        $this->mail->Host = $this->config['host']; //Set the SMTP server to send through
        $this->mail->SMTPAuth = true; //Enable SMTP authentication
        $this->mail->Username = $this->config['username']; //SMTP username
        $this->mail->Password = $this->config['password']; //SMTP password
        $this->mail->SMTPSecure = $this->config['verify_type']; //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $this->mail->Port = $this->config['port'];
        $this->mail->CharSet = 'utf8';
    }

    protected function setFrom()
    {
        if (empty($this->config['username_from'])) {
            $this->mail->setFrom("=?UTF-8?B?" . base64_encode($this->config['username']) . "?=");
        } else {
            $this->mail->setFrom($this->config['username'], "=?UTF-8?B?" . base64_encode($this->config['username_from']) . "?=");
        }
    }

    protected function addAddress()
    {
        if (is_string($this->to)) {
            $this->mail->addAddress($this->to); //Add a recipient
        } elseif (is_array($this->to)) {
            foreach ($this->to as $t) {
                $this->mail->addAddress($t); //Add a recipient
            }
        }
    }

    protected function addCC()
    {
        if (is_string($this->cc)) {
            $this->mail->addCC($this->cc); //Add a recipient
        } elseif (is_array($this->cc)) {
            foreach ($this->cc as $t) {
                $this->mail->addCC($t); //Add a recipient
            }
        }
    }

    protected function message()
    {
        $this->mail->isHTML(true);
        $this->mail->Subject = "=?UTF-8?B?" . base64_encode($this->subject) . "?=";
        $this->mail->Body = $this->body;
    }

    protected function send()
    {
        $this->mail->send();
    }

    protected function beforeSend()
    {
    }
}
