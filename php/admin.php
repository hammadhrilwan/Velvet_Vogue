<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to avoid breaking JSON
ini_set('log_errors', 1);

require_once 'config.php';
require_once 'auth.php';

// Ensure we always output JSON
header('Content-Type: application/json');

// Check if admin is logged in
session_start();

// Get the action first to decide if authentication is required
$action = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
}

try {
    if (!isAdminLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Admin access required. Please login first.']);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Authentication error: ' . $e->getMessage()]);
    exit;
}

// Handle requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_product':
            addProduct();
            break;
        case 'update_product':
            updateProduct();
            break;
        case 'delete_product':
            deleteProduct();
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_product':
            getProduct();
            break;
        case 'get_categories':
            getCategories();
            break;
        case 'get_dashboard_stats':
            getDashboardStats();
            break;
        case 'get_all_products':
            getAllProducts();
            break;
    }
}

function addProduct() {
    try {
        $pdo = getConnection();
        
        // Validate required fields
        $required = ['name', 'description', 'price', 'category_id', 'stock_quantity'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                return;
            }
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO products (name, description, price, category_id, stock_quantity, image_url, sizes, colors) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            $_POST['price'],
            $_POST['category_id'],
            $_POST['stock_quantity'],
            $_POST['image_url'] ?? 'images/products/default-product.jpg',
            $_POST['sizes'] ?? '',
            $_POST['colors'] ?? ''
        ]);
        
        if ($result) {
            $productId = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'message' => 'Product added successfully', 'product_id' => $productId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add product']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function updateProduct() {
    try {
        $pdo = getConnection();
        
        $productId = $_POST['product_id'] ?? 0;
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
            return;
        }
        
        // Check if product exists
        $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }
        
        $stmt = $pdo->prepare("
            UPDATE products SET 
                name = ?, description = ?, price = ?, category_id = ?, 
                stock_quantity = ?, image_url = ?, sizes = ?, colors = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        $result = $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            $_POST['price'],
            $_POST['category_id'],
            $_POST['stock_quantity'],
            $_POST['image_url'],
            $_POST['sizes'] ?? '',
            $_POST['colors'] ?? '',
            $productId
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update product']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function deleteProduct() {
    try {
        $pdo = getConnection();
        
        $productId = $_POST['product_id'] ?? 0;
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
            return;
        }
        
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $result = $stmt->execute([$productId]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete product']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function getProduct() {
    try {
        $pdo = getConnection();
        
        $productId = $_GET['product_id'] ?? 0;
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
            return;
        }
        
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            echo json_encode(['success' => true, 'product' => $product]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function getCategories() {
    try {
        $pdo = getConnection();
        
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'categories' => $categories]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function getDashboardStats() {
    try {
        $pdo = getConnection();
        
        // Get total products
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
        $totalProducts = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get low stock products (less than 10)
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE stock_quantity < 10");
        $lowStockProducts = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get total categories
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM categories");
        $totalCategories = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get out of stock products
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE stock_quantity = 0");
        $outOfStockProducts = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get total users
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $stats = [
            'total_products' => $totalProducts,
            'low_stock_products' => $lowStockProducts,
            'total_categories' => $totalCategories,
            'out_of_stock_products' => $outOfStockProducts,
            'total_users' => $totalUsers
        ];
        
        echo json_encode(['success' => true, 'stats' => $stats]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function getAllProducts() {
    try {
        $pdo = getConnection();
        
        $stmt = $pdo->query("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.created_at DESC
        ");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'products' => $products]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>