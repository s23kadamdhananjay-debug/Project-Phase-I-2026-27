<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'csession.php';

$otp = isset($_POST['otp']) ? trim((string)$_POST['otp']) : '';
$customerId = (int)$_SESSION['customer_id'];

if ($otp === '') {
    echo 'not_exist';
    exit();
}

$stmt = $conn->prepare('SELECT cust_id FROM custlogin WHERE cust_id = ? AND otp = ? LIMIT 1');
if (!$stmt) {
    echo 'not_exist';
    exit();
}
$stmt->bind_param('is', $customerId, $otp);
$stmt->execute();
$stmt->bind_result($matchedId);
$found = $stmt->fetch();
$stmt->close();

if (!$found) {
    echo 'not_exist';
    exit();
}

$clear = $conn->prepare('UPDATE custlogin SET otp = 0 WHERE cust_id = ?');
if ($clear) {
    $clear->bind_param('i', $customerId);
    $clear->execute();
    $clear->close();
}

$_SESSION['IS_LOGIN'] = $user_check;
echo 'yes';
