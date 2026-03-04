<?php
// order_confirmation.php
// Receives confirmed order POST (cart JSON + totals). Generates an order number and displays confirmation.

$cartJson = $_POST['cart_json'] ?? '[]';
$subtotal = isset($_POST['subtotal']) ? floatval($_POST['subtotal']) : 0.0;
$delivery = isset($_POST['delivery']) ? floatval($_POST['delivery']) : 0.0;
$total = isset($_POST['total']) ? floatval($_POST['total']) : ($subtotal + $delivery);

$cart = json_decode($cartJson, true);
if (!is_array($cart))
    $cart = [];

include 'includes/language.php';

$dbSaved = false;
$dbError = '';
$orderNumber = null;
$orderId = null;

try {
    require_once __DIR__ . '/connection.php';
    
    if (!isset($conn) || !($conn instanceof PDO)) {
        throw new Exception('Database connection not available');
    }
    
    // Generate incremental order number
    $counterDir = __DIR__ . '/data';
    $counterFile = $counterDir . '/order_counter.txt';
    
    if (!is_dir($counterDir)) {
        @mkdir($counterDir, 0755, true);
    }
    
    $last = 0;
    $fp = @fopen($counterFile, 'c+');
    if ($fp) {
        flock($fp, LOCK_EX);
        $contents = stream_get_contents($fp);
        $last = intval(trim($contents));
        $last = $last + 1;
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, (string) $last);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
    } else {
        $last = rand(0, 9999);
    }
    $orderNumber = '#' . str_pad((string) $last, 4, '0', STR_PAD_LEFT);
    $pickup = (string) $last;
    
    // Save order to database
    $conn->beginTransaction();
    
    $insertOrder = $conn->prepare('INSERT INTO orders (order_status_id, pickup_number, price_total, datetime) VALUES (:status, :pickup, :total, NOW())');
    $status = 2; // 'Placed and paid' as default
    $insertOrder->execute([':status' => $status, ':pickup' => $pickup, ':total' => $total]);
    $insertOrderId = $conn->lastInsertId();
    $orderId = $insertOrderId;
    
    // Insert order products
    $insertProduct = $conn->prepare('INSERT INTO order_product (order_id, product_id, price) VALUES (:order_id, :product_id, :price)');
    $skippedItems = [];
    
    foreach ($cart as $item) {
        $productId = 0;
        if (isset($item['id']))
            $productId = intval($item['id']);
        elseif (isset($item['product_id']))
            $productId = intval($item['product_id']);
        
        $price = isset($item['price']) ? floatval($item['price']) : 0.0;
        
        if ($productId > 0) {
            $insertProduct->execute([':order_id' => $insertOrderId, ':product_id' => $productId, ':price' => $price]);
        } else {
            // skip items without a valid product id to avoid FK constraint errors
            $skippedItems[] = $item;
        }
    }
    
    if (!empty($skippedItems)) {
        try {
            $log = date('c') . " - Skipped items for order {$insertOrderId}: " . json_encode($skippedItems) . PHP_EOL;
            @file_put_contents($counterDir . '/order_error.log', $log, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) { }
    }
    
    $conn->commit();
    $dbSaved = true;
    
} catch (Exception $e) {
    if (isset($conn) && $conn instanceof PDO && $conn->inTransaction()) {
        try { $conn->rollBack(); } catch (Exception $_) { }
    }
    $dbError = $e->getMessage();
}

if (!$dbSaved && empty($dbError)) {
    $dbError = 'Database connection not available or inserts skipped.';
}

?>
<!doctype html>
<html lang="<?php echo $current_language ?? 'nl'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo t('order_confirmation'); ?> - Happy Herbivore</title>
    <link rel="stylesheet" href="assets/menu.css">
    <style>
        .confirm-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            text-align: center
        }

        .order-number {
            font-size: 1.4rem;
            font-weight: 700;
            color: #053631;
            margin: 14px 0
        }

        .items-list {
            margin-top: 20px;
            text-align: left
        }
    </style>
</head>

<body class="menu-page">
    <?php include 'includes/header.php'; ?>
    <main class="menu-main">
        <div class="confirm-container">
            <h2><?php echo t('order_confirmation'); ?></h2>
            <div class="order-number"><?php echo t('order_confirmation'); ?>: <?php echo htmlspecialchars($orderNumber); ?></div>
            <p><?php echo t('order_confirmation'); ?>. <?php echo t('order_review'); ?></p>

            <div class="items-list">
                <?php foreach ($cart as $it): ?>
                    <div style="display:flex; gap:12px; align-items:center; padding:8px 0; border-bottom:1px solid #eee;">
                        <img src="<?php echo htmlspecialchars($it['image'] ?? 'assets/images/image.png'); ?>"
                            style="width:64px;height:64px;object-fit:cover;border-radius:6px">
                        <div>
                            <div style="font-weight:700"><?php echo htmlspecialchars($it['name'] ?? 'Product'); ?></div>
                            <div style="color:#666">€<?php echo number_format(floatval($it['price'] ?? 0), 2); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top:18px; text-align:right">
                <div><strong><?php echo t('subtotal'); ?></strong> €<?php echo number_format($subtotal, 2); ?></div>
                <div><strong><?php echo t('delivery_cost'); ?></strong> €<?php echo number_format($delivery, 2); ?></div>
                <div style="font-weight:700; font-size:1.1rem"><strong><?php echo t('total'); ?></strong>
                    €<?php echo number_format($total, 2); ?></div>
            </div>

            <?php if (defined('PHP_SAPI') && PHP_SAPI !== 'cli' && !$dbSaved): ?>
                <div style="margin-top:12px; color:#c00; font-weight:700"><?php echo htmlspecialchars($dbError ? $dbError : 'Order not saved to database.'); ?></div>
                <div style="color:#666; font-size:0.9rem; margin-top:6px">Logbestand: data/order_error.log</div>
            <?php endif; ?>

            <div style="margin-top:20px">
                <a href="home.php"
                    style="text-decoration:none; display:inline-block; padding:12px 18px; border-radius:8px; background:#053631; color:#fff; font-weight:700"><?php echo t('back_to_menu'); ?></a>
            </div>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>

    <script>
        // Clear client-side cart now that order is confirmed
        try {
            localStorage.removeItem('hh_cart_v1');
            // also update header cart count if header is loaded
            const cnt = document.getElementById('cart-count'); if (cnt) cnt.textContent = '0';
        } catch (e) { }
    </script>
</body>

</html>