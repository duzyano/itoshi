<?php
// order_review.php
// Receives cart JSON via POST and displays a review screen before confirming
include 'includes/language.php';

$cartJson = $_POST['cart_json'] ?? '[]';
$subtotal = isset($_POST['subtotal']) ? floatval($_POST['subtotal']) : 0.0;
$delivery = isset($_POST['delivery']) ? floatval($_POST['delivery']) : 0.0;
$total = isset($_POST['total']) ? floatval($_POST['total']) : ($subtotal + $delivery);

// decode safely
$cart = json_decode($cartJson, true);
if (!is_array($cart))
    $cart = [];
?>
<!doctype html>
<html lang="<?php echo $current_language; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo t('order_review'); ?> - Happy Herbivore</title>
    <link rel="stylesheet" href="assets/menu.css">
</head>

<body class="menu-page">
    <?php include 'includes/header.php'; ?>
    <main class="menu-main">
        <div class="review-container">
            <div class="review-header">
                <h2><?php echo t('order_review'); ?></h2>
                <p>Review your order before confirming</p>
            </div>

            <?php if (count($cart) === 0): ?>
                <div class="empty-cart-message">
                    <p><?php echo t('empty_cart'); ?></p>
                    <a href="menu.php"><?php echo t('back_to_menu'); ?></a>
                </div>
            <?php else: ?>
                <div class="review-items-wrapper">
                    <?php foreach ($cart as $it): ?>
                        <div class="review-item">
                            <img src="<?php echo htmlspecialchars($it['image'] ?? 'assets/images/image.png'); ?>"
                                alt="<?php echo htmlspecialchars($it['name'] ?? 'Product'); ?>">
                            <div class="review-item-info">
                                <div class="review-item-name"><?php echo htmlspecialchars($it['name'] ?? 'Product'); ?></div>
                                <div class="review-item-price">€<?php echo number_format(floatval($it['price'] ?? 0), 2); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

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

                <div class="review-actions">
                    <button class="btn btn-back" onclick="window.history.back()"><?php echo t('back'); ?></button>
                    <form method="post" action="order_confirmation.php" style="margin:0; flex: 1;">
                        <input type="hidden" name="cart_json"
                            value="<?php echo htmlspecialchars($cartJson, ENT_QUOTES); ?>">
                        <input type="hidden" name="subtotal"
                            value="<?php echo htmlspecialchars(number_format($subtotal, 2, '.', ''), ENT_QUOTES); ?>">
                        <input type="hidden" name="delivery"
                            value="<?php echo htmlspecialchars(number_format($delivery, 2, '.', ''), ENT_QUOTES); ?>">
                        <input type="hidden" name="total"
                            value="<?php echo htmlspecialchars(number_format($total, 2, '.', ''), ENT_QUOTES); ?>">
                        <button type="submit" class="btn btn-confirm" style="width: 100%;"><?php echo t('confirm_order'); ?></button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <script src="assets/language.js"></script>
</body>

</html>