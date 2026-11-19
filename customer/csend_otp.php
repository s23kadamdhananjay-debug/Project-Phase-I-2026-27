<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'csession.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'mailer.php';

header('Content-Type: text/plain; charset=utf-8');

$email = $user_check;
$customerId = (int)$_SESSION['customer_id'];

$otp = random_int(100000, 999999);
$update = $conn->prepare('UPDATE custlogin SET otp = ? WHERE cust_id = ?');
if (!$update) {
    echo 'error';
    exit();
}
$update->bind_param('ii', $otp, $customerId);
$update->execute();
$update->close();

$sent = agri_send_mail(
    $email,
    'OTP Verification - Agriculture Portal',
    'Your OTP verification code for Agriculture Portal is <strong>' . $otp . '</strong>.'
);

echo $sent ? 'yes' : 'mail_failed';
