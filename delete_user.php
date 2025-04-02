<?php
require_once 'config.php';

// Check if user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No user ID provided']);
    exit;
}

$userId = intval($_GET['id']);

// Start transaction
$conn->begin_transaction();

try {
    // First delete related records in users_lnd table
    $deleteLndSql = "DELETE FROM users_lnd WHERE user_id = ?";
    $stmtLnd = $conn->prepare($deleteLndSql);
    $stmtLnd->bind_param("i", $userId);
    $stmtLnd->execute();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>