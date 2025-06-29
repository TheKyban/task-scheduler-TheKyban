<?php
require_once __DIR__ . '/phpmailer/PHPMailer.php';
require_once __DIR__ . '/phpmailer/SMTP.php';
require_once __DIR__ . '/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function loadEnv(string $path): void
{
    if (!file_exists($path))
        return;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#'))
            continue;

        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}


function sendMail(string $to, string $subject, string $body, bool $isHtml = false): bool
{
    try {
        // Load environment variables
        loadEnv(__DIR__ . '/../.env');
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->SMTPDebug = 2;
        $mail->Debugoutput = 'html';

        $mail->setFrom($_ENV['EMAIL_USER'], 'Task Planner');
        $mail->addAddress($to);
        $mail->Subject = $subject;
        if ($isHtml) {
            $mail->isHTML(true);
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body); // fallback
        } else {
            $mail->Body = $body;
        }
        return $mail->send();
    } catch (Exception $e) {
        echo "Mailer Error: " . $mail->ErrorInfo;
        return false;
    }
}
