<?php

namespace LookstarKernel\Application\Tenant\Push\Mail;

use PHPMailer\PHPMailer\PHPMailer as PHPMailerBase;

class PHPMailer
{
    public static function getClient($config)
    {
        $mail = new PHPMailerBase(true);
        $mail->isSMTP(); //Send using SMTP
        $mail->Host = $config['host']; //Set the SMTP server to send through
        $mail->SMTPAuth = true; //Enable SMTP authentication
        $mail->Username = $config['username']; //SMTP username
        $mail->Password = $config['password']; //SMTP password
        $mail->SMTPSecure = $config['verify_type']; //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port = $config['port'];
        $mail->CharSet = 'utf8';
        return $mail;
    }
}
