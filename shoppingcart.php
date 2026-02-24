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
  <style>
    .cart-container {
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 20px;
      display: grid;
      grid-template-columns: 1fr 350px;
      gap: 30px;
    }

    .cart-items-section {
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .cart-items-section h2 {
      font-size: 1.8rem;
      color: #053631;
      margin-bottom: 20px;
      font-weight: 700;
    }

    .cart-item-row {
      display: flex;
      gap: 15px;
      padding: 15px 0;
      border-bottom: 1px solid #eee;
      align-items: center;
    }

    .cart-item-row img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
      flex-shrink: 0;
    }

    .cart-item-details {
      flex: 1;
    }

    .cart-item-details h4 {
      font-size: 1.1rem;
      color: #053631;
      font-weight: 700;
      margin-bottom: 5px;
    }

    .cart-item-row .price {
      font-size: 1rem;
      color: #ff7520;
      font-weight: 700;
    }

    .remove-btn {
      background: #ff7520;
      color: #fff;
      border: none;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      cursor: pointer;
      font-weight: 700;
      font-size: 18px;
      transition: background 0.2s;
    }

    .remove-btn:hover {
      background: #ff5500;
    }

    .empty-cart {
      text-align: center;
      padding: 40px 20px;
      color: #666;
    }

    .empty-cart p {
      font-size: 1.1rem;
      margin-bottom: 20px;
    }

    .continue-shopping {
      display: inline-block;
      background: #8cd003;
      color: #fff;
      padding: 12px 24px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 700;
      transition: background 0.2s;
    }

    .continue-shopping:hover {
      background: #7ab200;
    }

    .cart-summary {
      background: #deff78;
      padding: 20px;
      border-radius: 10px;
      position: sticky;
      top: 20px;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      font-size: 0.95rem;
    }

    .summary-row.total {
      border-top: 2px solid #8cd003;
      padding-top: 10px;
      font-size: 1.2rem;
      font-weight: 700;
      color: #053631;
    }

    .checkout-btn {
      width: 100%;
      background: #8cd003;
      color: #fff;
      border: none;
      padding: 15px;
      border-radius: 8px;
      font-weight: 700;
      font-size: 1.1rem;
      cursor: pointer;
      margin-top: 20px;
      transition: background 0.2s;
    }

    .checkout-btn:hover {
      background: #7ab200;
    }

    .upsell-section {
      margin-top: 40px;
      padding: 20px;
      background: #f9f9f9;
      border-radius: 10px;
      border-left: 4px solid #8cd003;
    }

    .upsell-section h3 {
      font-size: 1.2rem;
      color: #053631;
      margin-bottom: 15px;
      font-weight: 700;
    }

    .upsell-items {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      gap: 15px;
    }

    .upsell-item {
      background: #fff;
      padding: 12px;
      border-radius: 8px;
      text-align: center;
      cursor: pointer;
      border: 2px solid transparent;
      transition: all 0.2s;
    }

    .upsell-item:hover {
      border-color: #8cd003;
      transform: translateY(-2px);
    }

    .upsell-item img {
      width: 100%;
      height: 100px;
      object-fit: cover;
      border-radius: 6px;
      margin-bottom: 8px;
    }

    .upsell-item-name {
      font-weight: 700;
      font-size: 0.85rem;
      color: #053631;
      margin-bottom: 5px;
    }

    .upsell-item-price {
      color: #ff7520;
      font-weight: 700;
      margin-bottom: 8px;
    }

    .upsell-add-btn {
      background: #8cd003;
      color: #fff;
      border: none;
      padding: 6px 12px;
      border-radius: 4px;
      font-weight: 700;
      font-size: 0.8rem;
      cursor: pointer;
      width: 100%;
    }

    .upsell-add-btn:hover {
      background: #7ab200;
    }

    @media (max-width: 768px) {
      .cart-container {
        grid-template-columns: 1fr;
      }

      .cart-summary {
        position: static;
      }

      .upsell-items {
        grid-template-columns: repeat(2, 1fr);
      }
    }
  </style>
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

  <script>
    const cartKey = 'hh_cart_v1';

    // Upselling suggestions based on product names
    const upsellingSuggestions = {
      'Oven-Baked Sweet Potato Wedges': ['Avocado Lime Crema', 'Peanut Sauce'],
      'French Fries': ['Avocado Lime Crema', 'Peanut Sauce'],
      'Spring Rolls': ['Peanut Sauce', 'Sweet Chili Sauce']
    };

    const allProducts = <?php echo json_encode($all_products); ?>;

    function loadCart() {
      try {
        return JSON.parse(localStorage.getItem(cartKey)) || [];
      } catch (e) {
        return [];
      }
    }

    function saveCart(items) {
      localStorage.setItem(cartKey, JSON.stringify(items));
      renderCart();
    }

    function escapeHtml(s) {
      return String(s).replace(/[&<>"']/g, function (m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
      });
    }

    function renderCart() {
      const items = loadCart();
      const cartList = document.getElementById('cart-list');

      if (items.length === 0) {
        cartList.innerHTML = `
          <div class="empty-cart">
            <p>Je winkelwagen is leeg</p>
            <a href="menu.php" class="continue-shopping">Terug naar menu</a>
          </div>
        `;
        document.getElementById('upsell-container').style.display = 'none';
        updateSummary(items);
        return;
      }

      cartList.innerHTML = '';
      items.forEach((item, idx) => {
        const div = document.createElement('div');
        div.className = 'cart-item-row';
        div.innerHTML = `
          <img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.name)}">
          <div class="cart-item-details">
            <h4>${escapeHtml(item.name)}</h4>
            <span class="price">€${Number(item.price).toFixed(2)}</span>
          </div>
          <button class="remove-btn" data-idx="${idx}">✕</button>
        `;
        cartList.appendChild(div);

        div.querySelector('.remove-btn').addEventListener('click', function () {
          items.splice(idx, 1);
          saveCart(items);
        });
      });

      updateSummary(items);
      renderUpselling(items);
    }

    function updateSummary(items) {
      const subtotal = items.reduce((sum, item) => sum + Number(item.price), 0);
      const delivery = subtotal > 0 ? 3.99 : 0;
      const total = subtotal + delivery;

      document.getElementById('subtotal').textContent = '€' + subtotal.toFixed(2);
      document.getElementById('delivery').textContent = '€' + delivery.toFixed(2);
      document.getElementById('total').textContent = '€' + total.toFixed(2);
    }

    function renderUpselling(cartItems) {
      const cartItemNames = cartItems.map(item => item.name);
      const suggestedNames = new Set();

      // Collect suggestions from cart items
      cartItemNames.forEach(name => {
        if (upsellingSuggestions[name]) {
          upsellingSuggestions[name].forEach(sugg => suggestedNames.add(sugg));
        }
      });

      // Filter out items already in cart
      const suggestedProducts = allProducts.filter(p => suggestedNames.has(p.name) && !cartItemNames.includes(p.name));

      if (suggestedProducts.length === 0) {
        document.getElementById('upsell-container').style.display = 'none';
        return;
      }

      document.getElementById('upsell-container').style.display = 'block';
      const upsellItems = document.getElementById('upsell-items');
      upsellItems.innerHTML = '';

      suggestedProducts.forEach(product => {
        const div = document.createElement('div');
        div.className = 'upsell-item';
        div.innerHTML = `
          <img src="${escapeHtml(product.image)}" alt="${escapeHtml(product.name)}">
          <div class="upsell-item-name">${escapeHtml(product.name)}</div>
          <div class="upsell-item-price">€${Number(product.price).toFixed(2)}</div>
          <button class="upsell-add-btn">Add</button>
        `;
        upsellItems.appendChild(div);

        div.querySelector('.upsell-add-btn').addEventListener('click', function () {
          const items = loadCart();
          items.push({ name: product.name, price: product.price, image: product.image });
          saveCart(items);
        });
      });
    }

    // Initial render
    renderCart();
  </script>
</body>

</html>
