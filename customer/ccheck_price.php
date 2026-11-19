<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'csession.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'cart_lib.php';

header('Content-Type: application/json');

$crop = isset($_POST['crops']) && is_string($_POST['crops']) ? trim($_POST['crops']) : '';
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

if ($crop === '' || $quantity < 1) {
    echo json_encode(array('ok' => false, 'total' => 0, 'available' => 0, 'message' => 'Select a crop and a valid quantity.'));
    exit();
}

$quote = agri_quote_crop($conn, $crop, $quantity);

echo json_encode(array(
    'ok' => $quote['available'] >= $quantity,
    'total' => $quote['total'],
    'available' => $quote['available'],
    'message' => $quote['available'] >= $quantity ? '' : 'Only ' . $quote['available'] . ' kg available.',
));
