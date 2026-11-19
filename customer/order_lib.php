<?php
/**
 * Order processing.
 *
 * Converts the customer's cart into farmer sales inside a single database
 * transaction. Stock is consumed FIFO from `farmer_crops_trade`, a row is
 * written to `farmer_history` for every farmer that supplied part of the
 * order, and the cart is cleared only when everything succeeded.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'cart_lib.php';

if (!function_exists('agri_place_order')) {
    /**
     * @return array{reference:string, date:string, items:array<int, array{name:string, quantity:int, price:float}>, total:float}
     * @throws RuntimeException when the cart is empty or stock changed.
     */
    function agri_place_order(mysqli $conn, int $customerId): array
    {
        $cartRows = agri_cart_fetch($conn, $customerId);
        if (!$cartRows) {
            throw new RuntimeException('Your cart is empty.');
        }

        $conn->begin_transaction();
        $invoiceItems = array();
        $grandTotal = 0.0;

        try {
            foreach ($cartRows as $cartRow) {
                $crop = $cartRow['cropname'];
                $quantity = (int)$cartRow['quantity'];

                if ($quantity <= 0) {
                    throw new RuntimeException('Remove ' . $crop . ' from the cart and add it again with a valid quantity.');
                }

                $quote = agri_quote_crop($conn, $crop, $quantity, true);
                if ($quote['available'] < $quantity) {
                    throw new RuntimeException(
                        'Only ' . $quote['available'] . ' kg of ' . $crop . ' is available right now. Please update the cart.'
                    );
                }

                foreach ($quote['allocations'] as $allocation) {
                    $history = $conn->prepare(
                        'INSERT INTO farmer_history (farmer_id, farmer_crop, farmer_quantity, farmer_price, date)
                         VALUES (?, ?, ?, ?, ?)'
                    );
                    if (!$history) {
                        throw new RuntimeException('Unable to record the sale. Please try again.');
                    }
                    $historyDate = date('d/m/Y');
                    $history->bind_param(
                        'isids',
                        $allocation['farmer_fkid'],
                        $crop,
                        $allocation['quantity'],
                        $allocation['total'],
                        $historyDate
                    );
                    if (!$history->execute()) {
                        $history->close();
                        throw new RuntimeException('Unable to record the sale. Please try again.');
                    }
                    $history->close();

                    $trade = $conn->prepare(
                        'SELECT Crop_quantity FROM farmer_crops_trade WHERE trade_id = ? FOR UPDATE'
                    );
                    if (!$trade) {
                        throw new RuntimeException('Unable to update stock. Please try again.');
                    }
                    $trade->bind_param('i', $allocation['trade_id']);
                    $trade->execute();
                    $trade->bind_result($stockLeft);
                    $found = $trade->fetch();
                    $trade->close();
                    if (!$found) {
                        throw new RuntimeException('Stock changed while placing the order. Please try again.');
                    }

                    if ((int)floor($stockLeft) <= $allocation['quantity']) {
                        $delete = $conn->prepare('DELETE FROM farmer_crops_trade WHERE trade_id = ?');
                        if (!$delete) {
                            throw new RuntimeException('Unable to update stock. Please try again.');
                        }
                        $delete->bind_param('i', $allocation['trade_id']);
                        $delete->execute();
                        $delete->close();
                    } else {
                        $update = $conn->prepare(
                            'UPDATE farmer_crops_trade SET Crop_quantity = Crop_quantity - ? WHERE trade_id = ?'
                        );
                        if (!$update) {
                            throw new RuntimeException('Unable to update stock. Please try again.');
                        }
                        $update->bind_param('di', $allocation['quantity'], $allocation['trade_id']);
                        $update->execute();
                        $update->close();
                    }
                }

                $production = $conn->prepare(
                    'UPDATE production_approx SET quantity = GREATEST(quantity - ?, 0) WHERE crop = ?'
                );
                if ($production) {
                    $production->bind_param('is', $quantity, $crop);
                    $production->execute();
                    $production->close();
                }

                $invoiceItems[] = array(
                    'name' => $crop,
                    'quantity' => $quantity,
                    'price' => $quote['total'],
                );
                $grandTotal += $quote['total'];
            }

            $clear = $conn->prepare('DELETE FROM cart WHERE cust_id = ?');
            if (!$clear) {
                throw new RuntimeException('Unable to clear the cart. Please try again.');
            }
            $clear->bind_param('i', $customerId);
            $clear->execute();
            $clear->close();

            $conn->commit();
        } catch (Throwable $exception) {
            $conn->rollback();
            throw $exception;
        }

        $invoice = array(
            'reference' => 'AGP-' . date('Ymd-His') . '-' . strtoupper(bin2hex(random_bytes(2))),
            'date' => date('d/m/Y H:i:s'),
            'items' => $invoiceItems,
            'total' => round($grandTotal, 2),
        );

        $_SESSION['last_order'] = $invoice;
        return $invoice;
    }
}
