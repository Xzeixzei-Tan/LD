<?php
// unregister.php - Handles event unregistration
require_once 'config.php';

// Start session to access user data
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Get the user ID from session
$user_id = $_SESSION['user_id'];

// Check if event ID is provided in the URL
if (!isset($_GET['event_id'])) {
    // Redirect back to events page if no event ID
    header("Location: user-events.php");
    exit();
}

$event_id = intval($_GET['event_id']);

// Get event details for notification message
$event_title = "";
$event_sql = "SELECT title FROM events WHERE id = ?";
$event_stmt = $conn->prepare($event_sql);
$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();

if ($event_result->num_rows > 0) {
    $event_row = $event_result->fetch_assoc();
    $event_title = $event_row['title'];
}
$event_stmt->close();

// Prepare and execute the deletion query
$sql = "DELETE FROM registered_users WHERE user_id = ? AND event_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $event_id);
$result = $stmt->execute();

if ($result) {
    // Successful unregistration
        echo "<script>alert('You are already registered for this event.'); window.location.href='user-events.php';</script>";
} else {
    // Failed unregistration
    $_SESSION['message'] = "Failed to unregister from this event. Please try again.";
}

// Close the statement and connection
$stmt->close();
$conn->close();

// Redirect back to the events page
header("Location: user-events.php?tab=unregistered");
exit();
?>
