<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'csession.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'cart_lib.php';

header('Content-Type: application/json');

$crop = isset($_POST['crops']) && is_string($_POST['crops']) ? trim($_POST['crops']) : '';
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
$customerId = (int)$_SESSION['customer_id'];

if ($crop === '' || $quantity < 1) {
    echo json_encode(array('available' => false, 'message' => 'Select a crop and a valid quantity.'));
    exit();
}

$stock = agri_quote_crop($conn, $crop, 0);
$inCart = $conn->prepare('SELECT COALESCE(quantity, 0) FROM cart WHERE cust_id = ? AND cropname = ? LIMIT 1');
$reserved = 0;
if ($inCart) {
    $inCart->bind_param('is', $customerId, $crop);
    $inCart->execute();
    $inCart->bind_result($reserved);
    $inCart->fetch();
    $inCart->close();
}

$free = max(0, $stock['available'] - (int)$reserved);
$available = $free >= $quantity;

echo json_encode(array(
    'available' => $available,
    'remaining' => $free,
    'message' => $available ? '' : 'Only ' . $free . ' kg of ' . ucfirst($crop) . ' is available.',
));
