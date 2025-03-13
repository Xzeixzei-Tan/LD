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

// Register the user for the event
$insert_sql = "INSERT INTO registered_users (user_id, event_id, registration_date) VALUES (?, ?, NOW())";
$insert_stmt = $conn->prepare($insert_sql);
$insert_stmt->bind_param("ii", $user_id, $event_id);
$result = $insert_stmt->execute();

//if ($result) {
    
    // Optionally, insert a notification record for the user
    //$notification_message = "You have registered for an event.";
    //$notification_sql = "INSERT INTO notifications (user_id, message, created_at, is_read) VALUES (?, ?, NOW(), 0)";
    
    //if ($conn->prepare($notification_sql)) {
    //    $notification_stmt = $conn->prepare($notification_sql);
    //    $notification_stmt->bind_param("is", $user_id, $notification_message);
    //    $notification_stmt->execute();
    //    $notification_stmt->close();
    //}
//} else {
    // Failed registration
//    echo "<script>alert('Failed to register for this event. Please try again.'); window.location.href='user-events.php';</script>";
//}

// Close the statement and connection
$insert_stmt->close();
$conn->close();

// Redirect back to the events page
header("Location: user-events.php?event_id=" . $event_id . "&tab=registered");
exit();
?>
