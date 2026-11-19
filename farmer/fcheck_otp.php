<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fsession.php';

$otp = isset($_POST['otp']) ? trim((string)$_POST['otp']) : '';
if ($otp === '') {
    echo 'not_exist';
    exit();
}

$stmt = $conn->prepare('SELECT farmer_id FROM farmerlogin WHERE email = ? AND otp = ? LIMIT 1');
if (!$stmt) {
    echo 'not_exist';
    exit();
}
$stmt->bind_param('ss', $user_check, $otp);
$stmt->execute();
$stmt->bind_result($farmerId);
$found = $stmt->fetch();
$stmt->close();

if (!$found) {
    echo 'not_exist';
    exit();
}

$clear = $conn->prepare('UPDATE farmerlogin SET otp = 0 WHERE farmer_id = ?');
if ($clear) {
    $clear->bind_param('i', $farmerId);
    $clear->execute();
    $clear->close();
}

$_SESSION['IS_LOGIN'] = $user_check;
echo 'yes';
