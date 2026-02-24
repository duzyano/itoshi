<?php
require_once 'connection.php';

// Get all products for upselling suggestions on the cart page
$all_products = [];
try {
  $stmt = $conn->prepare(
    "SELECT p.*, c.name AS category_name, i.filename AS image_filename
     FROM products p
     JOIN categories c ON p.category_id = c.category_id
     LEFT JOIN images i ON p.image_id = i.image_id
     ORDER BY c.name, p.name"
  );
  $stmt->execute();
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($results as $row) {
    $imageFilename = $row['image_filename'] ?? '';
    if (!empty($imageFilename)) {
      $imagePath = 'assets/images/' . $imageFilename;
    } else {
      $imagePath = 'assets/images/image.png';
    }

    $all_products[] = [
      'name' => $row['name'],
      'price' => $row['price'],
      'image' => $imagePath,
      'category' => $row['category_name'] ?? 'uncategorized'
    ];
  }
} catch (PDOException $e) {
  die("Fout bij ophalen producten: " . $e->getMessage());
}

// Get dips for upselling (common pairings)
$dips = array_filter($all_products, function($p) {
  return strtolower($p['category']) === 'dips';
});
?>

<!doctype html>
<html lang="nl">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Winkelwagen - Happy Herbivore</title>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/menu.css">
  <link rel="stylesheet" href="assets/cart.css">
</head>

<body class="menu-page">
  <?php include 'includes/header.php'; ?>

  <main class="menu-main">
    <div class="cart-container">
      <div class="cart-items-section">
        <h2>Jouw winkelwagen</h2>
        <div id="cart-list"></div>
      </div>

      <div class="cart-summary">
        <div class="summary-row">
          <span>Subtotaal:</span>
          <span id="subtotal">€0.00</span>
        </div>
        <div class="summary-row">
          <span>Bezorgkosten:</span>
          <span id="delivery">€0.00</span>
        </div>
        <div class="summary-row total">
          <span>Totaal:</span>
          <span id="total">€0.00</span>
        </div>
        <a href="menu.php" class="continue-shopping" style="display:block; text-align:center; margin-bottom:12px;">Verder bestellen</a>
        <button class="checkout-btn">Afrekenen</button>
      </div>
    </div>

    <!-- Upselling Section -->
    <div class="cart-container" id="upsell-container" style="display: none;">
      <div style="grid-column: 1 / -1;">
        <div class="upsell-section">
          <h3>✨ Suggesties om je bestelling compleet te maken</h3>
          <div class="upsell-items" id="upsell-items"></div>
        </div>
      </div>
    </div>
  </main>

  <?php include 'includes/footer.php'; ?>
    <script src="assets/cart.js"></script>
</body>

</html>
