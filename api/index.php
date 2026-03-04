<?php
// api/index.php
// Main API router - handles all API requests

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/ResponseHelper.php';
require_once __DIR__ . '/ProductController.php';
require_once __DIR__ . '/OrderController.php';

// Parse request URI
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Remove the base path /itoshi/api/index.php and everything before it
$request = preg_replace('#.*/api/index\.php#', '', $request);
// Remove leading slash
$request = ltrim($request, '/');
$method = $_SERVER['REQUEST_METHOD'];

// If empty request, treat as root
if (empty($request)) {
    ResponseHelper::success([], 'API is ready');
    exit;
}

// Route the API requests
if (preg_match('/^products(?:\/(\d+))?$/', $request, $matches)) {
    $id = $matches[1] ?? null;
    $controller = new ProductController($conn);
    
    if ($method === 'GET') {
        if ($id) {
            $controller->getProduct($id);
        } else {
            $controller->getAllProducts();
        }
    } else {
        ResponseHelper::error('Method not allowed', 405);
    }
} elseif (preg_match('/^products\/(\d+)\/related$/', $request, $matches)) {
    $id = $matches[1];
    $controller = new ProductController($conn);
    if ($method === 'GET') {
        $controller->getRelatedProducts($id);
    }
} elseif ($request === 'categories') {
    $controller = new ProductController($conn);
    if ($method === 'GET') {
        $controller->getAllCategories();
    } else {
        ResponseHelper::error('Method not allowed', 405);
    }
} elseif (preg_match('/^orders(?:\/(\d+))?$/', $request, $matches)) {
    $id = $matches[1] ?? null;
    $controller = new OrderController($conn);
    
    if ($method === 'POST' && !$id) {
        $controller->createOrder();
    } elseif ($method === 'GET' && $id) {
        $controller->getOrder($id);
    } elseif ($method === 'GET') {
        $controller->getAllOrders();
    } else {
        ResponseHelper::error('Method not allowed', 405);
    }
} else {
    ResponseHelper::error('Endpoint not found', 404);
}
?>
