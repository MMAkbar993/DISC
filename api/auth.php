<?php
/**
 * Authentication API Endpoints
 * DISC Assessment Platform
 */

require_once '../config/config.php';
require_once '../config/database.php';

setCorsHeaders();

// Ensure session is started for authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);

    switch ($method) {
        case 'POST':
            if (isset($input['action'])) {
                switch ($input['action']) {
                    case 'validate_access_code':
                        validateAccessCode($db, $input);
                        break;
                    case 'admin_login':
                        adminLogin($db, $input);
                        break;
                    case 'change_password':
                        changePassword($db, $input);
                        break;
                    default:
                        sendError('Invalid action');
                }
            } else {
                sendError('Action required');
            }
            break;
        default:
            sendError('Method not allowed', 405);
    }
} catch (Exception $e) {
    sendError('Server error: ' . $e->getMessage(), 500);
}

/**
 * Validate access code for assessment
 */
function validateAccessCode($db, $input) {
    $accessCode = sanitizeInput($input['access_code'] ?? '');
    
    if (empty($accessCode)) {
        sendError('Access code is required');
    }

    try {
        $stmt = $db->prepare("
            SELECT id, code, is_used, used_by, expires_at 
            FROM access_codes 
            WHERE code = ? AND (expires_at IS NULL OR expires_at > NOW())
        ");
        $stmt->execute([$accessCode]);
        $code = $stmt->fetch();

        if (!$code) {
            sendError('Invalid or expired access code');
        }

        if ($code['is_used']) {
            sendError('Access code has already been used');
        }

        sendSuccess([
            'valid' => true,
            'code' => $code['code'],
            'message' => 'Access code is valid'
        ]);

    } catch (PDOException $e) {
        sendError('Database error');
    }
}

/**
 * Admin login
 */
function adminLogin($db, $input) {
    $username = sanitizeInput($input['username'] ?? '');
    $password = $input['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        sendError('Username and password are required');
    }

    try {
        $stmt = $db->prepare("
            SELECT id, username, password_hash, email, is_active 
            FROM admin_users 
            WHERE username = ? AND is_active = 1
        ");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            sendError('Invalid credentials');
        }

        // Update last login
        $updateStmt = $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
        $updateStmt->execute([$admin['id']]);

        // Set session variables
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_login_time'] = time();

        sendSuccess([
            'admin_id' => $admin['id'],
            'username' => $admin['username'],
            'message' => 'Login successful'
        ]);

    } catch (PDOException $e) {
        sendError('Database error');
    }
}

/**
 * Change admin password
 */
function changePassword($db, $input) {
    // Check if admin is logged in
    if (!isset($_SESSION['admin_id'])) {
        sendError('Authentication required', 401);
    }

    $currentPassword = $input['current_password'] ?? '';
    $newPassword = $input['new_password'] ?? '';
    $confirmPassword = $input['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        sendError('All password fields are required');
    }

    if ($newPassword !== $confirmPassword) {
        sendError('New passwords do not match');
    }

    if (strlen($newPassword) < 8) {
        sendError('New password must be at least 8 characters long');
    }

    try {
        // Verify current password
        $stmt = $db->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($currentPassword, $admin['password_hash'])) {
            sendError('Current password is incorrect');
        }

        // Check if new password is different from current password
        if (password_verify($newPassword, $admin['password_hash'])) {
            sendError('New password must be different from current password');
        }

        // Update password
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = $db->prepare("UPDATE admin_users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
        $updateStmt->execute([$newHash, $_SESSION['admin_id']]);

        sendSuccess(null, 'Password changed successfully');

    } catch (PDOException $e) {
        sendError('Database error');
    }
}
?>
