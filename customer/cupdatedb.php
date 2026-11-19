<?php
/**
 * Legacy order endpoint.
 *
 * Older templates posted directly to cupdatedb.php. It now simply processes
 * the signed-in customer's cart through the shared order pipeline and shows
 * the invoice, so the whole flow stays in one place.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'csession.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'order_lib.php';

$customerId = (int)$_SESSION['customer_id'];

try {
    agri_place_order($conn, $customerId);
} catch (Throwable $exception) {
    error_log('Order failed: ' . $exception->getMessage());
    $_SESSION['cart_error'] = $exception->getMessage();
    header('Location: cbuy_crops.php');
    exit();
}

header('Location: cmoney_transfered.php');
exit();
