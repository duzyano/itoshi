// kiosk.js - Unified cart system with localStorage and upselling + Category Filtering
const CART_KEY = 'hh_cart_v1';

let upsellRules = {};
let allProducts = {}; // Store all products for category lookup

function loadCart() {
  try {
    const stored = localStorage.getItem(CART_KEY);
    return stored ? JSON.parse(stored) : [];
  } catch (e) {
    console.error('Error loading cart:', e);
    return [];
  }
}

function saveCart(cart) {
  try {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
  } catch (e) {
    console.error('Error saving cart:', e);
  }
}

let cart = loadCart();
let currentUpsellQueue = [];

function getCurrentLanguage() {
  return localStorage.getItem('hh_language') || 'nl';
}

function setLanguage(lang) {
  if(['en','nl','fr'].includes(lang)) {
    localStorage.setItem('hh_language', lang);
  }
  renderCart();
  renderMenuText();
}

function t(key, lang=null){
  if(!lang) lang=getCurrentLanguage();
  return translations[lang]?.[key] || translations['en']?.[key] || key;
}

// Dynamische upsell teksten gebaseerd op categorie
function getUpsellTexts(upsellProductId) {
  const lang = getCurrentLanguage();
  const product = allProducts[upsellProductId];
  
  if (!product) {
    return {
      title: 'Wil je er dit bij?',
      subtitle: 'Probeer dan de'
    };
  }

  // Detecteer categorie uit product data
  const category = product.category ? product.category.toLowerCase() : '';
  
  const categoryTexts = {
    'nl': {
      'drinks': {
        title: 'Wil je er een drankje bij?',
        subtitle: 'Probeer dan de'
      },
      'dips': {
        title: 'Wil je een dip erbij?',
        subtitle: 'Neem dan de'
      },
      'sides': {
        title: 'Wil je er een bijgerecht bij?',
        subtitle: 'Probeer dan de'
      },
      'default': {
        title: 'Wil je er dit bij?',
        subtitle: 'Klanten die dit bestelden, namen ook de'
      }
    },
    'en': {
      'drinks': {
        title: 'Would you like a drink with that?',
        subtitle: 'Try the'
      },
      'dips': {
        title: 'Want to add a dip?',
        subtitle: 'Try the'
      },
      'sides': {
        title: 'Want to add a side?',
        subtitle: 'Try the'
      },
      'default': {
        title: 'Would you like this too?',
        subtitle: 'Customers who ordered this also got the'
      }
    },
    'fr': {
      'drinks': {
        title: 'Voulez-vous une boisson?',
        subtitle: 'Essayez le'
      },
      'dips': {
        title: 'Voulez-vous une sauce?',
        subtitle: 'Essayez le'
      },
      'sides': {
        title: 'Voulez-vous un accompagnement?',
        subtitle: 'Essayez le'
      },
      'default': {
        title: 'Voulez-vous ceci aussi?',
        subtitle: 'Les clients qui ont commandé cela ont aussi pris le'
      }
    }
  };

  const langTexts = categoryTexts[lang] || categoryTexts['nl'];
  const texts = langTexts[category] || langTexts['default'];
  
  return texts;
}

function showUpsellModal(productId, productName, productPrice, productImage) {
  if (!upsellRules[productId] || upsellRules[productId].length === 0) {
    return;
  }

  const upsells = upsellRules[productId];
  currentUpsellQueue = [...upsells];
  
  showNextUpsell(productName);
}

function showNextUpsell(originalProductName) {
  if (currentUpsellQueue.length === 0) {
    return;
  }

  const upsell = currentUpsellQueue.shift();
  const lang = getCurrentLanguage();
  
  const texts = getUpsellTexts(upsell.id);

  const yesButton = lang === 'nl' ? 'Ja, toevoegen!' : lang === 'en' ? 'Yes, add it!' : 'Oui, ajouter!';
  const noButton = lang === 'nl' ? 'Nee, bedankt' : lang === 'en' ? 'No, thanks' : 'Non, merci';

  const backdrop = document.createElement('div');
  backdrop.className = 'upsell-backdrop';

  const modal = document.createElement('div');
  modal.className = 'upsell-modal';

  modal.innerHTML = `
    <div class="upsell-content">
      <h3>${texts.title}</h3>
      <p>${texts.subtitle}</p>
      
      <div class="upsell-product">
        <img src="${upsell.image}" alt="${upsell.name}">
        <div class="upsell-info">
          <div class="upsell-name">${upsell.name}</div>
          <div class="upsell-desc">${upsell.description || ''}</div>
          <div class="upsell-price">€${parseFloat(upsell.price).toFixed(2)}</div>
        </div>
      </div>
      
      <div class="upsell-buttons">
        <button class="upsell-accept">${yesButton} ✓</button>
        <button class="upsell-decline">${noButton}</button>
      </div>
    </div>
  `;

  document.body.appendChild(backdrop);
  document.body.appendChild(modal);

  modal.querySelector('.upsell-accept').addEventListener('click', () => {
    cart.push({
      name: upsell.name,
      price: parseFloat(upsell.price),
      image: upsell.image
    });
    saveCart(cart);
    renderCart();

    backdrop.remove();
    modal.remove();

    setTimeout(() => showNextUpsell(originalProductName), 300);
  });

  modal.querySelector('.upsell-decline').addEventListener('click', () => {
    backdrop.remove();
    modal.remove();

    setTimeout(() => showNextUpsell(originalProductName), 300);
  });

  backdrop.addEventListener('click', () => {
    backdrop.remove();
    modal.remove();
    currentUpsellQueue = [];
  });
}

