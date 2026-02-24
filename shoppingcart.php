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
        <form id="checkout-form" method="post" action="order_review.php">
          <input type="hidden" name="cart_json" id="cart-json" value="">
          <input type="hidden" name="subtotal" id="form-subtotal" value="">
          <input type="hidden" name="delivery" id="form-delivery" value="">
          <input type="hidden" name="total" id="form-total" value="">
          <a href="menu.php" class="continue-shopping" style="display:block; text-align:center; margin-bottom:12px;">Verder bestellen</a>
          <button type="button" id="checkout-btn" class="checkout-btn">Afrekenen</button>
        </form>
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

  <script>
    const cartKey = 'hh_cart_v1';
    const upsellingSuggestions = {
      'Oven-Baked Sweet Potato Wedges': ['Avocado Lime Crema','Peanut Sauce'],
      'French Fries': ['Avocado Lime Crema','Peanut Sauce'],
      'Spring Rolls': ['Peanut Sauce','Sweet Chili Sauce']
    };

    const allProducts = <?php echo json_encode($all_products); ?>;

    function loadCart() {
      try { return JSON.parse(localStorage.getItem(cartKey)) || []; } catch (e) { return []; }
    }

    function saveCart(items) {
      localStorage.setItem(cartKey, JSON.stringify(items));
      renderCart();
    }

    function escapeHtml(s) {
      return String(s).replace(/[&<>"']/g, function (m) { return { '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;" }[m]; });
    }

    function renderCart() {
      const items = loadCart();
      const cartList = document.getElementById('cart-list');
      if (!cartList) return;
      if (items.length === 0) {
        cartList.innerHTML = `\n          <div class="empty-cart">\n            <p>Je winkelwagen is leeg</p>\n            <a href="menu.php" class="continue-shopping">Terug naar menu</a>\n          </div>\n        `;
        document.getElementById('upsell-container').style.display = 'none';
        updateSummary(items);
        return;
      }

      cartList.innerHTML = '';
      items.forEach((item, idx) => {
        const div = document.createElement('div');
        div.className = 'cart-item-row';
        div.innerHTML = `\n          <img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.name)}">\n          <div class="cart-item-details">\n            <h4>${escapeHtml(item.name)}</h4>\n            <span class="price">€${Number(item.price).toFixed(2)}</span>\n          </div>\n          <button class="remove-btn" data-idx="${idx}">✕</button>\n        `;
        cartList.appendChild(div);
        div.querySelector('.remove-btn').addEventListener('click', function () {
          const items = loadCart();
          items.splice(idx,1);
          saveCart(items);
        });
      });

      updateSummary(items);
      renderUpselling(items);
    }

    function updateSummary(items) {
      const subtotal = items.reduce((sum, it) => sum + Number(it.price), 0);
      const delivery = subtotal > 0 ? 3.99 : 0;
      const total = subtotal + delivery;
      document.getElementById('subtotal').textContent = '€' + subtotal.toFixed(2);
      document.getElementById('delivery').textContent = '€' + delivery.toFixed(2);
      document.getElementById('total').textContent = '€' + total.toFixed(2);
    }

    function renderUpselling(cartItems) {
      const cartItemNames = cartItems.map(i => i.name);
      const suggestedNames = new Set();
      cartItemNames.forEach(name => { if (upsellingSuggestions[name]) upsellingSuggestions[name].forEach(s => suggestedNames.add(s)); });
      const suggestedProducts = allProducts.filter(p => suggestedNames.has(p.name) && !cartItemNames.includes(p.name));
      if (suggestedProducts.length === 0) { document.getElementById('upsell-container').style.display = 'none'; return; }
      document.getElementById('upsell-container').style.display = 'block';
      const upsellItems = document.getElementById('upsell-items'); upsellItems.innerHTML = '';
      suggestedProducts.forEach(product => {
        const div = document.createElement('div'); div.className = 'upsell-item';
        div.innerHTML = `\n          <img src="${escapeHtml(product.image)}" alt="${escapeHtml(product.name)}">\n          <div class="upsell-item-name">${escapeHtml(product.name)}</div>\n          <div class="upsell-item-price">€${Number(product.price).toFixed(2)}</div>\n          <button class="upsell-add-btn">Add</button>\n        `;
        upsellItems.appendChild(div);
        div.querySelector('.upsell-add-btn').addEventListener('click', function () { const items = loadCart(); items.push({ name: product.name, price: product.price, image: product.image }); saveCart(items); });
      });
    }

    // Initial render
    renderCart();

    // Checkout: prepare form and submit
    (function (){
      const btn = document.getElementById('checkout-btn');
      if (!btn) return;
      btn.addEventListener('click', function () {
        const items = loadCart();
        if (!items || items.length === 0) { alert('Je winkelwagen is leeg. Voeg eerst items toe.'); return; }
        const subtotal = items.reduce((s, it) => s + Number(it.price), 0);
        const delivery = subtotal > 0 ? 3.99 : 0;
        const total = subtotal + delivery;
        document.getElementById('cart-json').value = JSON.stringify(items);
        document.getElementById('form-subtotal').value = subtotal.toFixed(2);
        document.getElementById('form-delivery').value = delivery.toFixed(2);
        document.getElementById('form-total').value = total.toFixed(2);
        document.getElementById('checkout-form').submit();
      });
    })();
  </script>
</body>

</html>
