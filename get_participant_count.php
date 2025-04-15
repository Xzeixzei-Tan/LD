<?php
require_once 'config.php';
session_start();

// Set headers for JSON response
header('Content-Type: application/json');

// Get event ID from URL
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

if (!$event_id) {
    echo json_encode(['status' => 'error', 'message' => 'No event specified']);
    exit;
}

// Check database connection
try {
    if (!$conn->ping()) {
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        if ($conn->connect_error) {
            echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
            exit;
        }
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection error']);
    exit;
}

// Get total number of participants
$countSQL = "SELECT COUNT(DISTINCT user_id) as total 
             FROM registered_users ru
             JOIN users u ON ru.user_id = u.id
             WHERE ru.event_id = ?";

$stmt = $conn->prepare($countSQL);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$totalResult = $stmt->get_result();
$totalRow = $totalResult->fetch_assoc();
$total_participants = $totalRow['total'];

// Return the count
echo json_encode([
    'status' => 'success',
    'count' => $total_participants
]);
?>