// ===== CATEGORY FILTERING FUNCTION =====
function initCategoryFiltering() {
  const categoryButtons = document.querySelectorAll('.category-btn');
  const menuSections = document.querySelectorAll('.menu-section');
  
  if (categoryButtons.length === 0 || menuSections.length === 0) {
    console.warn('Category buttons or sections not found');
    return;
  }

  // Set first category as active by default
  if (categoryButtons.length > 0) {
    categoryButtons[0].classList.add('active');
    const firstCategory = categoryButtons[0].getAttribute('data-category');
    
    // Hide all sections except the first one
    menuSections.forEach(section => {
      const sectionCategory = section.getAttribute('data-category');
      if (sectionCategory === firstCategory) {
        section.classList.remove('hidden');
      } else {
        section.classList.add('hidden');
      }
    });
  }

  // Add click handlers to category buttons
  categoryButtons.forEach(button => {
    button.addEventListener('click', () => {
      const selectedCategory = button.getAttribute('data-category');
      
      console.log('Category clicked:', selectedCategory);
      
      // Update active button
      categoryButtons.forEach(btn => btn.classList.remove('active'));
      button.classList.add('active');
      
      // Show/hide sections
      menuSections.forEach(section => {
        const sectionCategory = section.getAttribute('data-category');
        if (sectionCategory === selectedCategory) {
          section.classList.remove('hidden');
          section.style.opacity = '0';
          setTimeout(() => {
            section.style.opacity = '1';
          }, 10);
        } else {
          section.classList.add('hidden');
        }
      });

      // Smooth scroll to top of menu
      const menuMain = document.querySelector('.menu-main');
      if (menuMain) {
        menuMain.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
}

document.addEventListener('DOMContentLoaded', ()=>{
  // ===== INITIALIZE CATEGORY FILTERING =====
  initCategoryFiltering();
  
  try {
    const upsellData = document.getElementById('upsell-data');
    if (upsellData) {
      upsellRules = JSON.parse(upsellData.textContent);
      console.log('Upsell rules loaded:', upsellRules);
      
      // Build product lookup map from upsell data
      Object.values(upsellRules).forEach(upsellList => {
        upsellList.forEach(upsell => {
          allProducts[upsell.id] = upsell;
        });
      });
      
      console.log('Products indexed:', allProducts);
    }
  } catch (e) {
    console.error('Error loading upsell rules:', e);
  }

  renderCart();
  renderMenuText();

  document.querySelectorAll('.add-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const id = btn.dataset.id;
      const name = btn.dataset.name;
      const price = parseFloat(btn.dataset.price);
      const image = btn.dataset.image;

      cart.push({ name, price, image });
      saveCart(cart);
      renderCart();
      
      const originalText = btn.textContent;
      const lang = getCurrentLanguage();
      const addedText = lang === 'nl' ? 'Toegevoegd!' : lang === 'en' ? 'Added!' : 'Ajouté!';
      
      btn.textContent = '✓ ' + addedText;
      btn.style.background = '#053631';
      
      setTimeout(() => {
        btn.textContent = originalText;
        btn.style.background = '';
      }, 1000);

      setTimeout(() => {
        showUpsellModal(id, name, price, image);
      }, 800);
    });
  });

  document.querySelectorAll('.lang-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>setLanguage(btn.dataset.lang));
  });
});

function renderCart(){
  const lang = getCurrentLanguage();
  const liveCart = document.getElementById('live-cart');
  if(!liveCart) return;

  const emptyText = lang === 'nl' ? 'Je winkelwagen is leeg' : lang === 'en' ? 'Your cart is empty' : 'Votre panier est vide';
  
  if(!cart.length){
    liveCart.innerHTML = `<p>${emptyText}</p>`;
    return;
  }

  const itemCounts = {};
  cart.forEach(item => {
    if (!itemCounts[item.name]) {
      itemCounts[item.name] = {
        ...item,
        quantity: 0
      };
    }
    itemCounts[item.name].quantity++;
  });

  const removeText = lang === 'nl' ? 'Verwijder' : lang === 'en' ? 'Remove' : 'Retirer';
  const subtotalText = lang === 'nl' ? 'Subtotaal' : lang === 'en' ? 'Subtotal' : 'Sous-total';
  const cartText = lang === 'nl' ? 'Bekijk winkelwagen' : lang === 'en' ? 'View cart' : 'Voir le panier';

  let html = '<ul class="cart-items">';
  Object.values(itemCounts).forEach(item => {
    const totalPrice = item.price * item.quantity;
    html += `<li>
      <img src="${item.image}" alt="${item.name}">
      <strong>${item.name}</strong> x ${item.quantity} (€${totalPrice.toFixed(2)})
      <button class="remove-btn" data-name="${item.name}">${removeText}</button>
    </li>`;
  });
  html += '</ul>';
  
  const subtotal = cart.reduce((sum, item) => sum + item.price, 0);
  html += `<div class="cart-summary-mini">
    <p><strong>${subtotalText}:</strong> €${subtotal.toFixed(2)}</p>
  </div>`;
  
  html += `<div class="cart-actions">
    <a href="shoppingcart.php" class="checkout-btn">${cartText}</a>
  </div>`;

  liveCart.innerHTML = html;

  document.querySelectorAll('.remove-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const nameToRemove = btn.dataset.name;
      const index = cart.findIndex(item => item.name === nameToRemove);
      if (index > -1) {
        cart.splice(index, 1);
        saveCart(cart);
        renderCart();
      }
    });
  });
}

function renderMenuText(){
  const lang = getCurrentLanguage();
  const addText = lang === 'nl' ? 'Toevoegen' : lang === 'en' ? 'Add' : 'Ajouter';
  
  document.querySelectorAll('.section-title').forEach(section => {
    const key = section.dataset.category;
    section.textContent = t(key, lang);
  });
  
  document.querySelectorAll('.add-btn').forEach(btn => {
    btn.textContent = addText;
  });
}