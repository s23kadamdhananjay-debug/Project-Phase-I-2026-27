<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fsession.php';

header('Content-Type: text/plain; charset=utf-8');

$crops = isset($_POST['crops']) && is_string($_POST['crops']) ? trim($_POST['crops']) : '';
if ($crops === '') {
    echo '0';
    exit();
}

$stmt = $conn->prepare('SELECT AVG(costperkg) FROM farmer_crops_trade WHERE Trade_crop = ?');
$average = 0.0;
if ($stmt) {
    $stmt->bind_param('s', $crops);
    $stmt->execute();
    $stmt->bind_result($averageValue);
    if ($stmt->fetch() && $averageValue !== null) {
        $average = (float)$averageValue;
    }
    $stmt->close();
}

echo (string)(int)ceil($average);
