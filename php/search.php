<?php
require_once 'config.php';
require_once 'products.php';

// Handle search requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['q'])) {
    $query = trim($_GET['q']);
    
    if (empty($query)) {
        echo json_encode(['products' => []]);
        exit;
    }
    
    $products = searchProducts($query);
    echo json_encode(['products' => $products]);
    exit;
}

// Return empty result if no valid query
echo json_encode(['products' => []]);
?>