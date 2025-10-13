<?php
/**
 * Admin API Endpoints
 * DISC Assessment Platform
 */

require_once '../config/config.php';
require_once '../config/database.php';

setCorsHeaders();

// Ensure session is started for authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check admin authentication
function checkAdminAuth() {
    if (!isset($_SESSION['admin_id'])) {
        // Debug information for troubleshooting
        $debug_info = [
            'session_status' => session_status(),
            'session_id' => session_id(),
            'session_data' => $_SESSION,
            'message' => 'No admin session found. Please log in first.'
        ];
        
        if (DEBUG_MODE) {
            sendError('Authentication required. Debug: ' . json_encode($debug_info), 401);
        } else {
            sendError('Authentication required', 401);
        }
    }
}

try {
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);

    switch ($method) {
        case 'GET':
            checkAdminAuth();
            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'dashboard_stats':
                        getDashboardStats($db);
                        break;
                    case 'participants':
                        getParticipants($db);
                        break;
                    case 'access_codes':
                        getAccessCodes($db);
                        break;
                    case 'settings':
                        getSettings($db);
                        break;
                    default:
                        sendError('Invalid action');
                }
            } else {
                sendError('Action required');
            }
            break;
        case 'POST':
            checkAdminAuth();
            if (isset($input['action'])) {
                switch ($input['action']) {
                    case 'generate_codes':
                        generateAccessCodes($db, $input);
                        break;
                    case 'update_settings':
                        updateSettings($db, $input);
                        break;
                    case 'delete_participant':
                        deleteParticipant($db, $input);
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
 * Get dashboard statistics
 */
function getDashboardStats($db) {
    try {
        // Total participants
        $stmt = $db->query("SELECT COUNT(*) as total FROM participants");
        $totalParticipants = $stmt->fetch()['total'];

        // Completed assessments
        $stmt = $db->query("SELECT COUNT(*) as completed FROM participants WHERE assessment_completed = TRUE");
        $completedAssessments = $stmt->fetch()['completed'];

        // Pending assessments
        $stmt = $db->query("SELECT COUNT(*) as pending FROM participants WHERE assessment_completed = FALSE AND started_at IS NOT NULL");
        $pendingAssessments = $stmt->fetch()['pending'];

        // Total access codes
        $stmt = $db->query("SELECT COUNT(*) as total FROM access_codes");
        $totalCodes = $stmt->fetch()['total'];

        // Used access codes
        $stmt = $db->query("SELECT COUNT(*) as used FROM access_codes WHERE is_used = TRUE");
        $usedCodes = $stmt->fetch()['used'];

        sendSuccess([
            'total_participants' => $totalParticipants,
            'completed_assessments' => $completedAssessments,
            'pending_assessments' => $pendingAssessments,
            'total_codes' => $totalCodes,
            'used_codes' => $usedCodes,
            'available_codes' => $totalCodes - $usedCodes
        ]);

    } catch (PDOException $e) {
        sendError('Database error');
    }
}

/**
 * Get all participants with their results
 */
function getParticipants($db) {
    try {
        $stmt = $db->query("
            SELECT 
                p.id,
                p.full_name,
                p.position,
                p.email,
                p.access_code,
                p.started_at,
                p.completed_at,
                p.assessment_completed,
                dp.primary_type,
                dp.secondary_type,
                dp.profile_title,
                dp.dominance_score,
                dp.influence_score,
                dp.steadiness_score,
                dp.compliance_score
            FROM participants p
            LEFT JOIN disc_profiles dp ON p.id = dp.participant_id
            ORDER BY p.created_at DESC
        ");
        
        $participants = $stmt->fetchAll();
        
        sendSuccess(['participants' => $participants]);

    } catch (PDOException $e) {
        sendError('Database error');
    }
}

/**
 * Get access codes
 */
function getAccessCodes($db) {
    try {
        $stmt = $db->query("
            SELECT 
                id,
                code,
                is_used,
                used_by,
                used_at,
                created_at,
                expires_at
            FROM access_codes
            ORDER BY created_at DESC
        ");
        
        $codes = $stmt->fetchAll();
        
        sendSuccess(['access_codes' => $codes]);

    } catch (PDOException $e) {
        sendError('Database error');
    }
}

/**
 * Generate new access codes
 */
function generateAccessCodes($db, $input) {
    $count = intval($input['count'] ?? 20);
    
    if ($count < 1 || $count > 100) {
        sendError('Count must be between 1 and 100');
    }

    try {
        $db->beginTransaction();
        
        $codes = [];
        $stmt = $db->prepare("INSERT INTO access_codes (code, created_by) VALUES (?, ?)");
        
        for ($i = 0; $i < $count; $i++) {
            $code = generateRandomCode();
            $stmt->execute([$code, $_SESSION['admin_id']]);
            $codes[] = $code;
        }
        
        $db->commit();
        
        sendSuccess([
            'generated_codes' => $codes,
            'count' => $count,
            'message' => "Successfully generated {$count} access codes"
        ]);

    } catch (PDOException $e) {
        $db->rollBack();
        sendError('Database error');
    }
}

/**
 * Update admin settings
 */
function updateSettings($db, $input) {
    $settings = $input['settings'] ?? [];
    
    if (empty($settings)) {
        sendError('Settings data required');
    }

    try {
        $db->beginTransaction();
        
        $stmt = $db->prepare("
            INSERT INTO admin_settings (setting_key, setting_value, updated_by) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            setting_value = VALUES(setting_value), 
            updated_at = NOW(), 
            updated_by = VALUES(updated_by)
        ");
        
        foreach ($settings as $key => $value) {
            $stmt->execute([$key, $value, $_SESSION['admin_id']]);
        }
        
        $db->commit();
        
        sendSuccess(null, 'Settings updated successfully');

    } catch (PDOException $e) {
        $db->rollBack();
        sendError('Database error');
    }
}

/**
 * Get admin settings
 */
function getSettings($db) {
    try {
        $stmt = $db->query("SELECT setting_key, setting_value FROM admin_settings");
        $settings = [];
        
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        sendSuccess(['settings' => $settings]);

    } catch (PDOException $e) {
        sendError('Database error');
    }
}

/**
 * Delete participant
 */
function deleteParticipant($db, $input) {
    $participantId = intval($input['participant_id'] ?? 0);
    
    if ($participantId <= 0) {
        sendError('Valid participant ID required');
    }

    try {
        $db->beginTransaction();
        
        // Get participant info for access code cleanup
        $stmt = $db->prepare("SELECT access_code FROM participants WHERE id = ?");
        $stmt->execute([$participantId]);
        $participant = $stmt->fetch();
        
        if (!$participant) {
            sendError('Participant not found');
        }
        
        // Delete participant (cascade will handle related records)
        $stmt = $db->prepare("DELETE FROM participants WHERE id = ?");
        $stmt->execute([$participantId]);
        
        // Reset access code to unused
        $stmt = $db->prepare("
            UPDATE access_codes 
            SET is_used = FALSE, used_by = NULL, used_at = NULL 
            WHERE code = ?
        ");
        $stmt->execute([$participant['access_code']]);
        
        $db->commit();
        
        sendSuccess(null, 'Participant deleted successfully');

    } catch (PDOException $e) {
        $db->rollBack();
        sendError('Database error');
    }
}

/**
 * Generate random access code
 */
function generateRandomCode() {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    
    for ($i = 0; $i < ACCESS_CODE_LENGTH; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    
    return $code;
}
?>
