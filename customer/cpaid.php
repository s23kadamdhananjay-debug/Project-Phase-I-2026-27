<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'csession.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'order_lib.php';

$sessionId = isset($_GET['session_id']) && is_string($_GET['session_id']) ? trim($_GET['session_id']) : '';
if ($sessionId === '') {
    header('Location: cbuy_crops.php');
    exit();
}

$stripeInit = __DIR__ . DIRECTORY_SEPARATOR . 'StripePayment' . DIRECTORY_SEPARATOR . 'stripe-php-master' . DIRECTORY_SEPARATOR . 'init.php';
$secretKey = (string)agri_env('STRIPE_SECRET_KEY', '');

if (!is_file($stripeInit) || $secretKey === '') {
    $_SESSION['cart_error'] = 'Unable to confirm the payment because Stripe is not configured.';
    header('Location: cbuy_crops.php');
    exit();
}

require_once $stripeInit;

try {
    \Stripe\Stripe::setApiKey($secretKey);
    $checkoutSession = \Stripe\Checkout\Session::retrieve($sessionId);
} catch (Throwable $exception) {
    error_log('Stripe session lookup failed: ' . $exception->getMessage());
    $_SESSION['cart_error'] = 'Unable to confirm the payment. Please contact support if you were charged.';
    header('Location: cbuy_crops.php');
    exit();
}

if (!isset($checkoutSession->payment_status) || $checkoutSession->payment_status !== 'paid') {
    $_SESSION['cart_error'] = 'The payment was not completed. Your cart is still available.';
    header('Location: cbuy_crops.php');
    exit();
}

$customerId = (int)$_SESSION['customer_id'];
if ((string)$checkoutSession->client_reference_id !== (string)$customerId) {
    $_SESSION['cart_error'] = 'This payment does not belong to your account.';
    header('Location: cbuy_crops.php');
    exit();
}

try {
    $invoice = agri_place_order($conn, $customerId);
    $invoice['payment_id'] = $checkoutSession->id;
    $_SESSION['last_order'] = $invoice;
} catch (Throwable $exception) {
    error_log('Paid order could not be fulfilled: ' . $exception->getMessage());
    $_SESSION['cart_error'] = 'Your payment succeeded but the order could not be completed automatically. Please contact support with payment ID ' . $checkoutSession->id . '.';
    header('Location: cbuy_crops.php');
    exit();
}

header('Location: cmoney_transfered.php');
exit();
