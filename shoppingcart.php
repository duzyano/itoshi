<?php
// shoppingcart.php - Products loaded from API via JavaScript
include 'includes/language.php';

// Placeholder for compatibility with cart.js
$all_products = [];
?>

<!doctype html>
<html lang="<?php echo $current_language; ?>">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo t('your_cart'); ?> - Happy Herbivore</title>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/menu.css">
  <link rel="stylesheet" href="assets/cart.css">
</head>

<body class="menu-page">
  <?php include 'includes/header.php'; ?>

  <main class="menu-main">
    <div class="cart-container">
      <div class="cart-items-section">
        <h2><?php echo t('your_cart'); ?></h2>
        <div id="cart-list"></div>
      </div>

      <div class="cart-summary">
        <div class="summary-row">
          <span><?php echo t('subtotal'); ?></span>
          <span id="subtotal">€0.00</span>
        </div>
        <div class="summary-row">
          <span><?php echo t('delivery_cost'); ?></span>
          <span id="delivery">€0.00</span>
        </div>
        <div class="summary-row total">
          <span><?php echo t('total'); ?></span>
          <span id="total">€0.00</span>
        </div>
        <form id="checkout-form" method="post" action="order_review.php">
          <input type="hidden" name="cart_json" id="cart-json" value="">
          <input type="hidden" name="subtotal" id="form-subtotal" value="">
          <input type="hidden" name="delivery" id="form-delivery" value="">
          <input type="hidden" name="total" id="form-total" value="">
          <a href="menu.php" class="continue-shopping" style="display:block; text-align:center; margin-bottom:12px;"><?php echo t('continue_shopping'); ?></a>
          <button type="button" id="checkout-btn" class="checkout-btn"><?php echo t('checkout'); ?></button>
        </form>
      </div>
    </div>

    <!-- Upselling Section -->
    <div class="cart-container" id="upsell-container" style="display: none;">
      <div style="grid-column: 1 / -1;">
        <div class="upsell-section">
          <h3><?php echo t('suggestions'); ?></h3>
          <div class="upsell-items" id="upsell-items"></div>
        </div>
      </div>
    </div>
  </main>

  <?php include 'includes/footer.php'; ?>

  <script src="assets/language.js"></script>
  <script>
    const cartKey = 'hh_cart_v1';
    const upsellingSuggestions = {
      'Oven-Baked Sweet Potato Wedges': ['Avocado Lime Crema','Peanut Sauce'],
      'French Fries': ['Avocado Lime Crema','Peanut Sauce'],
      'Spring Rolls': ['Peanut Sauce','Sweet Chili Sauce']
    };

    const allProducts = <?php echo json_encode($all_products); ?>;
    const currentLang = '<?php echo $current_language; ?>';

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
        cartList.innerHTML = `
          <div class="empty-cart">
            <p>${t('empty_cart', currentLang)}</p>
            <a href="menu.php" class="continue-shopping">${t('back_to_menu', currentLang)}</a>
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
        div.innerHTML = `
          <img src="${escapeHtml(product.image)}" alt="${escapeHtml(product.name)}">
          <div class="upsell-item-name">${escapeHtml(product.name)}</div>
          <div class="upsell-item-price">€${Number(product.price).toFixed(2)}</div>
          <button class="upsell-add-btn">${t('add_to_cart', currentLang)}</button>
        `;
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
        if (!items || items.length === 0) { alert(t('empty_cart', currentLang)); return; }
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
