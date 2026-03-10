<?php
// shoppingcart.php - Shopping cart with API-loaded products and database upselling
include 'includes/language.php';
require_once 'connection.php';

// Haal upselling regels op uit database
$upsellRules = [];
try {
    $stmt = $conn->prepare(
        "SELECT u.product_id, u.upsell_product_id,
                p.name AS upsell_name, p.price AS upsell_price, 
                p.description AS upsell_desc, i.filename AS upsell_image
         FROM upsells u
         JOIN products p ON u.upsell_product_id = p.product_id
         LEFT JOIN images i ON p.image_id = i.image_id"
    );
    $stmt->execute();
    $upsellResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($upsellResults as $row) {
        if (!isset($upsellRules[$row['product_id']])) {
            $upsellRules[$row['product_id']] = [];
        }

        $imagePath = !empty($row['upsell_image']) 
            ? 'assets/images/' . $row['upsell_image'] 
            : 'assets/images/image.png';
            
        $upsellRules[$row['product_id']][] = [
            'id' => $row['upsell_product_id'],
            'name' => $row['upsell_name'],
            'price' => $row['upsell_price'],
            'description' => $row['upsell_desc'],
            'image' => $imagePath
        ];
    }
} catch (PDOException $e) {
    error_log("Upsell query failed: " . $e->getMessage());
    $upsellRules = [];
}
?>

<!doctype html>
<html lang="<?php echo $current_language; ?>">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Cart - Happy Herbivore</title>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/menu.css">
  <link rel="stylesheet" href="assets/cart.css">
</head>

