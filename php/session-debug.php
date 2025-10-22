<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to avoid breaking JSON
ini_set('log_errors', 1);

session_start();
require_once 'config.php';
require_once 'auth.php';

// Ensure we always output JSON
header('Content-Type: application/json');

try {
    // Return session status as JSON
    $response = [
        'session_id' => session_id(),
        'session_data' => $_SESSION,
        'admin_logged_in' => isAdminLoggedIn(),
        'cookie_params' => session_get_cookie_params(),
        'session_name' => session_name()
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => 'Debug error: ' . $e->getMessage()]);
}
?>