<?php
// session_manager.php - Combined session validation and management

// Start session and include config
require_once 'config.php';
session_start();

/**
 * Validates if the current user session is valid
 * Checks if the user still exists in the database
 * Redirects to signup if account was deleted
 */
function validateUserSession() {
    global $conn;
    
    // Check if user is not logged in
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Check if the user still exists in the database
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // User no longer exists - handle deletion
        handleAccountDeletion();
        return false;
    }
    
    return true;
}

/**
 * Handles account deletion actions
 * Clears session, sets cookie and redirects
 */
function handleAccountDeletion() {
    // Clear the session
    session_unset();
    session_destroy();
    
    // Set a cookie to indicate account deletion
    setcookie("account_deleted", "true", time() + 100, "/");
    
    // Redirect to signup with the deleted parameter
    header("Location: signup.php?account_deleted=true");
    exit();
}

/**
 * AJAX endpoint for checking session status
 * Returns JSON response for client-side validation
 */
function checkSessionStatusAjax() {
    // Default response
    $response = ['status' => 'valid'];
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $response['status'] = 'invalid';
        echo json_encode($response);
        exit();
    }
    
    // Check if the user still exists in the database
    global $conn;
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // User no longer exists in the database
        // Clear the session
        session_unset();
        session_destroy();
        
        $response['status'] = 'deleted';
    }
    
    echo json_encode($response);
    exit();
}

// If this file is accessed directly as an AJAX endpoint
if (basename($_SERVER['SCRIPT_NAME']) == 'session_manager.php' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] == 'check') {
        checkSessionStatusAjax();
    }
}
?>