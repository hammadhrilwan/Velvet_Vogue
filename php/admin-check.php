<?php
// Simple admin check endpoint
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

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Clean any unwanted output
ob_clean();

header('Content-Type: application/json');

try {
    $response = [
        'session_id' => session_id(),
        'admin_logged_in' => isAdminLoggedIn(),
        'session_admin_logged_in' => $_SESSION['admin_logged_in'] ?? 'not set',
        'session_admin_id' => $_SESSION['admin_id'] ?? 'not set',
        'session_admin_username' => $_SESSION['admin_username'] ?? 'not set',
        'all_session_data' => $_SESSION,
        'server_time' => date('Y-m-d H:i:s'),
        'session_cookie_params' => session_get_cookie_params()
    ];
    
    // If not logged in, try to check if we can log in
    if (!isAdminLoggedIn()) {
        $response['login_attempt'] = 'Admin not logged in, session not established';
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>