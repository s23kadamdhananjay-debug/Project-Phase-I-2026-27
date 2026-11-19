<?php
/**
 * Small SMTP helper used for OTP and contact emails.
 *
 * Credentials come from .env so they are never hard-coded in the repository.
 * When SMTP is not configured the helper returns false and callers can show a
 * friendly message instead of exposing the raw mailer error.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

if (!function_exists('agri_send_mail')) {
    function agri_send_mail(string $to, string $subject, string $body): bool
    {
        $username = (string)agri_env('SMTP_USERNAME', '');
        $password = (string)agri_env('SMTP_PASSWORD', '');

        if ($username === '' || $password === '') {
            error_log('SMTP is not configured (SMTP_USERNAME / SMTP_PASSWORD missing).');
            return false;
        }

        $phpmailer = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'smtp' . DIRECTORY_SEPARATOR . 'class.phpmailer.php';
        if (!is_file($phpmailer)) {
            error_log('PHPMailer library is missing.');
            return false;
        }
        require_once $phpmailer;

        if (!class_exists('PHPMailer')) {
            error_log('PHPMailer class could not be loaded.');
            return false;
        }

        try {
            $mail = new PHPMailer();
            $mail->IsSMTP();
            $mail->SMTPDebug = 0;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = (string)agri_env('SMTP_SECURE', 'ssl');
            $mail->Host = (string)agri_env('SMTP_HOST', 'smtp.gmail.com');
            $mail->Port = (int)agri_env('SMTP_PORT', '465');
            $mail->IsHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Username = $username;
            $mail->Password = $password;
            $mail->SetFrom((string)agri_env('SMTP_FROM', $username), (string)agri_env('SMTP_FROM_NAME', 'Agriculture Portal'));
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AddAddress($to);

            if (!$mail->Send()) {
                error_log('SMTP send failed: ' . $mail->ErrorInfo);
                return false;
            }
            return true;
        } catch (Throwable $exception) {
            error_log('SMTP send failed: ' . $exception->getMessage());
            return false;
        }
    }
}
