<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once('../sql.php');

if (!isset($_SESSION['customer_login_user']) || !is_string($_SESSION['customer_login_user'])) {
    header('Location: ../index.php');
    exit();
}

$user_check = $_SESSION['customer_login_user'];

$stmt = $conn->prepare('SELECT cust_id, cust_name FROM custlogin WHERE email = ? LIMIT 1');
if (!$stmt) {
    http_response_code(500);
    exit('Unable to load customer account.');
}
$stmt->bind_param('s', $user_check);
if (!$stmt->execute()) {
    $stmt->close();
    http_response_code(500);
    exit('Unable to load customer account.');
}
$stmt->bind_result($customerId, $customerName);
$found = $stmt->fetch();
$stmt->close();

if (!$found) {
    session_unset();
    session_destroy();
    header('Location: ../index.php');
    exit();
}

$login_session = $customerName;
$CustID = $user_check;
$_SESSION['customer_id'] = (int)$customerId;
