<?php
require_once 'config.php';

// This version of customer management works without the status column
// as a fallback solution

function getAllCustomersNoStatus() {
    try {
        $pdo = getConnection();
        
        // Build query without status column
        $search = $_GET['search'] ?? '';
        $limit = intval($_GET['limit'] ?? 50);
        $offset = intval($_GET['offset'] ?? 0);
        
        $sql = "SELECT id, first_name, last_name, email, phone, address, created_at FROM users WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add default status to each customer
        foreach ($customers as &$customer) {
            $customer['status'] = 'active'; // Default status
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM users WHERE 1=1";
        if (!empty($search)) {
            $countSql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
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

function updateCustomerNoStatus() {
    try {
        $pdo = getConnection();
        
        $customer_id = $_POST['customer_id'] ?? '';
        if (empty($customer_id)) {
            echo json_encode(['success' => false, 'message' => 'Customer ID is required']);
            return;
        }
        
        // Build update query without status field
        $fields = [];
        $values = [];
        
        $allowed_fields = ['first_name', 'last_name', 'email', 'phone', 'address'];
        
        foreach ($allowed_fields as $field) {
            if (isset($_POST[$field]) && $_POST[$field] !== '') {
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

function getCustomerStatsNoStatus() {
    try {
        $pdo = getConnection();
        
        // Get stats without status column
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $totalCustomers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // All customers are considered active since we don't have status
        $activeCustomers = $totalCustomers;
        
        // New customers this month
        $stmt = $pdo->query("SELECT COUNT(*) as new_this_month FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
        $newThisMonth = $stmt->fetch(PDO::FETCH_ASSOC)['new_this_month'];
        
        $stats = [
            'total_customers' => $totalCustomers,
            'active_customers' => $activeCustomers,
            'new_this_month' => $newThisMonth,
            'status_breakdown' => [
                ['status' => 'active', 'count' => $totalCustomers]
            ]
        ];
        
        echo json_encode(['success' => true, 'stats' => $stats]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function getDashboardStatsNoStatus() {
    try {
        $pdo = getConnection();
        
        // Get basic stats
        $stats = [];
        
        // Total products
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
        $stats['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total categories
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
        $stats['total_categories'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total customers (without status dependency)
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $stats['total_customers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total orders (placeholder since we don't have an orders table)
        $stats['total_orders'] = 0;
        
        // Total revenue (placeholder since we don't have an orders table)
        $stats['total_revenue'] = '0.00';
        
        // Recent activity (products added this month)
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
        $stats['products_this_month'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // New customers this month
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
        $stats['customers_this_month'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        echo json_encode(['success' => true, 'stats' => $stats]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Handle the request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_all_customers':
        getAllCustomersNoStatus();
        break;
    case 'update_customer':
        updateCustomerNoStatus();
        break;
    case 'get_customer_stats':
        getCustomerStatsNoStatus();
        break;
    case 'get_dashboard_stats':
        getDashboardStatsNoStatus();
        break;
    case 'get_customer':
        // Simple customer lookup
        try {
            $pdo = getConnection();
            $customer_id = $_GET['customer_id'] ?? '';
            if (empty($customer_id)) {
                echo json_encode(['success' => false, 'message' => 'Customer ID is required']);
                return;
            }
            
            $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, phone, address, created_at FROM users WHERE id = ?");
            $stmt->execute([$customer_id]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($customer) {
                $customer['status'] = 'active'; // Default status
                echo json_encode(['success' => true, 'customer' => $customer]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Customer not found']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>