<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    if (isset($_GET['json']) && $_GET['json'] == 'true') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    } else {
        header("Location: login.php");
        exit();
    }
}

if (isset($_GET['notification_id'])) {
    $notification_id = intval($_GET['notification_id']);
    
    // Update notification to mark as read
    $update_sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $notification_id);
    $success = $stmt->execute();
    
    if ($success) {
        if (isset($_GET['json']) && $_GET['json'] == 'true') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        } elseif (isset($_GET['redirect'])) {
            header("Location: " . $_GET['redirect']);
            exit();
        } else {
            header("Location: user-notif.php");
            exit();
        }
    } else {
        if (isset($_GET['json']) && $_GET['json'] == 'true') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit;
        } else {
            $_SESSION['message'] = "Error updating notification.";
            header("Location: user-notif.php");
            exit();
        }
    }
} else {
    if (isset($_GET['json']) && $_GET['json'] == 'true') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No notification ID provided']);
        exit;
    } else {
        $_SESSION['message'] = "No notification ID provided.";
        header("Location: user-notif.php");
        exit();
    }
}
?>