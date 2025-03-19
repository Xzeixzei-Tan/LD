<?php
// register.php - Handles event registration
require_once 'config.php';
// Start session to access user data
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
// Check if event ID is provided in the URL
if (!isset($_GET['event_id'])) {
    // Redirect back to events page if no event ID
    header("Location: user-events.php");
    exit();
}
$event_id = intval($_GET['event_id']);
// Check if the user is already registered for this event
$check_sql = "SELECT * FROM registered_users WHERE user_id = ? AND event_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $user_id, $event_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
if ($check_result->num_rows > 0) {
    // User is already registered
    echo "<script>alert('You are already registered for this event.'); window.location.href='user-events.php';</script>";
    $check_stmt->close();
    
    // Redirect back to the events page
    header("Location: user-events.php?event_id=" . $event_id . "&tab=registered");
    exit();
}
$check_stmt->close();

// Get user and event details for the notification message
$user_query = "SELECT first_name FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);

if (!$user_stmt) {
    die("User Query Prepare failed: " . $conn->error);
}

$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$first_name = $user_data['first_name'];
$user_stmt->close();

$event_query = "SELECT title FROM events WHERE id = ?";
$event_stmt = $conn->prepare($event_query);

if (!$event_stmt) {
    die("Event Query Prepare failed: " . $conn->error);
}

$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();
$event_data = $event_result->fetch_assoc();
$title = $event_data['title'];
$event_stmt->close();

// Register the user for the event
$insert_sql = "INSERT INTO registered_users (user_id, event_id, registration_date) VALUES (?, ?, NOW())";
$insert_stmt = $conn->prepare($insert_sql);
$insert_stmt->bind_param("ii", $user_id, $event_id);
$result = $insert_stmt->execute();

if ($result) {
    // Create notification for the user
    $admin_notification_message = "User " . $first_name . " has registered for event: " . $title;
    $admin_notification_sql = "INSERT INTO notifications (user_id, message, created_at, is_read, notification_type, notification_subtype,event_id) VALUES (?, ?, NOW(), 0, 'admin', 'admin_event_registration' , ?)";
    $admin_notification_stmt = $conn->prepare($admin_notification_sql);
    $admin_notification_stmt->bind_param("isi", $user_id, $admin_notification_message, $event_id);
    $admin_notification_stmt->execute();
    $admin_notification_stmt->close();
    
    // Create notification for the admin
    // Since your admin doesn't have an account, we'll use NULL for user_id and set admin_id to NULL as well
    // but mark the notification_type as 'admin' so it shows up in the admin interface
    // Create notification for the user
    // Create notification for the user
    $user_notification_message = "You have successfully registered for event: " . $title;
    $user_notification_sql = "INSERT INTO notifications (user_id, message, created_at, is_read, notification_type, notification_subtype) VALUES (?, ?, NOW(), 0, 'user', 'event_registration')";
    $user_notification_stmt = $conn->prepare($user_notification_sql);
    $user_notification_stmt->bind_param("is", $user_id, $user_notification_message);
    $user_notification_stmt->execute();
    $user_notification_stmt->close();
} else {
    // Failed registration
    echo "<script>alert('Failed to register for this event. Please try again.'); window.location.href='user-events.php';</script>";
}

// Close the statement and connection
$insert_stmt->close();
$conn->close();
// Redirect back to the events page
header("Location: user-events.php?event_id=" . $event_id . "&tab=registered");
exit();
?>