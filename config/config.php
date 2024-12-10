<?php

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'sledgepos';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
}

date_default_timezone_set('Europe/Berlin');

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en'; 
}

$translations = [
    'de' => [
        'app_name' => 'sledgePOS',
        'logged_in_as' => 'Eingeloggt als:',
        'logout' => 'Logout',
        'settings' => 'Einstellungen',
        'categories' => 'Kategorien',
        'all' => 'Alle',
        'search_product' => 'Produkt suchen...',
        'search' => 'Suchen',
        'barcode_name' => 'Barcode/Name...',
        'scan_add' => 'Scannen/Hinzufügen',
        'manual_price' => 'Eigener Preis',
        'add' => 'Hinzufügen',
        'cart' => 'Warenkorb',
        'empty_cart' => 'Warenkorb ist leer.',
        'subtotal' => 'Zwischensumme',
        'tax' => 'Steuern',
        'total' => 'Gesamt',
        'pay' => 'Bezahlen',
        'given' => 'Gegeben',
        'change' => 'Rückgeld',
        'insufficient_given' => 'Nicht genug Betrag gegeben.',
        'thank_you' => 'Vielen Dank für Ihren Einkauf!',
        'print' => 'Drucken',
        'order_number' => 'Bestellnummer',
        'date' => 'Datum',
        'employee' => 'Mitarbeiter',
        'no_products_found' => 'Keine Produkte gefunden.',
        'add_to_cart' => 'In Warenkorb',
        'language' => 'Sprache',
        'language_de' => 'Deutsch',
        'language_en' => 'Englisch',
        'print_receipt' => 'Bon drucken'
    ],
    'en' => [
        'app_name' => 'sledgePOS',
        'logged_in_as' => 'Logged in as:',
        'logout' => 'Logout',
        'settings' => 'Settings',
        'categories' => 'Categories',
        'all' => 'All',
        'search_product' => 'Search product...',
        'search' => 'Search',
        'barcode_name' => 'Barcode/Name...',
        'scan_add' => 'Scan/Add',
        'manual_price' => 'Custom Price',
        'add' => 'Add',
        'cart' => 'Cart',
        'empty_cart' => 'Cart is empty.',
        'subtotal' => 'Subtotal',
        'tax' => 'Tax',
        'total' => 'Total',
        'pay' => 'Pay',
        'given' => 'Given',
        'change' => 'Change',
        'insufficient_given' => 'Not enough amount given.',
        'thank_you' => 'Thank you for your purchase!',
        'print' => 'Print',
        'order_number' => 'Order number',
        'date' => 'Date',
        'employee' => 'Employee',
        'no_products_found' => 'No products found.',
        'add_to_cart' => 'Add to cart',
        'language' => 'Language',
        'language_de' => 'German',
        'language_en' => 'English',
        'print_receipt' => 'Print receipt'
    ]
];

function t($key) {
    global $translations;
    $lang = $_SESSION['lang'] ?? 'de';
    return isset($translations[$lang][$key]) ? $translations[$lang][$key] : $key;
}
if (!isset($_SESSION['receipt_config'])) {
    $_SESSION['receipt_config'] = [
        'company_name' => 'Mein Unternehmen GmbH',
        'company_address' => 'Musterstraße 1, 12345 Musterstadt',
        'system_serial' => 'SYS123456',
        'signature_counter' => 'SC-0001',
        'check_value' => 'CHK-XYZ123',
        'previous_order_start' => '2024-12-01 12:00:00'
    ];
}