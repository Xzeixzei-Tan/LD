<?php
require_once 'config.php';

// Check if the user is logged in and has admin privileges
// Add your authentication check here

// Get the event ID and action
$eventId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($eventId > 0 && ($action === 'archive' || $action === 'unarchive')) {
    // Set the archived value based on the action
    $archived = ($action === 'archive') ? 1 : 0;
    
    // Update the event's archived status
    $stmt = $conn->prepare("UPDATE events SET archived = ? WHERE id = ?");
    $stmt->bind_param("ii", $archived, $eventId);
    
    if ($stmt->execute()) {
        // Success
        $message = ($action === 'archive') ? "Event archived successfully." : "Event unarchived successfully.";
    } else {
        // Error
        $message = "Error updating event: " . $conn->error;
    }
    
    $stmt->close();
} else {
    $message = "Invalid request.";
}

// Redirect back to the events page with a message
$redirectUrl = ($action === 'unarchive') ? 'admin-events.php?view=archived' : 'admin-events.php';
header("Location: $redirectUrl?message=" . urlencode($message));
exit;
?>