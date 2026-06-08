<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

/* EMAIL SWITCH */

$EMAIL_ENABLED = false;

function sendPortalMail($to, $subject, $message)
{
    global $EMAIL_ENABLED;

    /* EMAIL DISABLED */

    if(!$EMAIL_ENABLED)
    {
        return true;
    }

    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        $mail->Username = '';
        $mail->Password = '';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom(
            'mitinternshipportal@gmail.com',
            'MIT Internship Portal'
        );

        $mail->addAddress($to);

        $mail->isHTML(true);

        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();

        return true;

    } catch (Exception $e) {

        return false;

    }
}
?>