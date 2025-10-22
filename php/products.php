<?php
// Set proper headers for JSON responses
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in JSON response

// Capture any errors
ob_start();

try {
    require_once 'config.php';
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit;
}

// Product operations
function getAllProducts($limit = null, $featured = false, $category = null) {
    try {
        $pdo = getConnection();
        
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE 1=1";
        $params = [];
        
        if ($featured) {
            $sql .= " AND p.is_featured = 1";
        }
        
        if ($category) {
            $sql .= " AND p.category_id = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        if ($limit && is_numeric($limit)) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function getProductById($id) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                              LEFT JOIN categories c ON p.category_id = c.id 
                              WHERE p.id = ?");
        $stmt->execute([$id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

function searchProducts($query) {
    try {
        $pdo = getConnection();
        $searchTerm = "%$query%";
        
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?
            ORDER BY p.name
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function getCategories() {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT * FROM categories ORDER BY name");
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function filterProducts($filters) {
    try {
        $pdo = getConnection();
        
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
        $params = [];
        
        if (!empty($filters['category'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['gender'])) {
            $sql .= " AND p.gender = ?";
            $params[] = $filters['gender'];
        }
        
        if (!empty($filters['min_price'])) {
            $sql .= " AND p.price >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= ?";
            $params[] = $filters['max_price'];
        }
        
        if (!empty($filters['size'])) {
            $sql .= " AND JSON_CONTAINS(p.sizes, ?)";
            $params[] = '"' . $filters['size'] . '"';
        }
        
        if (!empty($filters['color'])) {
            $sql .= " AND JSON_CONTAINS(p.colors, ?)";
            $params[] = '"' . $filters['color'] . '"';
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function addProduct($data) {
    try {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("
            INSERT INTO products (name, description, price, category_id, gender, sizes, colors, 
                                image_url, stock_quantity, is_featured, is_new_arrival, is_on_sale, sale_price) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['category_id'],
            $data['gender'],
            json_encode($data['sizes']),
            json_encode($data['colors']),
            $data['image_url'],
            $data['stock_quantity'],
            $data['is_featured'] ?? 0,
            $data['is_new_arrival'] ?? 0,
            $data['is_on_sale'] ?? 0,
            $data['sale_price'] ?? null
        ]);
    } catch (Exception $e) {
        return false;
    }
}

function updateProduct($id, $data) {
    try {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("
            UPDATE products SET 
                name = ?, description = ?, price = ?, category_id = ?, gender = ?, 
                sizes = ?, colors = ?, image_url = ?, stock_quantity = ?, 
                is_featured = ?, is_new_arrival = ?, is_on_sale = ?, sale_price = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['category_id'],
            $data['gender'],
            json_encode($data['sizes']),
            json_encode($data['colors']),
            $data['image_url'],
            $data['stock_quantity'],
            $data['is_featured'] ?? 0,
            $data['is_new_arrival'] ?? 0,
            $data['is_on_sale'] ?? 0,
            $data['sale_price'] ?? null,
            $id
        ]);
    } catch (Exception $e) {
        return false;
    }
}

function deleteProduct($id) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    } catch (Exception $e) {
        return false;
    }
}

// Handle AJAX requests
try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'get_all':
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
                $featured = $_GET['featured'] === 'true' || $_GET['featured'] === '1';
                $category = $_GET['category'] ?? null;
                $products = getAllProducts($limit, $featured, $category);
                ob_clean(); // Clean any potential output
                echo json_encode($products);
                break;
                
            case 'get_by_id':
                $id = $_GET['id'] ?? 0;
                $product = getProductById($id);
                ob_clean();
                echo json_encode($product);
                break;
                
            case 'search':
                $query = $_GET['q'] ?? '';
                $results = searchProducts($query);
                ob_clean();
                echo json_encode(['products' => $results]);
                break;
                
            case 'get_categories':
                $categories = getCategories();
                ob_clean();
                echo json_encode($categories);
                break;
                
            default:
                ob_clean();
                echo json_encode(['error' => 'Invalid action']);
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'filter':
                $results = filterProducts($_POST);
                ob_clean();
                echo json_encode($results);
                break;
                
            default:
                ob_clean();
                echo json_encode(['error' => 'Invalid POST action']);
        }
    }
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'error' => 'Server error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>