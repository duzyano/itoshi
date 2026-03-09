<?php
$lang = 'nl'; // default
if(isset($_COOKIE['hh_language'])) $lang = $_COOKIE['hh_language'];

$translations = [
    'nl' => [
        'breakfast' => 'Ontbijt',
        'lunch_dinner' => 'Lunch & Diner',
        'wraps_sandwiches' => 'Wraps & Sandwiches',
        'sides' => 'Bijgerechten',
        'dips' => 'Dips',
        'drinks' => 'Dranken',
        'add' => 'Toevoegen',
        'your_cart' => 'Je winkelwagen',
        'cart_empty' => 'Winkelwagen is leeg'
    ],
    'en' => [
        'breakfast' => 'Breakfast',
        'lunch_dinner' => 'Lunch & Dinner',
        'wraps_sandwiches' => 'Wraps & Sandwiches',
        'sides' => 'Sides',
        'dips' => 'Dips',
        'drinks' => 'Drinks',
        'add' => 'Add',
        'your_cart' => 'Your Cart',
        'cart_empty' => 'Cart is empty'
    ],
    'fr' => [
        'breakfast' => 'Petit-Déjeuner',
        'lunch_dinner' => 'Déjeuner & Dîner',
        'wraps_sandwiches' => 'Wraps & Sandwiches',
        'sides' => 'Accompagnements',
        'dips' => 'Dips',
        'drinks' => 'Boissons',
        'add' => 'Ajouter',
        'your_cart' => 'Panier',
        'cart_empty' => 'Panier vide'
    ]
];

function t($key){
    global $translations, $lang;
    return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
}