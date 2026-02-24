
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
  