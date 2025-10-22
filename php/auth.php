<?php
// Suppress all error output to prevent breaking JSON
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once 'config.php';

// User authentication functions
function registerUser($data) {
    try {
        $pdo = getConnection();
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already exists'];
        }
        
        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Insert new user (using correct column names)
        $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email, password_hash, phone) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $hashedPassword,
            $data['phone'] ?? ''
        ]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Registration successful'];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Registration error: ' . $e->getMessage()];
    }
}

function loginUser($email, $password) {
    try {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['logged_in'] = true;
            
            return ['success' => true, 'message' => 'Login successful', 'user' => $user];
        } else {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Login error'];
    }
}

function logoutUser() {
    session_destroy();
    return ['success' => true, 'message' => 'Logged out successfully'];
}

function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function getUserById($id) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

function updateUser($id, $data) {
    try {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("
            UPDATE users SET 
                first_name = ?, last_name = ?, email = ?, phone = ?, 
                address = ?, city = ?, state = ?, zip_code = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['address'] ?? '',
            $data['city'] ?? '',
            $data['state'] ?? '',
            $data['zip_code'] ?? '',
            $id
        ]);
    } catch (Exception $e) {
        return false;
    }
}

function changePassword($userId, $currentPassword, $newPassword) {
    try {
        $pdo = getConnection();
        
        // Get current password hash
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
        
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $result = $stmt->execute([$hashedPassword, $userId]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Password changed successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to change password'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error changing password'];
    }
}

// Admin authentication functions
function loginAdmin($username, $password) {
    try {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_logged_in'] = true;
            
            return ['success' => true, 'message' => 'Admin login successful', 'admin' => $admin];
        } else {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Login error'];
    }
}

function registerAdmin($data) {
    try {
        $pdo = getConnection();
        
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM admin WHERE username = ? OR email = ?");
        $stmt->execute([$data['username'], $data['email']]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO admin (username, email, password, full_name, role) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['full_name'],
            $data['role'] ?? 'admin'
        ]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Admin registration successful'];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Registration error'];
    }
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Handle requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure JSON output
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'register':
            echo json_encode(registerUser($_POST));
            break;
            
        case 'login':
            $result = loginUser($_POST['email'], $_POST['password']);
            echo json_encode($result);
            break;
            
        case 'logout':
            echo json_encode(logoutUser());
            break;
            
        case 'update_profile':
            if (isLoggedIn()) {
                $result = updateUser($_SESSION['user_id'], $_POST);
                echo json_encode(['success' => $result]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Not logged in']);
            }
            break;
            
        case 'change_password':
            if (isLoggedIn()) {
                $result = changePassword($_SESSION['user_id'], $_POST['current_password'], $_POST['new_password']);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'message' => 'Not logged in']);
            }
            break;
            
        case 'admin_login':
            $result = loginAdmin($_POST['username'], $_POST['password']);
            echo json_encode($result);
            break;
            
        case 'admin_register':
            // Check if this is the first admin (bootstrap mode)
            $pdo = getConnection();
            $stmt = $pdo->query('SELECT COUNT(*) FROM admin');
            $adminCount = $stmt->fetchColumn();
            
            if ($adminCount == 0 || isAdminLoggedIn()) {
                $result = registerAdmin($_POST);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'message' => 'Admin access required. Please login first or contact system administrator.']);
            }
            break;
            
        case 'check_admin_status':
            $pdo = getConnection();
            $stmt = $pdo->query('SELECT COUNT(*) FROM admin');
            $adminCount = $stmt->fetchColumn();
            
            $response = [
                'can_register' => ($adminCount == 0 || isAdminLoggedIn()),
                'is_bootstrap' => ($adminCount == 0),
                'is_logged_in' => isAdminLoggedIn(),
                'admin_count' => $adminCount
            ];
            
            echo json_encode($response);
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle GET requests for status checks
    $action = $_GET['action'] ?? '';
    
    if ($action === 'check_admin_status') {
        $pdo = getConnection();
        $stmt = $pdo->query('SELECT COUNT(*) FROM admin');
        $adminCount = $stmt->fetchColumn();
        
        $response = [
            'can_register' => ($adminCount == 0 || isAdminLoggedIn()),
            'is_bootstrap' => ($adminCount == 0),
            'is_logged_in' => isAdminLoggedIn(),
            'admin_count' => $adminCount
        ];
        
        echo json_encode($response);
    }
}
?>