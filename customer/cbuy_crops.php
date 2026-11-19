<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'csession.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'cart_lib.php';

$customerId = (int)$_SESSION['customer_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $cartId = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
    $delete = $conn->prepare('DELETE FROM cart WHERE id = ? AND cust_id = ?');
    if ($delete) {
        $delete->bind_param('ii', $cartId, $customerId);
        $delete->execute();
        $delete->close();
        $_SESSION['cart_message'] = 'Item removed from your cart.';
    }
    header('Location: cbuy_crops.php');
    exit();
}

$cart = agri_cart_details($conn, $customerId);
$cartError = isset($_SESSION['cart_error']) ? $_SESSION['cart_error'] : '';
$cartMessage = isset($_SESSION['cart_message']) ? $_SESSION['cart_message'] : '';
unset($_SESSION['cart_error'], $_SESSION['cart_message']);

$cropOptions = array();
$cropResult = $conn->query(
    'SELECT Trade_crop, SUM(Crop_quantity) AS available
     FROM farmer_crops_trade
     WHERE Crop_quantity > 0
     GROUP BY Trade_crop
     ORDER BY Trade_crop ASC'
);
if ($cropResult) {
    while ($row = $cropResult->fetch_assoc()) {
        $cropOptions[] = array(
            'name' => (string)$row['Trade_crop'],
            'available' => (int)floor((float)$row['available']),
        );
    }
}
?>

<!DOCTYPE html>
<html>
<?php include ('cheader.php');  ?>

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
        <span class="badge badge-danger badge-pill mb-3">Shopping</span>
      </div>
    </div>

    <?php if ($cartError !== ''): ?>
      <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($cartError, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if ($cartMessage !== ''): ?>
      <div class="alert alert-success" role="status"><?php echo htmlspecialchars($cartMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <div class="row">
      <div class="col-lg-5 mb-3">
        <div class="card text-white bg-gradient-danger">
          <div class="card-header">
            <span class="text-white h3">Buy Crops</span>
          </div>
          <div class="card-body">
            <form method="POST" action="cbuy_redirect.php" id="add-crop-form">
              <div class="form-group">
                <label for="crops">Crop</label>
                <select id="crops" name="crops" class="form-control text-dark" required>
                  <option value="">Select Crop</option>
                  <?php foreach ($cropOptions as $option): ?>
                    <option value="<?php echo htmlspecialchars($option['name'], ENT_QUOTES, 'UTF-8'); ?>" data-available="<?php echo $option['available']; ?>">
                      <?php echo htmlspecialchars(ucfirst($option['name']), ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <small id="qty-available" class="text-white"></small>
              </div>

              <div class="form-group">
                <label for="quantity">Quantity (in KG)</label>
                <input id="quantity" type="number" min="1" step="1" name="quantity" class="form-control text-dark" placeholder="Enter quantity" required>
              </div>

              <div class="form-group">
                <label for="price">Price (in Rs)</label>
                <input id="price" type="text" value="" name="price" class="form-control text-dark" placeholder="Price is calculated automatically" readonly>
              </div>

              <button class="btn btn-success btn-block" name="add_to_cart" type="submit" disabled>Add To Cart</button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-7 mb-3">
        <div class="card text-white bg-gradient-danger">
          <div class="card-header">
            <span class="text-white h3">Order Details</span>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-bordered bg-white text-dark mb-0" id="myTable">
                <thead class="text-center">
                  <tr>
                    <th>Item Name</th>
                    <th>Quantity (in KG)</th>
                    <th>Price (in Rs.)</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($cart['items'])): ?>
                    <tr>
                      <td colspan="4" class="text-center">Your cart is empty. Add a crop to get started.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($cart['items'] as $item): ?>
                      <tr>
                        <td><?php echo htmlspecialchars(ucfirst($item['cropname']), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="text-center"><?php echo (int)$item['quantity']; ?></td>
                        <td class="text-right">Rs. <?php echo number_format($item['price'], 2); ?></td>
                        <td class="text-center">
                          <form method="POST" action="cbuy_crops.php" class="mb-0">
                            <input type="hidden" name="cart_id" value="<?php echo (int)$item['id']; ?>">
                            <button type="submit" name="remove_item" class="btn btn-warning btn-sm">Remove</button>
                          </form>
                        </td>
                      </tr>
                      <?php if ($item['shortage']): ?>
                        <tr>
                          <td colspan="4" class="text-danger font-weight-bold">
                            Only <?php echo (int)$item['available']; ?> kg is available now. Remove this item and add a smaller quantity.
                          </td>
                        </tr>
                      <?php endif; ?>
                    <?php endforeach; ?>
                    <tr class="bg-white">
                      <td colspan="2" class="text-right font-weight-bold">Total</td>
                      <td class="text-right font-weight-bold">Rs. <?php echo number_format($cart['total'], 2); ?></td>
                      <td class="text-center">
                        <?php if ($cart['has_shortage']): ?>
                          <button class="btn btn-secondary btn-sm" type="button" disabled>Place Order</button>
                        <?php else: ?>
                          <form method="POST" action="ccheckout.php" class="mb-0">
                            <button class="btn btn-info btn-sm" name="place_order" type="submit">Place Order</button>
                          </form>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            <p class="text-white small mt-3 mb-0">
              Payments are processed securely. If Stripe is not configured, the portal runs in demo mode and records the order without charging a card.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require("footer.php");?>

<script>
(function () {
  var cropSelect = document.getElementById('crops');
  var quantityInput = document.getElementById('quantity');
  var priceInput = document.getElementById('price');
  var availableLabel = document.getElementById('qty-available');
  var addButton = document.querySelector('[name="add_to_cart"]');
  var available = 0;

  function setAddEnabled(enabled) {
    if (!addButton) {
      return;
    }
    if (enabled) {
      addButton.removeAttribute('disabled');
    } else {
      addButton.setAttribute('disabled', 'disabled');
    }
  }

  function quotePrice() {
    var crop = cropSelect ? cropSelect.value : '';
    var quantity = quantityInput ? parseInt(quantityInput.value, 10) : 0;

    if (!crop || !quantity || quantity < 1) {
      priceInput.value = '';
      setAddEnabled(false);
      return;
    }

    jQuery.ajax({
      url: 'ccheck_price.php',
      type: 'post',
      dataType: 'json',
      data: { crops: crop, quantity: quantity },
      success: function (result) {
        var total = parseFloat(result.total || 0);
        priceInput.value = total > 0 ? 'Rs. ' + total.toFixed(2) : '';
        setAddEnabled(total > 0 && result.ok === true);
      },
      error: function () {
        priceInput.value = '';
        setAddEnabled(false);
      }
    });
  }

  if (cropSelect) {
    cropSelect.addEventListener('change', function () {
      var option = cropSelect.options[cropSelect.selectedIndex];
      available = option ? parseInt(option.getAttribute('data-available') || '0', 10) : 0;

      if (available > 0) {
        quantityInput.setAttribute('max', available);
        quantityInput.placeholder = 'Max: ' + available + ' KG';
        availableLabel.innerText = 'Available: ' + available + ' KG';
      } else {
        quantityInput.removeAttribute('max');
        quantityInput.placeholder = 'Enter quantity';
        availableLabel.innerText = '';
      }

      quantityInput.value = '';
      quotePrice();
    });
  }

  if (quantityInput) {
    quantityInput.addEventListener('input', quotePrice);
    quantityInput.addEventListener('change', quotePrice);
  }
})();
</script>

</body>
</html>
