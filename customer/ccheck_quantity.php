<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'csession.php';

header('Content-Type: application/json');

$crop = isset($_POST['crops']) && is_string($_POST['crops']) ? trim($_POST['crops']) : '';
if ($crop === '') {
    echo json_encode(array('quantityR' => 0, 'TradeIdR' => 0));
    exit();
}

$stmt = $conn->prepare(
    'SELECT COALESCE(SUM(Crop_quantity), 0) AS quantity, MIN(trade_id) AS trade_id
     FROM farmer_crops_trade
     WHERE Trade_crop = ? AND Crop_quantity > 0'
);
$quantity = 0;
$tradeId = 0;
if ($stmt) {
    $stmt->bind_param('s', $crop);
    $stmt->execute();
    $stmt->bind_result($quantity, $tradeId);
    $stmt->fetch();
    $stmt->close();
}

echo json_encode(array(
    'TradeIdR' => (int)$tradeId,
    'quantityR' => (int)floor((float)$quantity),
));
