<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {

    $mail->isSMTP();

    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    $mail->Username = 'darekarwardhan@gmail.com';
    $mail->Password = 'jibj othy ntjo zehd';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom(
        'darekarwardhan@gmail.com',
        'MIT Internship Portal'
    );

    $mail->addAddress('darekarwardhan@gmail.com');

    $mail->isHTML(true);

    $mail->Subject = 'PHPMailer Test';
    $mail->Body = 'Email sending is working successfully!';

    $mail->send();

    echo "Email sent successfully";

} catch (Exception $e) {

    echo "Error: " . $mail->ErrorInfo;

}
?>