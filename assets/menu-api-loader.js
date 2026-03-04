// assets/menu-api-loader.js
// Load products from the API and render them dynamically

document.addEventListener('DOMContentLoaded', async function() {
    const menuContainer = document.getElementById('menu-container');
    
    try {
        // Get current language
        let currentLang = window.currentLanguage || localStorage.getItem('hh_language') || document.documentElement.lang || 'nl';
        
        // Fetch products from API
        const response = await fetch('api/index.php/products?limit=100');
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || t('loading_products', currentLang));
        }
        
        const allProducts = result.data.products || [];
        const groupedProducts = result.data.grouped || {};
        
        // Store products globally for cart.js compatibility
        window.allProducts = allProducts;
        
        // If no products grouped, render flat list
        if (Object.keys(groupedProducts).length === 0) {
            menuContainer.innerHTML = '<p style="text-align: center; padding: 40px;">Geen producten beschikbaar.</p>';
            return;
        }
        
        // Build HTML for grouped sections
        let html = '';
        
        // Normalize function: convert names/keys to a comparable form
        function normalizeName(name) {
            return String(name || '')
                .toLowerCase()
                .replace(/&/g, 'and')
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/_+/g, '_')
                .replace(/^_|_$/g, '');
        }

        // Dietary detection helper
        function detectDietary(product) {
            // base fields from the API/database
            let isVegan = product.is_vegan || product.vegan || product.vegan_flag || product.isVegan || false;
            let isVlees = product.is_vlees || product.vlees || product.meat || product.is_meat || false;
            let isVegetarian = product.is_vegetarian || product.vegetarian || false;

            // also inspect the name/description for VG/V markers when not already set
            const text = ((product.name || '') + ' ' + (product.description || '')).toLowerCase();
            if (!isVegan && /\b(vg|vegan)\b/.test(text)) {
                isVegan = true;
            }
            // only mark vegetarian if not already vegan (VG is stronger)
            if (!isVegetarian && !isVegan && /\b(v|vegetarian)\b/.test(text)) {
                isVegetarian = true;
            }
            return {isVegan, isVegetarian, isVlees};
        }

        // Desired order (normalized keys)
        const desiredOrder = [
            'breakfast',
            'lunch_dinner',
            'wraps',
            'sides_small_plates',
            'signature_dips',
            'drinks'
        ];

        // Human-friendly display map for specific labels (will be translated)
        const displayMap = {
            'breakfast': 'breakfast',
            'lunch_dinner': 'lunch_dinner',
            'wraps': 'wraps',
            'sides_small_plates': 'sides_small_plates',
            'signature_dips': 'signature_dips',
            'drinks': 'drinks'
        };

        // Build a lookup of normalized -> original key
        const normalizedMap = {};
        for (const [categoryKey, categoryData] of Object.entries(groupedProducts)) {
            if (!categoryData) continue;
            const sourceName = categoryData.name || categoryKey;
            const norm = normalizeName(sourceName);
            normalizedMap[norm] = categoryKey;
            // also map the plain lowercased name to help matching
            normalizedMap[String(sourceName).toLowerCase()] = categoryKey;
        }

        const used = new Set();

        // Helper: try multiple variants to find the correct category key
        function findCategoryKeyFor(want) {
            const variants = new Set();
            variants.add(want);
            variants.add(want.replace(/_/g, ''));
            variants.add(want.replace(/_/g, '&'));
            variants.add(want.replace(/_/g, 'and'));
            variants.add(want.replace(/_/g, ' '));
            // normalized forms
            for (const v of Array.from(variants)) {
                variants.add(normalizeName(v));
                variants.add(String(v).toLowerCase());
            }

            for (const v of variants) {
                if (v && normalizedMap[v]) return normalizedMap[v];
            }
            return null;
        }

        // Append categories in desired order when present
        for (const want of desiredOrder) {
            const keyFound = findCategoryKeyFor(want);
            if (keyFound && groupedProducts[keyFound] && groupedProducts[keyFound].items && groupedProducts[keyFound].items.length) {
                const data = groupedProducts[keyFound];
                // Translate the display name
                const displayName = t(displayMap[want] || want, currentLang) || data.name;
                html += `<section class="menu-section">
                    <h2 class="section-title">${escapeHtml(displayName)}</h2>
                    <div class="products-grid">`;

                data.items.forEach(product => {
                    const imagePath = product.image || 'assets/images/image.png';
                    const kCalDisplay = product.kcal ? `${product.kcal} kcal` : '';

                    // Determine dietary badge: check common field names
                    const {isVegan, isVegetarian, isVlees} = detectDietary(product);
                    const isAvailable = typeof product.available !== 'undefined' ? !!product.available : true; // default to true if not provided
                    
                    let badges = [];
                    if (isAvailable) {
                        badges.push('<span class="avail-badge" title="Beschikbaar"></span>');
                    } else {
                        badges.push('<span class="avail-badge not" title="Niet beschikbaar"></span>');
                    }
                    if (isVegan) {
                        badges.push('<span class="dietary-badge vg">VG</span>');
                    } else if (isVlees || isVegetarian) {
                        badges.push('<span class="dietary-badge v">V</span>');
                    }
                    const badgeHtml = badges.join('');

                    // remove any trailing VG/V markers from the name for display
                    const rawName = product.name || '';
                    const displayName = rawName.replace(/\s*\((?:vg|v|vegan|vegetarian)\)\s*$/i, '').trim();

                    html += `
                        <div class="product-card">
                            <div class="product-header">
                                <h3>${escapeHtml(displayName)}</h3>
                                ${badgeHtml}
                            </div>
                            ${imagePath ? `
                                <div class="product-image">
                                    <img src="${escapeHtml(imagePath)}" alt="${escapeHtml(displayName)}">
                                </div>
                            ` : ''}
                            <p class="product-description">${escapeHtml(product.description || '')}</p>
                            <div class="product-footer">
                                <div class="product-meta">
                                    <span class="price">€${Number(product.price).toFixed(2)}</span>
                                    ${kCalDisplay ? `<span class="kcal">${kCalDisplay}</span>` : ''}
                                </div>
                                <button class="add-btn" 
                                    data-name="${escapeHtml(displayName)}"
                                    data-price="${product.price}"
                                    data-image="${escapeHtml(imagePath)}"
                                    data-product-id="${product.id || product.product_id}">
                                    ${t('add_to_cart', currentLang)}
                                </button>
                            </div>
                        </div>
                    `;
                });

                html += `</div>
                </section>`;
                used.add(keyFound);
            }
        }

        // Append any remaining categories not in desiredOrder
        for (const [categoryKey, categoryData] of Object.entries(groupedProducts)) {
            if (used.has(categoryKey)) continue;
            if (!categoryData.items || categoryData.items.length === 0) continue;

            // Humanize display name: replace underscores with spaces, ampersand for 'and'
            const norm = normalizeName(categoryData.name || categoryKey);
            const human = (categoryData.name || categoryKey)
                .replace(/_/g, ' ')
                .replace(/\band\b/gi, '&');

            html += `<section class="menu-section">
                <h2 class="section-title">${escapeHtml(human)}</h2>
                <div class="products-grid">`;

            categoryData.items.forEach(product => {
                const imagePath = product.image || 'assets/images/image.png';
                const kCalDisplay = product.kcal ? `${product.kcal} kcal` : '';

                // dietary + availability badges
                const {isVegan, isVegetarian, isVlees} = detectDietary(product);
                const isAvailable = typeof product.available !== 'undefined' ? !!product.available : true;
                let badges = [];
                if (isAvailable) {
                    badges.push('<span class="avail-badge" title="Beschikbaar"></span>');
                } else {
                    badges.push('<span class="avail-badge not" title="Niet beschikbaar"></span>');
                }
                if (isVegan) {
                    badges.push('<span class="dietary-badge vg">VG</span>');
                } else if (isVlees || isVegetarian) {
                    badges.push('<span class="dietary-badge v">V</span>');
                }
                const badgeHtml = badges.join('');

                // clean name
                const rawName = product.name || '';
                const displayName = rawName.replace(/\s*\((?:vg|v|vegan|vegetarian)\)\s*$/i, '').trim();

                html += `
                    <div class="product-card">
                        <div class="product-header">
                            <h3>${escapeHtml(displayName)}</h3>
                            ${badgeHtml}
                        </div>
                        ${imagePath ? `
                            <div class="product-image">
                                <img src="${escapeHtml(imagePath)}" alt="${escapeHtml(displayName)}">
                            </div>
                        ` : ''}
                        <p class="product-description">${escapeHtml(product.description || '')}</p>
                        <div class="product-footer">
                            <div class="product-meta">
                                <span class="price">€${Number(product.price).toFixed(2)}</span>
                                ${kCalDisplay ? `<span class="kcal">${kCalDisplay}</span>` : ''}
                            </div>
                            <button class="add-btn" 
                                data-name="${escapeHtml(displayName)}"
                                data-price="${product.price}"
                                data-image="${escapeHtml(imagePath)}"
                                data-product-id="${product.id || product.product_id}">
                                ${t('add_to_cart', currentLang)}
                            </button>
                        </div>
                    </div>
                `;
            });

            html += `</div>
            </section>`;
        }
        
        menuContainer.innerHTML = html;
        
        // Re-attach event listeners for add-to-cart buttons
        attachAddToCartListeners();
        
    } catch (error) {
        console.error('Error loading products:', error);
        menuContainer.innerHTML = `<div style="text-align: center; padding: 40px; color: #c00;">
            <p>Error laden van producten: ${escapeHtml(error.message)}</p>
        </div>`;
    }
});

