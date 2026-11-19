<?php
/**
 * Shopping cart helpers.
 *
 * Every query is scoped to a single customer and the price of a cart line is
 * always recalculated from the live farmer listings, so a customer can never
 * change the amount that is charged by editing the form.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'config.php';

if (!function_exists('agri_cart_fetch')) {
    /**
     * @return array<int, array{id:int, cropname:string, quantity:int, price:float}>
     */
    function agri_cart_fetch(mysqli $conn, int $customerId): array
    {
        $rows = array();
        $stmt = $conn->prepare('SELECT id, cropname, quantity, price FROM cart WHERE cust_id = ? ORDER BY id ASC');
        if (!$stmt) {
            return $rows;
        }
        $stmt->bind_param('i', $customerId);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $rows[] = array(
                    'id' => (int)$row['id'],
                    'cropname' => (string)$row['cropname'],
                    'quantity' => (int)$row['quantity'],
                    'price' => (float)$row['price'],
                );
            }
        }
        $stmt->close();
        return $rows;
    }
}

if (!function_exists('agri_stock_rows')) {
    /**
     * Returns the live listings for a crop ordered oldest first (FIFO).
     *
     * @return array<int, array{trade_id:int, farmer_fkid:int, quantity:float, costperkg:float, msp:float}>
     */
    function agri_stock_rows(mysqli $conn, string $crop, bool $forUpdate = false): array
    {
        $rows = array();
        $sql = 'SELECT trade_id, farmer_fkid, Crop_quantity, costperkg, msp
                FROM farmer_crops_trade
                WHERE Trade_crop = ? AND Crop_quantity > 0
                ORDER BY trade_id ASC';
        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return $rows;
        }
        $stmt->bind_param('s', $crop);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $rows[] = array(
                    'trade_id' => (int)$row['trade_id'],
                    'farmer_fkid' => (int)$row['farmer_fkid'],
                    'quantity' => (float)$row['Crop_quantity'],
                    'costperkg' => (float)$row['costperkg'],
                    'msp' => (float)$row['msp'],
                );
            }
        }
        $stmt->close();
        return $rows;
    }
}

if (!function_exists('agri_quote_crop')) {
    /**
     * Prices the requested quantity across all live listings (FIFO).
     *
     * @return array{total:float, available:int, allocations:array<int, array{trade_id:int, farmer_fkid:int, quantity:int, msp:float, total:float}>}
     */
    function agri_quote_crop(mysqli $conn, string $crop, int $quantity, bool $forUpdate = false): array
    {
        $stock = agri_stock_rows($conn, $crop, $forUpdate);
        $available = 0;
        foreach ($stock as $row) {
            $available += (int)floor($row['quantity']);
        }

        $remaining = max(0, $quantity);
        $total = 0.0;
        $allocations = array();

        foreach ($stock as $row) {
            if ($remaining <= 0) {
                break;
            }

            $take = (int)min($remaining, floor($row['quantity']));
            if ($take <= 0) {
                continue;
            }

            $lineTotal = round($take * $row['msp'], 2);
            $allocations[] = array(
                'trade_id' => $row['trade_id'],
                'farmer_fkid' => $row['farmer_fkid'],
                'quantity' => $take,
                'msp' => $row['msp'],
                'total' => $lineTotal,
            );
            $total += $lineTotal;
            $remaining -= $take;
        }

        return array(
            'total' => round($total, 2),
            'available' => $available,
            'allocations' => $allocations,
        );
    }
}

if (!function_exists('agri_cart_details')) {
    /**
     * Loads the cart rows with a fresh quote for each line.
     *
     * @return array{items:array<int, array>, total:float, has_shortage:bool}
     */
    function agri_cart_details(mysqli $conn, int $customerId): array
    {
        $items = array();
        $total = 0.0;
        $hasShortage = false;

        foreach (agri_cart_fetch($conn, $customerId) as $row) {
            $quote = agri_quote_crop($conn, $row['cropname'], $row['quantity']);
            $lineTotal = $quote['total'];

            if ($quote['available'] < $row['quantity']) {
                $hasShortage = true;
            } else {
                $total += $lineTotal;
            }

            $items[] = array(
                'id' => $row['id'],
                'cropname' => $row['cropname'],
                'quantity' => $row['quantity'],
                'price' => $lineTotal,
                'available' => $quote['available'],
                'shortage' => $quote['available'] < $row['quantity'],
            );

            $update = $conn->prepare('UPDATE cart SET price = ? WHERE id = ? AND cust_id = ?');
            if ($update) {
                $update->bind_param('dii', $lineTotal, $row['id'], $customerId);
                $update->execute();
                $update->close();
            }
        }

        return array(
            'items' => $items,
            'total' => round($total, 2),
            'has_shortage' => $hasShortage,
        );
    }
}

if (!function_exists('agri_cart_count')) {
    function agri_cart_count(mysqli $conn, int $customerId): int
    {
        $stmt = $conn->prepare('SELECT COALESCE(SUM(quantity), 0) AS total FROM cart WHERE cust_id = ?');
        if (!$stmt) {
            return 0;
        }
        $stmt->bind_param('i', $customerId);
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();
        return (int)$total;
    }
}