<body class="menu-page">
  <?php include 'includes/header.php'; ?>

  <main class="menu-main">
    <div class="cart-container">
      <div class="cart-items-section">
        <h2>Cart</h2>
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
          <a href="menu.php" class="continue-shopping"
            style="display:block; text-align:center; margin-bottom:12px;"><?php echo t('continue_shopping'); ?></a>
          <button type="button" id="checkout-btn" class="checkout-btn"><?php echo t('checkout'); ?></button>
        </form>
      </div>
    </div>

    <!-- Upselling Section -->
    <div class="upsell-container" id="upsell-container" style="display: none;">
      <h3 class="upsell-title">You might also like</h3>
      <div class="upsell-grid" id="upsell-items"></div>
    </div>
  </main>

  <!-- Upsell Data from Database -->
  <script id="upsell-data" type="application/json">
    <?php echo json_encode($upsellRules); ?>
  </script>

  <script src="assets/language.js"></script>
  <script>
    const cartKey = 'hh_cart_v1';
    const currentLang = '<?php echo $current_language; ?>';

    // Load upselling rules from database
    const upsellRulesFromDB = JSON.parse(document.getElementById('upsell-data').textContent);
    
    let allProducts = [];

    // ----- Fetch all products from API -----
    fetch('api/index.php/products')
      .then(res => {
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
      })
      .then(resp => {
        console.log('API Response:', resp);
        
        // Handle verschillende mogelijke API response structures
        if (Array.isArray(resp)) {
          allProducts = resp;
        } else if (resp.success && Array.isArray(resp.data)) {
          allProducts = resp.data;
        } else if (resp.products && Array.isArray(resp.products)) {
          allProducts = resp.products;
        } else if (resp.data && Array.isArray(resp.data.products)) {
          allProducts = resp.data.products;
        } else {
          allProducts = [];
          console.warn('Products could not be loaded from API. Response structure:', resp);
        }
        
        console.log('Loaded products:', allProducts.length);
        renderCart();
      })
      .catch(err => {
        console.error('Error fetching products:', err);
        allProducts = [];
        renderCart();
      });

    // ----- Cart helpers -----
    function loadCart() {
      try { 
        return JSON.parse(localStorage.getItem(cartKey)) || []; 
      } catch (e) { 
        console.error('Error loading cart:', e);
        return []; 
      }
    }

    function saveCart(items) {
      try {
        localStorage.setItem(cartKey, JSON.stringify(items));
        renderCart();
      } catch (e) {
        console.error('Error saving cart:', e);
      }
    }

    function escapeHtml(s) {
      return String(s).replace(/[&<>"']/g, m => ({ 
        '&': '&amp;', 
        '<': '&lt;', 
        '>': '&gt;', 
        '"': '&quot;', 
        "'": "&#39;" 
      }[m]));
    }

    // ----- Render Cart -----
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
        updateSummary(items);
        renderUpselling(items);
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

        div.querySelector('.remove-btn').addEventListener('click', () => {
          const updatedItems = loadCart();
          updatedItems.splice(idx, 1);
          saveCart(updatedItems);
        });
      });

      updateSummary(items);
      renderUpselling(items);
    }

    // ----- Cart summary -----
    function updateSummary(items) {
      const subtotal = items.reduce((sum, it) => sum + Number(it.price), 0);
      const delivery = subtotal > 0 ? 3.99 : 0;
      const total = subtotal + delivery;

      document.getElementById('subtotal').textContent = '€' + subtotal.toFixed(2);
      document.getElementById('delivery').textContent = '€' + delivery.toFixed(2);
      document.getElementById('total').textContent = '€' + total.toFixed(2);
    }

    // ----- Upselling (Database-based) -----
    function renderUpselling(cartItems) {
      const cartProductIds = cartItems.map(item => parseInt(item.id)).filter(id => !isNaN(id));
      const suggestedProductIds = new Set();

      // Verzamel alle upsell product IDs op basis van cart items
      cartProductIds.forEach(productId => {
        if (upsellRulesFromDB[productId]) {
          upsellRulesFromDB[productId].forEach(upsell => {
            // Voeg alleen toe als het nog niet in cart zit
            if (!cartProductIds.includes(upsell.id)) {
              suggestedProductIds.add(upsell.id);
            }
          });
        }
      });

      // Maak een map van alle upsell producten
      const upsellProductsMap = {};
      cartProductIds.forEach(productId => {
        if (upsellRulesFromDB[productId]) {
          upsellRulesFromDB[productId].forEach(upsell => {
            if (!upsellProductsMap[upsell.id]) {
              upsellProductsMap[upsell.id] = upsell;
            }
          });
        }
      });

      // Filter de suggesties
      const suggestedProducts = Array.from(suggestedProductIds)
        .map(id => upsellProductsMap[id])
        .filter(p => p !== undefined);

      const upsellContainer = document.getElementById('upsell-container');
      const upsellItemsDiv = document.getElementById('upsell-items');

      if (!upsellContainer || !upsellItemsDiv) return;

      if (suggestedProducts.length === 0) {
        upsellContainer.style.display = 'none';
        return;
      }

      upsellContainer.style.display = 'block';
      upsellItemsDiv.innerHTML = '';

      suggestedProducts.forEach(product => {
        const div = document.createElement('div');
        div.className = 'upsell-card';
        div.innerHTML = `
          <div class="upsell-card-image">
            <img src="${escapeHtml(product.image)}" alt="${escapeHtml(product.name)}">
          </div>
          <div class="upsell-card-body">
            <h4 class="upsell-card-name">${escapeHtml(product.name)}</h4>
            <p class="upsell-card-desc">${escapeHtml(product.description)}</p>
            <div class="upsell-card-footer">
              <span class="upsell-card-price">€${Number(product.price).toFixed(2)}</span>
              <button class="upsell-add-btn" data-id="${product.id}">
                <span class="add-icon">+</span>
                Add
              </button>
            </div>
          </div>
        `;
        upsellItemsDiv.appendChild(div);

        div.querySelector('.upsell-add-btn').addEventListener('click', () => {
          const updatedItems = loadCart();
          updatedItems.push({ 
            id: product.id,
            name: product.name, 
            price: product.price, 
            image: product.image 
          });
          saveCart(updatedItems);
        });
      });
    }

    // ----- Checkout -----
    (function() {
      const btn = document.getElementById('checkout-btn');
      if (!btn) return;

      btn.addEventListener('click', () => {
        const items = loadCart();
        if (!items || items.length === 0) {
          alert(t('empty_cart', currentLang));
          return;
        }

        const subtotal = items.reduce((sum, it) => sum + Number(it.price), 0);
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