/**
 * Attach event listeners to "Add" buttons
 * This works with the existing cart.js logic
 */
function attachAddToCartListeners() {
    const addButtons = document.querySelectorAll('.add-btn');
    addButtons.forEach(button => {
        button.addEventListener('click', function() {
            const mainName = this.dataset.name;
            const mainPrice = parseFloat(this.dataset.price);
            const mainImage = this.dataset.image;
            const mainId = this.dataset.productId || this.dataset.name;

            // Find up to two drink options dynamically from allProducts
            const upsellOptions = findDrinkOptions(mainName, 2);

            if (upsellOptions.length === 0) {
                // No upsell options: add directly
                dispatchAddToCart({ name: mainName, price: mainPrice, image: mainImage, id: mainId });
                return;
            }

            // Show modal to choose upsell
            showUpsellModal({ name: mainName, price: mainPrice, image: mainImage, id: mainId }, upsellOptions);
        });
    });
}

// Dispatch the shared addToCart event so existing cart handlers pick it up
function dispatchAddToCart(product) {
    // Create a temporary .add-btn element and trigger a click so existing kiosk.js
    // event delegation handles animation and saving to cart.
    try {
        const tempBtn = document.createElement('button');
        tempBtn.className = 'add-btn';
        tempBtn.style.display = 'none';
        tempBtn.dataset.name = product.name;
        tempBtn.dataset.price = String(product.price || 0);
        tempBtn.dataset.image = product.image || '';
        tempBtn.dataset.productId = product.id || product.product_id || product.name;
        document.body.appendChild(tempBtn);
        tempBtn.click();
        // Remove quickly after a tick to avoid interfering with layout
        setTimeout(() => { tempBtn.remove(); }, 50);
    } catch (e) {
        // Fallback: dispatch a CustomEvent in case click approach fails
        const event = new CustomEvent('addToCart', {
            detail: {
                name: product.name,
                price: Number(product.price),
                image: product.image || '',
                id: product.id || product.product_id || product.name
            }
        });
        document.dispatchEvent(event);
    }
}

