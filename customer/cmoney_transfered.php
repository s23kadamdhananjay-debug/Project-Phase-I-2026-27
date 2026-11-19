<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'csession.php';

if (!isset($_SESSION['last_order']) || !is_array($_SESSION['last_order'])) {
    header('Location: cbuy_crops.php');
    exit();
}

$order = $_SESSION['last_order'];
$customerId = (int)$_SESSION['customer_id'];

$customerStmt = $conn->prepare(
    'SELECT cust_name, email, phone_no, state, city, address, pincode FROM custlogin WHERE cust_id = ? LIMIT 1'
);
if (!$customerStmt) {
    http_response_code(500);
    exit('Unable to load the invoice.');
}
$customerStmt->bind_param('i', $customerId);
$customerStmt->execute();
$customerStmt->bind_result($customerName, $customerEmail, $customerPhone, $customerState, $customerCity, $customerAddress, $customerPincode);
if (!$customerStmt->fetch()) {
    $customerStmt->close();
    header('Location: cbuy_crops.php');
    exit();
}
$customerStmt->close();

$totalPrice = (float)$order['total'];
$orderReference = isset($order['reference']) ? $order['reference'] : '';
$orderDate = isset($order['date']) ? $order['date'] : date('d/m/Y H:i:s');
$paymentId = isset($order['payment_id']) ? $order['payment_id'] : '';
?>

<!DOCTYPE html>
<html>
<?php include ('cheader.php');  ?>
<style>
.invoice { background: #fff; padding: 20px }
.invoice-company { font-size: 20px }
.invoice-header { margin: 0 -20px; background: #f0f3f4; padding: 20px }
.invoice-date, .invoice-from, .invoice-to { display: table-cell; width: 1% }
.invoice-from, .invoice-to { padding-right: 20px }
.invoice-date .date, .invoice-from strong, .invoice-to strong { font-size: 16px; font-weight: 600 }
.invoice-date { text-align: right; padding-left: 20px }
.invoice-price { background: #f0f3f4; display: table; width: 100% }
.invoice-price .invoice-price-left, .invoice-price .invoice-price-right { display: table-cell; padding: 20px; font-size: 20px; font-weight: 600; width: 75%; position: relative; vertical-align: middle }
.invoice-price .invoice-price-left .sub-price { display: table-cell; vertical-align: middle; padding: 0 20px }
.invoice-price small { font-size: 12px; font-weight: 400; display: block }
.invoice-price .invoice-price-right { width: 25%; background: #2d353c; color: #fff; font-size: 28px; text-align: right; vertical-align: bottom; font-weight: 300 }
.invoice-price .invoice-price-right small { display: block; opacity: .6; position: absolute; top: 10px; left: 10px; font-size: 12px }
.invoice-footer { border-top: 1px solid #ddd; padding-top: 10px; font-size: 10px }
.invoice > div:not(.invoice-footer) { margin-bottom: 20px }
.btn.btn-white, .btn.btn-white.disabled, .btn.btn-white.disabled:focus, .btn.btn-white.disabled:hover, .btn.btn-white[disabled], .btn.btn-white[disabled]:focus, .btn.btn-white[disabled]:hover { color: #2d353c; background: #fff; border-color: #d9dfe3; }
</style>
  <body class="bg-white" id="top">

<?php include ('cnav.php');  ?>

  <section class="section section-shaped section-lg">
    <div class="shape shape-style-1 shape-primary">
      <span></span><span></span><span></span><span></span><span></span>
      <span></span><span></span><span></span><span></span><span></span>
    </div>

<div class="container">

    <div class="row">
      <div class="col-md-8 mx-auto text-center">
        <span class="badge badge-danger badge-pill mb-3">Invoice</span>
      </div>
    </div>

    <div class="row row-content">
      <div class="col-md-12 mb-3">
        <div class="card text-dark bg-gradient-secondary mb-3">
          <div class="card-header">
            <span class="text-warning display-4">Invoice</span>
          </div>
          <div class="card-body">
            <div class="invoice">
              <div class="invoice-company text-inverse f-w-600">
                <span class="pull-right hidden-print">
                  <a href="javascript:;" onclick="window.print()" class="btn btn-sm btn-white m-b-10 p-l-5"><i class="fa fa-print fa-fw fa-lg"></i> Print</a>
                  <a class="btn btn-sm btn-default m-b-10 p-l-5" href="cprofile.php"><i class="fa fa-rotate-left fa-fw fa-lg"></i> Home</a>
                </span>
                Agriculture Portal
              </div>

              <div class="invoice-header">
                <div class="invoice-to">
                  <small>To</small>
                  <address class="m-t-5 m-b-5">
                    <strong class="text-inverse"><?php echo htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8'); ?></strong><br>
                    <?php echo htmlspecialchars($customerAddress, ENT_QUOTES, 'UTF-8'); ?><br>
                    <?php echo htmlspecialchars($customerCity . ', ' . $customerPincode, ENT_QUOTES, 'UTF-8'); ?><br>
                    Phone: <?php echo htmlspecialchars($customerPhone, ENT_QUOTES, 'UTF-8'); ?><br>
                    <?php echo htmlspecialchars($customerEmail, ENT_QUOTES, 'UTF-8'); ?>
                  </address>
                </div>
                <div class="invoice-date">
                  <small>Invoice</small>
                  <div class="date text-inverse m-t-5"><?php echo htmlspecialchars($orderDate, ENT_QUOTES, 'UTF-8'); ?></div>
                  <div class="invoice-detail">
                    <?php echo htmlspecialchars($orderReference, ENT_QUOTES, 'UTF-8'); ?><br>
                    <?php if ($paymentId !== ''): ?>
                      Payment: <?php echo htmlspecialchars($paymentId, ENT_QUOTES, 'UTF-8'); ?><br>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <div class="invoice-content">
                <div class="table-responsive">
                  <table class="table table-invoice">
                    <thead>
                      <tr>
                        <th>Product Name</th>
                        <th class="text-center" width="10%">Quantity</th>
                        <th class="text-center" width="20%">Price</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($order['items'] as $item): ?>
                        <tr>
                          <td class="text-inverse"><?php echo htmlspecialchars(ucfirst($item['name']), ENT_QUOTES, 'UTF-8'); ?></td>
                          <td class="text-center"><?php echo (int)$item['quantity']; ?> KG</td>
                          <td class="text-center">Rs. <?php echo number_format((float)$item['price'], 2); ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>

                <div class="invoice-price">
                  <div class="invoice-price-left">
                    <div class="invoice-price-row">
                      <p>Amount Paid</p>
                    </div>
                  </div>
                  <div class="invoice-price-right">
                    <small>TOTAL</small> <span class="f-w-600">Rs.&nbsp;<?php echo number_format($totalPrice, 2); ?></span>
                  </div>
                </div>
              </div>

              <div class="invoice-footer">
                <p class="text-center m-b-5 f-w-600">THANK YOU FOR YOUR BUSINESS</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require("footer.php");?>

</body>
</html>
