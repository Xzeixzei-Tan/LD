<?php
require_once 'config.php';

// Get JSON data from the request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Check if user IDs are provided
if (!isset($data['userIds']) || empty($data['userIds']) || !is_array($data['userIds'])) {
    echo json_encode(['success' => false, 'message' => 'No valid user IDs provided']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // First delete related records in users_lnd table
    $deleteLndSql = "DELETE FROM users_lnd WHERE user_id = ?";
    $stmtLnd = $conn->prepare($deleteLndSql);
    
    $successCount = 0;
    
    // Process each user ID
    foreach ($data['userIds'] as $userId) {
        $userId = intval($userId);
        
        // Delete from users_lnd
        $stmtLnd->bind_param("i", $userId);
        $stmtLnd->execute();
        
        // Delete from users (soft delete)
        //$stmtUser->bind_param("i", $userId);
        //$stmtUser->execute();
        
        $successCount++;
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => "$successCount users have been deleted successfully."
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>