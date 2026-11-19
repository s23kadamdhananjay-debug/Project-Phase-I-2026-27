<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['farmer_login_user']) || !is_string($_SESSION['farmer_login_user'])) {
    header('Location: ../index.php');
    exit();
}

require_once('../sql.php');
if (!$conn) {
    exit();
}

$user_check = $_SESSION['farmer_login_user'];
$stmt = $conn->prepare('SELECT farmer_name FROM farmerlogin WHERE email = ? LIMIT 1');
if (!$stmt) {
    http_response_code(500);
    exit('Unable to load farmer account.');
}

$stmt->bind_param('s', $user_check);
if (!$stmt->execute()) {
    $stmt->close();
    http_response_code(500);
    exit('Unable to load farmer account.');
}

$stmt->bind_result($farmerName);
$found = $stmt->fetch();
$stmt->close();

if (!$found) {
    session_unset();
    session_destroy();
    header('Location: ../index.php');
    exit();
}

$row = array('farmer_name' => $farmerName);
$login_session = $farmerName;
$CustID = $user_check;
?>
