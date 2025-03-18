<?php
require_once 'config.php';
session_start();

// Check if notification ID is provided
if (isset($_GET['notification_id'])) {
    $notification_id = $_GET['notification_id'];
    
    // Update the notification status to read
    $update_sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $notification_id);
    
    if ($stmt->execute()) {
        // Redirect to the appropriate page based on notification type
        if (isset($_GET['redirect'])) {
            header("Location: " . $_GET['redirect']);
            exit;
        }
    }
}

// If we get here, something went wrong or no ID was provided
// Redirect back to the dashboard
header("Location: user-dashboard.php");
exit;
?>
