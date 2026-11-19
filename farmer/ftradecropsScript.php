<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fsession.php';

if (!isset($_POST['Crop_submit'])) {
    header('Location: ftradecrops.php');
    exit();
}

$crop = isset($_POST['crops']) && is_string($_POST['crops']) ? trim($_POST['crops']) : '';
$quantity = isset($_POST['trade_farmer_cropquantity']) ? (float)$_POST['trade_farmer_cropquantity'] : 0;
$costPerKg = isset($_POST['trade_farmer_cost']) ? (float)$_POST['trade_farmer_cost'] : 0;

$allowedCrops = array('arhar', 'bajra', 'barley', 'cotton', 'gram', 'jowar', 'jute', 'lentil', 'maize', 'moong', 'ragi', 'rice', 'soyabean', 'urad', 'wheat');
if (!in_array($crop, $allowedCrops, true) || $quantity <= 0 || $costPerKg < 0) {
    echo "<script type='text/javascript'>alert('Select a crop and enter valid quantity and cost values.'); window.location='ftradecrops.php';</script>";
    exit();
}

$farmerStmt = $conn->prepare('SELECT farmer_id FROM farmerlogin WHERE email = ? LIMIT 1');
if (!$farmerStmt) {
    echo "<script type='text/javascript'>alert('Unable to add the crop right now.'); window.location='ftradecrops.php';</script>";
    exit();
}
$farmerStmt->bind_param('s', $user_check);
$farmerStmt->execute();
$farmerStmt->bind_result($farmerId);
$found = $farmerStmt->fetch();
$farmerStmt->close();

if (!$found) {
    echo "<script type='text/javascript'>alert('Farmer account not found.'); window.location='ftradecrops.php';</script>";
    exit();
}

$conn->begin_transaction();

try {
    $msp = (int)ceil($costPerKg + ceil($costPerKg * 0.5));
    if ($msp < 1) {
        $msp = 1;
    }

    $insert = $conn->prepare(
        'INSERT INTO farmer_crops_trade (farmer_fkid, Trade_crop, Crop_quantity, costperkg, msp)
         VALUES (?, ?, ?, ?, ?)'
    );
    if (!$insert) {
        throw new RuntimeException('Unable to add the crop.');
    }
    $insert->bind_param('isdii', $farmerId, $crop, $quantity, $costPerKg, $msp);
    if (!$insert->execute()) {
        $insert->close();
        throw new RuntimeException('Unable to add the crop.');
    }
    $insert->close();

    // Recalculate the market support price from the average cost of every
    // listing for this crop so all farmers see the same price.
    $averageStmt = $conn->prepare('SELECT AVG(costperkg) FROM farmer_crops_trade WHERE Trade_crop = ?');
    $average = $costPerKg;
    if ($averageStmt) {
        $averageStmt->bind_param('s', $crop);
        $averageStmt->execute();
        $averageStmt->bind_result($averageValue);
        if ($averageStmt->fetch() && $averageValue !== null) {
            $average = (float)$averageValue;
        }
        $averageStmt->close();
    }

    $newMsp = (int)ceil($average + ceil($average * 0.5));
    if ($newMsp < 1) {
        $newMsp = 1;
    }

    $updateMsp = $conn->prepare('UPDATE farmer_crops_trade SET msp = ? WHERE Trade_crop = ?');
    if ($updateMsp) {
        $updateMsp->bind_param('is', $newMsp, $crop);
        $updateMsp->execute();
        $updateMsp->close();
    }

    $production = $conn->prepare(
        'INSERT INTO production_approx (crop, quantity) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)'
    );
    if ($production) {
        $wholeQuantity = (int)floor($quantity);
        $production->bind_param('si', $crop, $wholeQuantity);
        $production->execute();
        $production->close();
    }

    $conn->commit();
} catch (Throwable $exception) {
    $conn->rollback();
    error_log('Unable to add crop stock: ' . $exception->getMessage());
    echo "<script type='text/javascript'>alert('Unable to add the crop right now. Please try again.'); window.location='ftradecrops.php';</script>";
    exit();
}

echo "<script type='text/javascript'>alert('Crop Details Added Successfully'); window.location='ftradecrops.php';</script>";
