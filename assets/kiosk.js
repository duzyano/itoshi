document.addEventListener('DOMContentLoaded', function () {
  const cartKey = 'hh_cart_v1';

  // Upselling suggestions: map product names to suggested pairings
  const upsellingSuggestions = {
    'Oven-Baked Sweet Potato Wedges': { name: 'Avocado Lime Crema', price: 1.00, image: 'assets/images/image.png' },
    'French Fries': { name: 'Avocado Lime Crema', price: 1.00, image: 'assets/images/image.png' },
    'Spring Rolls': { name: 'Peanut Sauce', price: 0.75, image: 'assets/images/image.png' }
  };

  function loadCart() {
    try {
      return JSON.parse(localStorage.getItem(cartKey)) || [];
    } catch (e) {
      return [];
    }
  }

  function saveCart(items) {
    localStorage.setItem(cartKey, JSON.stringify(items));
    updateCartUI();
  }

  function updateCartUI() {
    const items = loadCart();
    const countEl = document.getElementById('cart-count');
    const dropdown = document.getElementById('cart-dropdown');
    const itemsEl = document.getElementById('cart-items');
    if (!countEl || !itemsEl) return;
    countEl.textContent = items.length;
    if (items.length === 0) {
      itemsEl.innerHTML = 'Je winkelwagen is leeg.';
    } else {
      itemsEl.innerHTML = '';
      items.forEach((it, idx) => {
        const div = document.createElement('div');
        div.className = 'cart-item';
        div.innerHTML = `
          <img src="${escapeHtml(it.image)}" alt="${escapeHtml(it.name)}">
          <div class="ci-meta">
            <div class="ci-name">${escapeHtml(it.name)}</div>
            <div class="ci-price">€${Number(it.price).toFixed(2)}</div>
          </div>
          <button class="ci-remove" data-idx="${idx}">✕</button>
        `;
        itemsEl.appendChild(div);
      });
    }
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (m) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[m]; });
  }

  function showUpsellingSuggestion(suggestion) {
    // Create backdrop
    const backdrop = document.createElement('div');
    backdrop.className = 'upsell-backdrop';
    document.body.appendChild(backdrop);

    // Create modal
    const modal = document.createElement('div');
    modal.className = 'upsell-modal';
    modal.innerHTML = `
      <div class="upsell-content">
        <h3>Perfect pairing!</h3>
        <p>We suggest:</p>
        <div class="upsell-product">
          <img src="${escapeHtml(suggestion.image)}" alt="${escapeHtml(suggestion.name)}">
          <div class="upsell-info">
            <div class="upsell-name">${escapeHtml(suggestion.name)}</div>
            <div class="upsell-price">€${Number(suggestion.price).toFixed(2)}</div>
          </div>
        </div>
        <div class="upsell-buttons">
          <button class="upsell-accept">Yes, add it!</button>
          <button class="upsell-decline">No thanks</button>
        </div>
      </div>
    `;
    document.body.appendChild(modal);

    // Accept button
    modal.querySelector('.upsell-accept').addEventListener('click', function () {
      const items = loadCart();
      items.push({ name: suggestion.name, price: suggestion.price, image: suggestion.image });
      saveCart(items);
      closeUpsellModal(backdrop, modal);
    });

    // Decline button
    modal.querySelector('.upsell-decline').addEventListener('click', function () {
      closeUpsellModal(backdrop, modal);
    });

    // Close on backdrop click
    backdrop.addEventListener('click', function () {
      closeUpsellModal(backdrop, modal);
    });
  }

  function closeUpsellModal(backdrop, modal) {
    modal.remove();
    backdrop.remove();
  }

  // Remove item handler
  document.body.addEventListener('click', function (e) {
    if (e.target.classList.contains('ci-remove')) {
      e.stopPropagation(); // Prevent dropdown close
      const idx = Number(e.target.dataset.idx);
      const items = loadCart();
      items.splice(idx, 1);
      saveCart(items);
      return;
    }

    // Toggle dropdown
    if (e.target.closest('.cart-btn')) {
      const dd = document.getElementById('cart-dropdown');
      if (!dd) return;
      dd.classList.toggle('open');
    } else {
      // click outside closes dropdown
      const dd = document.getElementById('cart-dropdown');
      if (dd && !e.target.closest('.header-cart')) {
        dd.classList.remove('open');
      }
    }
  });

  // Add button handler with animation
  document.querySelectorAll('.add-btn').forEach(btn => {
    btn.addEventListener('click', function (ev) {
      const name = btn.dataset.name || 'Product';
      const price = btn.dataset.price || 0;
      const image = btn.dataset.image || 'assets/images/image.png';

      // find product image to clone
      const card = btn.closest('.product-card');
      const img = card ? card.querySelector('img') : null;
      let imgSrc = image;
      if (img && img.src) imgSrc = img.src;

      // create flying image
      const flyer = document.createElement('img');
      flyer.src = imgSrc;
      flyer.className = 'flying-img';
      document.body.appendChild(flyer);

      // position flyer at image/button
      const startRect = (img && img.getBoundingClientRect()) || btn.getBoundingClientRect();
      flyer.style.left = startRect.left + 'px';
      flyer.style.top = startRect.top + 'px';
      flyer.style.width = startRect.width + 'px';
      flyer.style.height = startRect.height + 'px';

      // target cart position
      const cartBtn = document.querySelector('.cart-btn');
      const cartRect = cartBtn.getBoundingClientRect();
      const targetX = cartRect.left + cartRect.width / 2 - startRect.width / 2;
      const targetY = cartRect.top + cartRect.height / 2 - startRect.height / 2;

      // calculate distance for consistent animation speed
      const deltaX = targetX - startRect.left;
      const deltaY = targetY - startRect.top;
      const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
      const speed = 800; // pixels per second
      const duration = Math.max(400, distance / speed * 1000); // minimum 400ms

      // force reflow then animate
      requestAnimationFrame(() => {
        flyer.style.transition = `all ${duration}ms cubic-bezier(0.4, 0, 0.2, 1)`;
        flyer.style.transform = `translate(${targetX - startRect.left}px, ${targetY - startRect.top}px) scale(0.2)`;
        flyer.style.opacity = '0.6';
      });

      // on transition end, remove flyer and add to cart
      flyer.addEventListener('transitionend', function () {
        flyer.remove();
        const items = loadCart();
        items.push({ name: name, price: price, image: image });
        saveCart(items);

        // small bounce effect on cart
        cartBtn.animate([
          { transform: 'scale(1)' },
          { transform: 'scale(1.15)' },
          { transform: 'scale(1)' }
        ], { duration: 300 });

        // Check for upselling suggestion
        if (upsellingSuggestions[name]) {
          showUpsellingSuggestion(upsellingSuggestions[name]);
        }
      }, { once: true });
    });
  });

  // init
  updateCartUI();
});
