<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'csession.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'cart_lib.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'order_lib.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['place_order'])) {
    header('Location: cbuy_crops.php');
    exit();
}

$customerId = (int)$_SESSION['customer_id'];
$cart = agri_cart_details($conn, $customerId);

if (empty($cart['items'])) {
    $_SESSION['cart_error'] = 'Your cart is empty. Add a crop before placing an order.';
    header('Location: cbuy_crops.php');
    exit();
}

if ($cart['has_shortage']) {
    $_SESSION['cart_error'] = 'Some items in your cart are no longer available in the requested quantity. Please update the cart.';
    header('Location: cbuy_crops.php');
    exit();
}

$secretKey = (string)agri_env('STRIPE_SECRET_KEY', '');
$publishableKey = (string)agri_env('STRIPE_PUBLISHABLE_KEY', '');
$demoMode = agri_env_bool('STRIPE_DEMO_MODE', true);

$stripeReady = !$demoMode
    && strpos($secretKey, 'sk_') === 0
    && strpos($publishableKey, 'pk_') === 0
    && strpos($secretKey, 'xxx') === false
    && strpos($publishableKey, 'xxx') === false;

if (!$stripeReady) {
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
}

$stripeInit = __DIR__ . DIRECTORY_SEPARATOR . 'StripePayment' . DIRECTORY_SEPARATOR . 'stripe-php-master' . DIRECTORY_SEPARATOR . 'init.php';
if (!is_file($stripeInit)) {
    $_SESSION['cart_error'] = 'The Stripe library is missing. Set STRIPE_DEMO_MODE=1 in .env to use demo checkout.';
    header('Location: cbuy_crops.php');
    exit();
}
require_once $stripeInit;

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$directory = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$successUrl = $scheme . '://' . $host . $directory . '/cpaid.php?session_id={CHECKOUT_SESSION_ID}';
$cancelUrl = $scheme . '://' . $host . $directory . '/cbuy_crops.php';

try {
    \Stripe\Stripe::setApiKey($secretKey);

    $checkoutSession = \Stripe\Checkout\Session::create(array(
        'mode' => 'payment',
        'payment_method_types' => array('card'),
        'client_reference_id' => (string)$customerId,
        'customer_email' => $user_check,
        'line_items' => array(
            array(
                'quantity' => 1,
                'price_data' => array(
                    'currency' => 'inr',
                    'unit_amount' => (int)round($cart['total'] * 100),
                    'product_data' => array(
                        'name' => 'Agriculture Portal crop order',
                        'description' => count($cart['items']) . ' item(s) from the portal marketplace',
                    ),
                ),
            ),
        ),
        'metadata' => array(
            'customer_id' => (string)$customerId,
        ),
        'success_url' => $successUrl,
        'cancel_url' => $cancelUrl,
    ));

    header('Location: ' . $checkoutSession->url);
    exit();
} catch (Throwable $exception) {
    error_log('Stripe checkout failed: ' . $exception->getMessage());
    $_SESSION['cart_error'] = 'Online payment is unavailable right now. Please try again later.';
    header('Location: cbuy_crops.php');
    exit();
}
