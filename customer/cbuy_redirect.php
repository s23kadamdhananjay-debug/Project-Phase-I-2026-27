<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'csession.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'cart_lib.php';

$customerId = (int)$_SESSION['customer_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['add_to_cart'])) {
    header('Location: cbuy_crops.php');
    exit();
}

$crop = isset($_POST['crops']) && is_string($_POST['crops']) ? trim($_POST['crops']) : '';
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

if ($crop === '' || $quantity < 1) {
    $_SESSION['cart_error'] = 'Select a crop and enter a quantity of at least 1 kg.';
    header('Location: cbuy_crops.php');
    exit();
}

$quote = agri_quote_crop($conn, $crop, $quantity);
if ($quote['available'] < $quantity) {
    $_SESSION['cart_error'] = $quote['available'] > 0
        ? 'Only ' . $quote['available'] . ' kg of ' . ucfirst($crop) . ' is available right now.'
        : ucfirst($crop) . ' is out of stock right now.';
    header('Location: cbuy_crops.php');
    exit();
}

$lineTotal = $quote['total'];

$existing = $conn->prepare('SELECT id FROM cart WHERE cust_id = ? AND cropname = ? LIMIT 1');
$existingId = 0;
if ($existing) {
    $existing->bind_param('is', $customerId, $crop);
    $existing->execute();
    $existing->bind_result($existingId);
    $existing->fetch();
    $existing->close();
}

if ($existingId > 0) {
    $update = $conn->prepare('UPDATE cart SET quantity = ?, price = ? WHERE id = ? AND cust_id = ?');
    if ($update) {
        $update->bind_param('idii', $quantity, $lineTotal, $existingId, $customerId);
        $update->execute();
        $update->close();
    }
} else {
    $insert = $conn->prepare('INSERT INTO cart (cust_id, cropname, quantity, price) VALUES (?, ?, ?, ?)');
    if ($insert) {
        $insert->bind_param('isid', $customerId, $crop, $quantity, $lineTotal);
        $insert->execute();
        $insert->close();
    }
}

// The database cart is the source of truth now; drop the legacy session cart.
unset($_SESSION['shopping_cart'], $_SESSION['Total_Cart_Price']);

$_SESSION['cart_message'] = ucfirst($crop) . ' (' . $quantity . ' kg) was added to your cart.';
header('Location: cbuy_crops.php');
exit();
