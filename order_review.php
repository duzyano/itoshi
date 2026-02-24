<?php
// order_review.php
// Receives cart JSON via POST and displays a review screen before confirming
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
<html lang="nl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Besteloverzicht - Happy Herbivore</title>
    <link rel="stylesheet" href="assets/menu.css">
    <style>
        .review-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }

        .review-item {
            display: flex;
            gap: 15px;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .review-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px
        }

        .review-actions {
            display: flex;
            gap: 12px;
            margin-top: 20px
        }

        .btn {
            padding: 12px 18px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            border: none
        }

        .btn-back {
            background: #ddd;
            color: #053631
        }

        .btn-confirm {
            background: #8cd003;
            color: #fff
        }
    </style>
</head>

<body class="menu-page">
    <?php include 'includes/header.php'; ?>
    <main class="menu-main">
        <div class="review-container">
            <h2>Controleer je bestelling</h2>
            <?php if (count($cart) === 0): ?>
                <p>Je winkelwagen is leeg. <a href="menu.php">Terug naar het menu</a></p>
            <?php else: ?>
                <?php foreach ($cart as $it): ?>
                    <div class="review-item">
                        <img src="<?php echo htmlspecialchars($it['image'] ?? 'assets/images/image.png'); ?>"
                            alt="<?php echo htmlspecialchars($it['name'] ?? 'Product'); ?>">
                        <div>
                            <div style="font-weight:700"><?php echo htmlspecialchars($it['name'] ?? 'Product'); ?></div>
                            <div style="color:#666">€<?php echo number_format(floatval($it['price'] ?? 0), 2); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div style="margin-top:18px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <strong>Subtotaal</strong><span>€<?php echo number_format($subtotal, 2); ?></span></div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <strong>Bezorgkosten</strong><span>€<?php echo number_format($delivery, 2); ?></span></div>
                    <div style="display:flex; justify-content:space-between; font-weight:700; font-size:1.1rem;">
                        <strong>Totaal</strong><span>€<?php echo number_format($total, 2); ?></span></div>
                </div>

                <div class="review-actions">
                    <button class="btn btn-back" onclick="window.history.back()">Terug</button>
                    <form method="post" action="order_confirmation.php" style="margin:0">
                        <input type="hidden" name="cart_json"
                            value="<?php echo htmlspecialchars($cartJson, ENT_QUOTES); ?>">
                        <input type="hidden" name="subtotal"
                            value="<?php echo htmlspecialchars(number_format($subtotal, 2, '.', ''), ENT_QUOTES); ?>">
                        <input type="hidden" name="delivery"
                            value="<?php echo htmlspecialchars(number_format($delivery, 2, '.', ''), ENT_QUOTES); ?>">
                        <input type="hidden" name="total"
                            value="<?php echo htmlspecialchars(number_format($total, 2, '.', ''), ENT_QUOTES); ?>">
                        <button type="submit" class="btn btn-confirm">Bevestig bestelling</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>

</html>