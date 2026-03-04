<?php
// includes/language.php
// Language configuration and translations

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current language from session or default to Dutch
$current_language = $_SESSION['language'] ?? 'nl';

// Allow language switching via GET parameter
if (isset($_GET['lang']) && in_array($_GET['lang'], ['nl', 'fr', 'en'])) {
    $_SESSION['language'] = $_GET['lang'];
    $current_language = $_GET['lang'];
}

// Translation strings
$translations = [
    'en' => [
        // Home page
        'welcome' => 'Welcome to Happy Herbivore',
        'subtitle' => 'Delicious plant-based food, made fresh daily',
        'cta' => 'Touch anywhere to start your order',
        'footer_tagline' => '100% Plant-Based · Locally Sourced · Sustainable',
        
        // Order type page
        'choose_order_type' => 'Choose Your Order Type',
        'order_question' => 'Will you eat here or take away?',
        'eat_here' => 'Eat Here',
        'eat_here_description' => 'Enjoy your meal with us in the restaurant.',
        'takeaway' => 'Takeaway',
        'takeaway_description' => 'Take it with you and enjoy at home or on the go.',
        
        // Menu page
        'loading_products' => 'Loading products...',
        'no_products' => 'No products available.',
        'menu_title' => 'Happy Herbivore - Menu',
        
        // Shopping cart
        'your_cart' => 'Your Cart',
        'empty_cart' => 'Your cart is empty',
        'back_to_menu' => 'Back to Menu',
        'continue_shopping' => 'Continue Shopping',
        'subtotal' => 'Subtotal:',
        'delivery_cost' => 'Delivery:',
        'total' => 'Total:',
        'checkout' => 'Checkout',
        'suggestions' => '✨ Suggestions to complete your order',
        
        // Header
        'shopping_cart' => 'Shopping Cart',
        'plant_based_menu' => 'Plant-Based Menu',
        
        // Footer
        'hours' => 'Hours',
        'hours_weekday' => 'Mon–Fri: 9am–7pm',
        'hours_weekend' => 'Sat–Sun: 10am–6pm',
        'contact' => 'Contact',
        'phone' => '+31 (0) 123 456 789',
        'email' => 'hello@happyherbivore.nl',
        'copyright' => '© 2025 Happy Herbivore. All rights reserved.',
        
        // Language selector
        'language' => 'Language',
        
        // Order Review
        'order_review' => 'Order Review',
        'confirm_order' => 'Confirm Order',
        'back' => 'Back',
        'order_confirmation' => 'Order Confirmation',
    ],
    'nl' => [
        // Home page
        'welcome' => 'Welkom bij Happy Herbivore',
        'subtitle' => 'Heerlijk plantaardig eten, vers bereid',
        'cta' => 'Raak hier aan om je bestelling te starten',
        'footer_tagline' => '100% Plantaardig · Lokaal Geproduceerd · Duurzaam',
        
        // Order type page
        'choose_order_type' => 'Kies je besteltype',
        'order_question' => 'Wil je hier eten of meenemen?',
        'eat_here' => 'Hier eten',
        'eat_here_description' => 'Geniet van je maaltijd bij ons in het restaurant.',
        'takeaway' => 'Meenemen',
        'takeaway_description' => 'Neem het mee en geniet thuis of onderweg.',
        
        // Menu page
        'loading_products' => 'Producten laden...',
        'no_products' => 'Geen producten beschikbaar.',
        'menu_title' => 'Menu - Happy Herbivore',
        
        // Shopping cart
        'your_cart' => 'Jouw winkelwagen',
        'empty_cart' => 'Je winkelwagen is leeg',
        'back_to_menu' => 'Terug naar menu',
        'continue_shopping' => 'Verder bestellen',
        'subtotal' => 'Subtotaal:',
        'delivery_cost' => 'Bezorgkosten:',
        'total' => 'Totaal:',
        'checkout' => 'Afrekenen',
        'suggestions' => '✨ Suggesties om je bestelling compleet te maken',
        
        // Header
        'shopping_cart' => 'Winkelwagen',
        'plant_based_menu' => 'Plantaardige Menu',
        
        // Footer
        'hours' => 'Openingstijden',
        'hours_weekday' => 'Ma–Vr: 9:00–19:00',
        'hours_weekend' => 'Za–Zo: 10:00–18:00',
        'contact' => 'Contact',
        'phone' => '+31 (0) 123 456 789',
        'email' => 'hello@happyherbivore.nl',
        'copyright' => '© 2025 Happy Herbivore. Alle rechten voorbehouden.',
        
        // Language selector
        'language' => 'Taal',
        
        // Order Review
        'order_review' => 'Besteloverzicht',
        'confirm_order' => 'Bestelling Bevestigen',
        'back' => 'Terug',
        'order_confirmation' => 'Bestelbevestiging',
    ],
    'fr' => [
        // Home page
        'welcome' => 'Bienvenue chez Happy Herbivore',
        'subtitle' => 'Délicious nourriture à base de plantes, faite frais tous les jours',
        'cta' => 'Touchez n\'importe où pour commencer votre commande',
        'footer_tagline' => '100% À Base de Plantes · Produit Localement · Durable',
        
        // Order type page
        'choose_order_type' => 'Choisissez Votre Type de Commande',
        'order_question' => 'Allez-vous manger ici ou emporter?',
        'eat_here' => 'Manger Ici',
        'eat_here_description' => 'Profitez de votre repas avec nous au restaurant.',
        'takeaway' => 'À Emporter',
        'takeaway_description' => 'Prenez-le avec vous et profitez-en à la maison ou en chemin.',
        
        // Menu page
        'loading_products' => 'Chargement des produits...',
        'no_products' => 'Aucun produit disponible.',
        'menu_title' => 'Menu - Happy Herbivore',
        
        // Shopping cart
        'your_cart' => 'Votre Panier',
        'empty_cart' => 'Votre panier est vide',
        'back_to_menu' => 'Retour au Menu',
        'continue_shopping' => 'Continuer Vos Achats',
        'subtotal' => 'Sous-total:',
        'delivery_cost' => 'Frais de Livraison:',
        'total' => 'Total:',
        'checkout' => 'Passer la Commande',
        'suggestions' => '✨ Suggestions pour compléter votre commande',
        
        // Header
        'shopping_cart' => 'Panier',
        'plant_based_menu' => 'Menu Végétal',
        
        // Footer
        'hours' => 'Horaires',
        'hours_weekday' => 'Lun–Ven: 9h–19h',
        'hours_weekend' => 'Sam–Dim: 10h–18h',
        'contact' => 'Contact',
        'phone' => '+31 (0) 123 456 789',
        'email' => 'hello@happyherbivore.nl',
        'copyright' => '© 2025 Happy Herbivore. Tous droits réservés.',
        
        // Language selector
        'language' => 'Langue',
        
        // Order Review
        'order_review' => 'Aperçu de la Commande',
        'confirm_order' => 'Confirmer la Commande',
        'back' => 'Retour',
        'order_confirmation' => 'Confirmation de Commande',
    ],
];

// Function to get translated string
if (!function_exists('t')) {
    function t($key, $lang = null) {
        global $current_language, $translations;
        
        if ($lang === null) {
            $lang = $current_language;
        }
        
        return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
    }
}

// Function to get all available languages
if (!function_exists('getAvailableLanguages')) {
    function getAvailableLanguages() {
        return [
            'en' => 'English',
            'nl' => 'Nederlands',
            'fr' => 'Français',
        ];
    }
}
?>
