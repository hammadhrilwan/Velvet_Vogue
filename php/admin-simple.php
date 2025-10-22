<?php
// Enable error reporting for debugging
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to catch any unwanted output
ob_start();

session_start();

// Database config
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'velvet_vogue');

function getConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        return null;
    }
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Clean any unwanted output
ob_clean();

// Ensure we always output JSON
header('Content-Type: application/json');

// Simple admin check - try multiple ways to verify admin access
function checkAdminAccess() {
    // Method 1: Check session
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        return true;
    }
    
    // Method 2: Check if admin credentials are in request
    if (isset($_POST['admin_verify']) || isset($_GET['admin_verify'])) {
        $username = $_POST['username'] ?? $_GET['username'] ?? '';
        $password = $_POST['password'] ?? $_GET['password'] ?? '';
        
        if ($username && $password) {
            // Try to login with provided credentials
            $result = loginAdmin($username, $password);
            return $result['success'] ?? false;
        }
    }
    
    return false;
}

// Get the action
$action = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
}

// Handle requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'add_product':
            if (checkAdminAccess()) {
                addProduct();
            } else {
                echo json_encode(['success' => false, 'message' => 'Admin access required for adding products']);
            }
            break;
        case 'update_product':
            if (checkAdminAccess()) {
                updateProduct();
            } else {
                echo json_encode(['success' => false, 'message' => 'Admin access required for updating products']);
            }
            break;
        case 'delete_product':
            if (checkAdminAccess()) {
                deleteProduct();
            } else {
                echo json_encode(['success' => false, 'message' => 'Admin access required for deleting products']);
            }
            break;
        case 'update_customer':
            if (checkAdminAccess()) {
                updateCustomer();
            } else {
                echo json_encode(['success' => false, 'message' => 'Admin access required for updating customers']);
            }
            break;
        case 'delete_customer':
            if (checkAdminAccess()) {
                deleteCustomer();
            } else {
                echo json_encode(['success' => false, 'message' => 'Admin access required for deleting customers']);
            }
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    switch ($action) {
        case 'get_product':
            if (checkAdminAccess()) {
                getProduct();
            } else {
                echo json_encode(['success' => false, 'message' => 'Admin access required for viewing products']);
            }
            break;
        case 'get_categories':
            // Allow category access for now - it's just reading data
            getCategories();
            break;
        case 'get_dashboard_stats':
            // Allow dashboard stats access - try to show data
            getDashboardStats();
            break;
        case 'get_all_products':
            // Allow product listing - it's just reading data
            getAllProducts();
            break;
        case 'get_all_customers':
            if (checkAdminAccess()) {
                getAllCustomers();
            } else {
                echo json_encode(['success' => false, 'message' => 'Admin access required for viewing customers']);
            }
            break;
        case 'get_customer':
            if (checkAdminAccess()) {
                getCustomer();
            } else {
                echo json_encode(['success' => false, 'message' => 'Admin access required for viewing customer details']);
            }
            break;
        case 'get_customer_stats':
            if (checkAdminAccess()) {
                getCustomerStats();
            } else {
                echo json_encode(['success' => false, 'message' => 'Admin access required for customer statistics']);
            }
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

// Admin functions (same as before)
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
        
        // Prepare sizes and colors safely
        $sizes = isset($_POST['sizes']) ? trim($_POST['sizes']) : '';
        $colors = isset($_POST['colors']) ? trim($_POST['colors']) : '';
        $gender = isset($_POST['gender']) ? trim($_POST['gender']) : 'Unisex';
        
        // Ensure they're not null
        if ($sizes === '') $sizes = 'S,M,L,XL';
        if ($colors === '') $colors = 'Black,White';
        if ($gender === '' || !in_array($gender, ['Men', 'Women', 'Unisex', 'Kids'])) $gender = 'Unisex';
        
        $stmt = $pdo->prepare("
            INSERT INTO products (name, description, price, category_id, stock_quantity, image_url, sizes, colors, gender) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            $_POST['price'],
            $_POST['category_id'],
            $_POST['stock_quantity'],
            $_POST['image_url'] ?? 'images/products/default-product.jpg',
            $sizes,
            $colors,
            $gender
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
        
        $product_id = $_POST['product_id'] ?? '';
        if (empty($product_id)) {
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            return;
        }
        
        // Build update query dynamically based on provided fields
        $fields = [];
        $values = [];
        
        $allowed_fields = ['name', 'description', 'price', 'category_id', 'stock_quantity', 'image_url', 'sizes', 'colors', 'gender'];
        
        foreach ($allowed_fields as $field) {
            if (isset($_POST[$field]) && $_POST[$field] !== '') {
                // Validate gender field
                if ($field === 'gender' && !in_array($_POST[$field], ['Men', 'Women', 'Unisex', 'Kids'])) {
                    continue; // Skip invalid gender values
                }
                $fields[] = "$field = ?";
                $values[] = $_POST[$field];
            }
        }
        
        if (empty($fields)) {
            echo json_encode(['success' => false, 'message' => 'No fields to update']);
            return;
        }
        
        $values[] = $product_id; // Add product_id for WHERE clause
        
        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        $result = $stmt->execute($values);
        
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
        
        $product_id = $_POST['product_id'] ?? '';
        if (empty($product_id)) {
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            return;
        }
        
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $result = $stmt->execute([$product_id]);
        
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
        
        $product_id = $_GET['product_id'] ?? '';
        if (empty($product_id)) {
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            return;
        }
        
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
        $stmt->execute([$product_id]);
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

// Customer Management Functions
function getAllCustomers() {
    try {
        $pdo = getConnection();
        
        // Build query with search and filters
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $limit = intval($_GET['limit'] ?? 50);
        $offset = intval($_GET['offset'] ?? 0);
        
        $sql = "SELECT id, first_name, last_name, email, phone, address, status, created_at FROM users WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($status)) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as total FROM users WHERE 1=1";
        if (!empty($search)) {
            $countSql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
        }
        if (!empty($status)) {
            $countSql .= " AND status = ?";
        }
        
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo json_encode([
            'success' => true, 
            'customers' => $customers,
            'total' => $total,
            'has_more' => ($offset + $limit) < $total
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function getCustomer() {
    try {
        $pdo = getConnection();
        
        $customer_id = $_GET['customer_id'] ?? '';
        if (empty($customer_id)) {
            echo json_encode(['success' => false, 'message' => 'Customer ID is required']);
            return;
        }
        
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, phone, address, status, created_at FROM users WHERE id = ?");
        $stmt->execute([$customer_id]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($customer) {
            echo json_encode(['success' => true, 'customer' => $customer]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Customer not found']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function updateCustomer() {
    try {
        $pdo = getConnection();
        
        $customer_id = $_POST['customer_id'] ?? '';
        if (empty($customer_id)) {
            echo json_encode(['success' => false, 'message' => 'Customer ID is required']);
            return;
        }
        
        // Build update query dynamically based on provided fields
        $fields = [];
        $values = [];
        
        $allowed_fields = ['first_name', 'last_name', 'email', 'phone', 'address', 'status'];
        
        foreach ($allowed_fields as $field) {
            if (isset($_POST[$field]) && $_POST[$field] !== '') {
                // Validate status field
                if ($field === 'status' && !in_array($_POST[$field], ['active', 'inactive', 'suspended'])) {
                    continue; // Skip invalid status values
                }
                // Validate email format
                if ($field === 'email' && !filter_var($_POST[$field], FILTER_VALIDATE_EMAIL)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                    return;
                }
                $fields[] = "$field = ?";
                $values[] = $_POST[$field];
            }
        }
        
        if (empty($fields)) {
            echo json_encode(['success' => false, 'message' => 'No valid fields to update']);
            return;
        }
        
        $values[] = $customer_id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($values);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Customer updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update customer']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function deleteCustomer() {
    try {
        $pdo = getConnection();
        
        $customer_id = $_POST['customer_id'] ?? '';
        if (empty($customer_id)) {
            echo json_encode(['success' => false, 'message' => 'Customer ID is required']);
            return;
        }
        
        // Don't actually delete, just deactivate for data integrity
        $stmt = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
        $result = $stmt->execute([$customer_id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Customer account deactivated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to deactivate customer']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function getCustomerStats() {
    try {
        $pdo = getConnection();
        
        // Total customers
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $totalCustomers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Active customers
        $stmt = $pdo->query("SELECT COUNT(*) as active FROM users WHERE status = 'active'");
        $activeCustomers = $stmt->fetch(PDO::FETCH_ASSOC)['active'];
        
        // New customers this month
        $stmt = $pdo->query("SELECT COUNT(*) as new_this_month FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
        $newThisMonth = $stmt->fetch(PDO::FETCH_ASSOC)['new_this_month'];
        
        // Customers by status
        $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM users GROUP BY status");
        $statusBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stats = [
            'total_customers' => $totalCustomers,
            'active_customers' => $activeCustomers,
            'new_this_month' => $newThisMonth,
            'status_breakdown' => $statusBreakdown
        ];
        
        echo json_encode(['success' => true, 'stats' => $stats]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>