// Find candidate drink options from window.allProducts
function findDrinkOptions(mainProductName, limit) {
    try {
        const list = window.allProducts || [];
        const drinkKeywords = ['drink','juice','smoothie','tea','coffee','latte','cola','soda','water','iced'];

        // score products by keyword match in name
        const scored = list.map(p => {
            const name = String(p.name || '').toLowerCase();
            let score = 0;
            for (const kw of drinkKeywords) if (name.includes(kw)) score += 10;
            // prefer different products than the main one
            if (name === String(mainProductName || '').toLowerCase()) score = 0;
            return {p, score};
        }).filter(x => x.score > 0)
        .sort((a,b) => b.score - a.score)
        .map(x => x.p);

        // return up to limit unique names
        const out = [];
        for (const p of scored) {
            if (out.length >= limit) break;
            if (!out.find(o => String(o.name || '').toLowerCase() === String(p.name || '').toLowerCase())) out.push(p);
        }
        return out;
    } catch (e) {
        return [];
    }
}

// Create and show a modal offering upsell choices
function showUpsellModal(mainProduct, options) {
    // remove existing modal if present
    const existing = document.getElementById('hh-upsell-modal');
    if (existing) existing.remove();

    const modal = document.createElement('div');
    modal.id = 'hh-upsell-modal';
    modal.innerHTML = `
        <div class="hh-upsell-backdrop" style="position:fixed;inset:0;background:rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;z-index:9999;">
            <div class="hh-upsell-box" style="background:#fff;padding:20px;border-radius:10px;max-width:420px;width:100%;box-shadow:0 8px 24px rgba(0,0,0,0.2);">
                <h3 style="margin:0 0 8px;">Wil je nog een drankje erbij?</h3>
                <p style="margin:0 0 12px;color:#444;">Kies een extra drankje of voeg alleen het product toe.</p>
                <div class="hh-upsell-options" style="display:flex;flex-direction:column;gap:8px;margin-bottom:12px;"></div>
                <div style="display:flex;gap:8px;justify-content:flex-end;">
                    <button id="hh-upsell-skip" style="padding:8px 12px;border-radius:6px;border:1px solid #ccc;background:#f6f6f6;">Alleen toevoegen</button>
                    <button id="hh-upsell-cancel" style="padding:8px 12px;border-radius:6px;border:1px solid #ccc;background:#fff;">Annuleer</button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);

    const optionsContainer = modal.querySelector('.hh-upsell-options');
    options.forEach(opt => {
        const row = document.createElement('div');
        row.style.display = 'flex';
        row.style.alignItems = 'center';
        row.style.justifyContent = 'space-between';
        row.style.gap = '12px';

        const left = document.createElement('div');
        left.style.display = 'flex';
        left.style.alignItems = 'center';
        left.style.gap = '12px';

        const img = document.createElement('img');
        img.src = opt.image || 'assets/images/image.png';
        img.style.width = '56px';
        img.style.height = '40px';
        img.style.objectFit = 'cover';
        img.style.borderRadius = '6px';

        const info = document.createElement('div');
        info.innerHTML = `<div style="font-weight:700">${escapeHtml(opt.name)}</div><div style="color:#666">€${Number(opt.price).toFixed(2)}</div>`;

        left.appendChild(img);
        left.appendChild(info);

        const btn = document.createElement('button');
        btn.textContent = 'Voeg toe';
        btn.style.padding = '8px 12px';
        btn.style.borderRadius = '6px';
        btn.style.background = '#8cd003';
        btn.style.color = '#fff';
        btn.style.border = 'none';

        btn.addEventListener('click', function() {
            // add main product then the option
            dispatchAddToCart(mainProduct);
            dispatchAddToCart(opt);
            modal.remove();
        });

        row.appendChild(left);
        row.appendChild(btn);
        optionsContainer.appendChild(row);
    });

    modal.querySelector('#hh-upsell-skip').addEventListener('click', function() {
        dispatchAddToCart(mainProduct);
        modal.remove();
    });
    modal.querySelector('#hh-upsell-cancel').addEventListener('click', function() {
        modal.remove();
    });
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}
