<?php
/**
 * Application Configuration
 * DISC Assessment Platform
 */

// Security settings
define('SECURITY_SALT', 'disc_assessment_2024_security_salt_change_this');
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('ADMIN_SESSION_TIMEOUT', 7200); // 2 hours for admin

// Application settings
define('APP_NAME', 'DISC Leadership Assessment');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/DISC'); // Change this to your domain

// Assessment settings
define('TOTAL_QUESTIONS', 24);
define('ACCESS_CODE_LENGTH', 8);
define('ACCESS_CODE_EXPIRY_DAYS', 30);

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'svg']);

// Error reporting (set to false in production)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Start session with security settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_strict_mode', 1);
    // For localhost development, ensure sessions work properly
    ini_set('session.cookie_domain', '');
    session_start();
}

// CORS headers for API endpoints
function setCorsHeaders() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json; charset=utf-8');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

// Security functions
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Response helpers
function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit();
}

function sendError($message, $status = 400) {
    sendResponse(['error' => $message], $status);
}

function sendSuccess($data = null, $message = 'Success') {
    $response = ['success' => true, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    sendResponse($response);
}
?>
