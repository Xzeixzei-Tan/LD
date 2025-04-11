<?php
require_once 'config.php';
session_start();

// Get JSON data from the request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Check if user IDs are provided
if (!isset($data['userIds']) || empty($data['userIds']) || !is_array($data['userIds'])) {
    echo json_encode(['success' => false, 'message' => 'No valid user IDs provided']);
    exit;
}

// Convert all user IDs to integers
$userIds = array_map('intval', $data['userIds']);

// Start transaction
$conn->begin_transaction();

try {
    // Initialize statements
    $deleteRegistrationsSql = "DELETE FROM registered_users WHERE user_id = ?";
    $stmtRegistrations = $conn->prepare($deleteRegistrationsSql);
    
    $deleteLndSql = "DELETE FROM users_lnd WHERE user_id = ?";
    $stmtLnd = $conn->prepare($deleteLndSql);
    
    // Prepare statement for hard deleting users
    $deleteUserSql = "DELETE FROM users WHERE id = ?";
    $stmtUser = $conn->prepare($deleteUserSql);
    
    $successCount = 0;
    
    // Process each user ID
    foreach ($userIds as $userId) {
        // 1. First delete from registered_users
        $stmtRegistrations->bind_param("i", $userId);
        $stmtRegistrations->execute();
        
        // 2. Then delete from users_lnd
        $stmtLnd->bind_param("i", $userId);
        $stmtLnd->execute();
        
        // 3. Hard delete the user from the users table
        $stmtUser->bind_param("i", $userId);
        $stmtUser->execute();
        
        $successCount++;
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => "$successCount users have been deleted successfully.",
        'deletedUserIds' => $userIds
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>