// assets/language.js
// Language translations for JavaScript

const translations = {
    'en': {
        'no_products': 'No products available.',
        'loading_products': 'Loading products...',
        'breakfast': 'Breakfast',
        'lunch_dinner': 'Lunch & Dinner',
        'wraps': 'Wraps',
        'sides_small_plates': 'Sides & Small Plates',
        'signature_dips': 'Signature Dips',
        'drinks': 'Drinks',
        'empty_cart': 'Your cart is empty',
        'back_to_menu': 'Back to Menu',
        'continue_shopping': 'Continue Shopping',
        'remove': 'Remove',
        'add_to_cart': 'Add to Cart',
        'suggestions': '✨ Suggestions to complete your order',
        'vegan': 'Vegan',
        'vegetarian': 'Vegetarian',
        'meat': 'Meat',
        // upsell modal keys
        'upsell_prompt_title': 'Add another drink?',
        'upsell_prompt_description': 'Choose an extra drink or just add the item.',
        'upsell_skip': 'Add only item',
        'upsell_cancel': 'Cancel',
        'perfect_pairing_title': 'Perfect pairing!',
        'perfect_pairing_suggestion': 'We suggest:',
        'upsell_accept': 'Yes, add it!',
        'upsell_decline': 'No thanks',
    },
    'nl': {
        'no_products': 'Geen producten beschikbaar.',
        'loading_products': 'Producten laden...',
        'breakfast': 'Ontbijt',
        'lunch_dinner': 'Lunch & Diner',
        'wraps': 'Wraps',
        'sides_small_plates': 'Bijgerechten & Kleine Gerechten',
        'signature_dips': 'Signature Dips',
        'drinks': 'Dranken',
        'empty_cart': 'Je winkelwagen is leeg',
        'back_to_menu': 'Terug naar menu',
        'continue_shopping': 'Verder bestellen',
        'remove': 'Verwijderen',
        'add_to_cart': 'Toevoegen aan winkelwagen',
        'suggestions': '✨ Suggesties om je bestelling compleet te maken',
        'vegan': 'Veganistisch',
        'vegetarian': 'Vegetarisch',
        'meat': 'Vlees',
        // upsell modal keys
        'upsell_prompt_title': 'Voeg nog een drankje toe?',
        'upsell_prompt_description': 'Kies een extra drankje of voeg alleen het product toe.',
        'upsell_skip': 'Alleen toevoegen',
        'upsell_cancel': 'Annuleer',
        'perfect_pairing_title': 'Perfecte combinatie!',
        'perfect_pairing_suggestion': 'Wij suggereren:',
        'upsell_accept': 'Ja, voeg het toe!',
        'upsell_decline': 'Nee bedankt',
    },
    
    'fr': {
        'no_products': 'Aucun produit disponible.',
        'loading_products': 'Chargement des produits...',
        'breakfast': 'Petit-Déjeuner',
        'lunch_dinner': 'Déjeuner & Dîner',
        'wraps': 'Wraps',
        'sides_small_plates': 'Accompagnements & Petits Plats',
        'signature_dips': 'Signature Dips',
        'drinks': 'Boissons',
        'empty_cart': 'Votre panier est vide',
        'back_to_menu': 'Retour au Menu',
        'continue_shopping': 'Continuer Vos Achats',
        'remove': 'Retirer',
        'add_to_cart': 'Ajouter au Panier',
        'suggestions': '✨ Suggestions pour compléter votre commande',
        'vegan': 'Végan',
        'vegetarian': 'Végétarien',
        'meat': 'Viande',
        // upsell modal keys

        'upsell_prompt_title': 'Ajouter une autre boisson ?',
        'upsell_prompt_description': 'Choisissez une boisson supplémentaire ou ajoutez simplement l\'article.',
        'upsell_skip': 'Ajouter seulement l\'article',
        'upsell_cancel': 'Annuler',
        'perfect_pairing_title': 'Accord parfait !',
        'perfect_pairing_suggestion': 'Nous suggérons :',
        'upsell_accept': 'Oui, ajoutez-le !',
        'upsell_decline': 'Non merci'
        }
};

// Get current language from cookie or local storage
function getCurrentLanguage() {
    return localStorage.getItem('hh_language') || 'nl';
}

// Set language to cookie and local storage
function setLanguage(lang) {
    if (['en', 'nl', 'fr'].includes(lang)) {
        localStorage.setItem('hh_language', lang);
    }
}

// Translate function
function t(key, lang = null) {
    if (lang === null) {
        lang = getCurrentLanguage();
    }
    
    return translations[lang]?.[key] || translations['en']?.[key] || key;
}

// Initialize language from document lang attribute
document.addEventListener('DOMContentLoaded', function() {
    const htmlLang = document.documentElement.lang || 'nl';
    setLanguage(htmlLang);
});
