<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fsession.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'mailer.php';

header('Content-Type: text/plain; charset=utf-8');

$email = $user_check;
$stmt = $conn->prepare('SELECT farmer_id FROM farmerlogin WHERE email = ? LIMIT 1');
if (!$stmt) {
    echo 'error';
    exit();
}
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->bind_result($farmerId);
$found = $stmt->fetch();
$stmt->close();

if (!$found) {
    echo 'not exist';
    exit();
}

$otp = random_int(100000, 999999);
$update = $conn->prepare('UPDATE farmerlogin SET otp = ? WHERE farmer_id = ?');
if (!$update) {
    echo 'error';
    exit();
}
$update->bind_param('ii', $otp, $farmerId);
$update->execute();
$update->close();

$sent = agri_send_mail(
    $email,
    'OTP Verification - Agriculture Portal',
    'Your OTP verification code for Agriculture Portal is <strong>' . $otp . '</strong>. It is valid for this login session.'
);

echo $sent ? 'yes' : 'mail_failed';
