<?php
require_once 'config.php';
session_start(); // Add session start

// Check if user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No user ID provided']);
    exit;
}

$userId = intval($_GET['id']);
$isSelfDelete = false;

// Check if the user is deleting their own account
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
    $isSelfDelete = true;
}

// Start transaction
$conn->begin_transaction();

try {
    // 1. First delete event registrations
    $deleteRegistrationsSql = "DELETE FROM registered_users WHERE user_id = ?";
    $stmtRegistrations = $conn->prepare($deleteRegistrationsSql);
    $stmtRegistrations->bind_param("i", $userId);
    $stmtRegistrations->execute();
    
    // 2. Then delete related records in users_lnd table
    $deleteLndSql = "DELETE FROM users_lnd WHERE user_id = ?";
    $stmtLnd = $conn->prepare($deleteLndSql);
    $stmtLnd->bind_param("i", $userId);
    $stmtLnd->execute();
    
    // 3. Hard delete the user from the users table
    $deleteUserSql = "DELETE FROM users WHERE id = ?";
    $stmtUser = $conn->prepare($deleteUserSql);
    $stmtUser->bind_param("i", $userId);
    $stmtUser->execute();
    
    // Commit transaction
    $conn->commit();
    
    // If self-delete, destroy the session
    if ($isSelfDelete) {
        session_destroy();
    }
    
    echo json_encode(['success' => true, 'selfDelete' => $isSelfDelete]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>