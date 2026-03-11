<?php
// order_confirmation.php
// Final confirmation page after order is placed
include 'includes/language.php';

$cartJson = $_POST['cart_json'] ?? '[]';
$subtotal = isset($_POST['subtotal']) ? floatval($_POST['subtotal']) : 0.0;
$delivery = isset($_POST['delivery']) ? floatval($_POST['delivery']) : 0.0;
$total = isset($_POST['total']) ? floatval($_POST['total']) : ($subtotal + $delivery);

// decode safely
$cart = json_decode($cartJson, true);
if (!is_array($cart))
    $cart = [];

// Generate a random order number between 0 and 9999
$orderNumber = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
?>
<!doctype html>
<html lang="<?php echo $current_language; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo t('order_confirmation'); ?> - Happy Herbivore</title>
    <link rel="stylesheet" href="assets/menu.css">
</head>

<body class="menu-page">
    <?php include 'includes/header.php'; ?>
    <main class="menu-main">
        <div class="confirmation-container">
            <div class="success-card">
                <div class="success-icon"></div>
                <h1 class="success-title">Order Confirmed!</h1>
                <p class="success-message">
                    Thank you for your order! Your delicious vegan meal is being prepared with care.
                </p>
                <div class="order-number-box">
                    <div class="order-number-label">Your Order Number</div>
                    <div class="order-number"><?php echo $orderNumber; ?></div>
                </div>
            </div>

            <div class="info-boxes">
                <div class="info-box">
                    <div class="info-icon">⏱️</div>
                    <div class="info-label">Estimated Time</div>
                    <div class="info-value">30-45 min</div>
                </div>
                <div class="info-box">
                    <div class="info-icon">💳</div>
                    <div class="info-label">Payment</div>
                    <div class="info-value">With Card</div>
                </div>
            </div>

            <?php if (count($cart) > 0): ?>
                <div class="order-details-card">
                    <h2 class="details-header">Order Details</h2>
                    
                    <?php foreach ($cart as $it): ?>
                        <div class="order-item">
                            <img src="<?php echo htmlspecialchars($it['image'] ?? 'assets/images/image.png'); ?>"
                                alt="<?php echo htmlspecialchars($it['name'] ?? 'Product'); ?>">
                            <div class="order-item-info">
                                <div class="order-item-name"><?php echo htmlspecialchars($it['name'] ?? 'Product'); ?></div>
                                <div class="order-item-price">€<?php echo number_format(floatval($it['price'] ?? 0), 2); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="order-summary">
                        <div class="summary-row">
                            <strong><?php echo t('subtotal'); ?></strong>
                            <span>€<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <strong><?php echo t('delivery_cost'); ?></strong>
                            <span>€<?php echo number_format($delivery, 2); ?></span>
                        </div>
                        <div class="summary-row summary-total">
                            <strong><?php echo t('total'); ?></strong>
                            <span>€<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="action-buttons">
                <button onclick="printReceipt()" class="btn btn-secondary" style="background: linear-gradient(135deg, #ff7520 0%, #ff9147 100%);">
                    <span style="font-size: 1.2rem;">🖨️</span> Print Bon
                </button>
                <a href="menu.php" class="btn btn-primary">Order Again</a>
                <a href="index.php" class="btn btn-secondary">Back to Home</a>
            </div>
        </div>
    </main>
    <script src="assets/language.js"></script>
  <script src="assets/printer.js"></script>
  <script>
    // Automatically print receipt on page load
    window.addEventListener('load', async () => {
      // Get order data from PHP
      const orderData = {
        orderNumber: '<?php echo $orderNumber; ?>',
        items: <?php echo json_encode($cart); ?>,
        subtotal: <?php echo $subtotal; ?>,
        delivery: <?php echo $delivery; ?>,
        total: <?php echo $total; ?>
      };

      // Wait a moment for page to fully load
      setTimeout(async () => {
        if (window.receiptPrinter && window.receiptPrinter.isSupported()) {
          console.log('Attempting to print receipt...');
          const result = await window.receiptPrinter.print(orderData);
          
          if (result.success) {
            console.log('✓ Receipt printed successfully');
          } else {
            console.warn('Print failed:', result.message);
            // Show user-friendly message
            if (confirm('Bon printen mislukt. Wilt u de printer selecteren?')) {
              await window.receiptPrinter.selectPrinter();
              await window.receiptPrinter.print(orderData);
            }
          }
        } else {
          console.warn('USB printing not supported in this browser');
        }
      }, 1000);
    });

    // Manual print function for the button
    async function printReceipt() {
      const orderData = {
        orderNumber: '<?php echo $orderNumber; ?>',
        items: <?php echo json_encode($cart); ?>,
        subtotal: <?php echo $subtotal; ?>,
        delivery: <?php echo $delivery; ?>,
        total: <?php echo $total; ?>
      };

      const result = await window.receiptPrinter.print(orderData);
      
      if (result.success) {
        alert('✓ Bon succesvol geprint!');
      } else {
        alert('❌ Print fout: ' + result.message);
      }
    }
  </script>
</body>

</html>