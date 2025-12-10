<?php
require __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to, $toName, $subject, $message) {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'gymmanagement05@gmail.com';
        $mail->Password = 'soqv jeoa eohk evej';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('gymmanagement05@gmail.com', 'FitZone Gym Management');
        $mail->addAddress($to, $toName);
        $mail->Subject = $subject;
        $mail->Body = $message;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>