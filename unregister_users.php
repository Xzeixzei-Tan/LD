<?php
// Include database configuration
require_once 'config.php';

// For debugging
error_log("unregister_users.php called with data: " . file_get_contents('php://input'));

// Get JSON data from request body
$data = json_decode(file_get_contents('php://input'), true);

// Check if registrationIds are provided
if (!isset($data['registrationIds']) || empty($data['registrationIds']) || !is_array($data['registrationIds'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Registration IDs are required']);
    exit;
}

$registrationIds = array_map('intval', $data['registrationIds']);

// Prepare placeholders for IN clause
$placeholders = implode(',', array_fill(0, count($registrationIds), '?'));

// Create SQL delete statement
$sql = "DELETE FROM registered_users WHERE id IN ($placeholders)";

// Prepare statement
$stmt = $conn->prepare($sql);

// Bind parameters dynamically
$types = str_repeat('i', count($registrationIds));
$stmt->bind_param($types, ...$registrationIds);

// Execute the query
$success = $stmt->execute();

// Check if the operation was successful
if ($success) {
    $affectedRows = $stmt->affected_rows;
    error_log("Successfully unregistered $affectedRows users");
    
    // Close the statement
    $stmt->close();
    
    // Set the response content type to JSON
    header('Content-Type: application/json');
    
    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => "Successfully unregistered $affectedRows user(s)",
        'affected_rows' => $affectedRows
    ]);
} else {
    // Log the error
    error_log("Error unregistering users: " . $conn->error);
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => "Database error: " . $conn->error
    ]);
}

// Close the database connection
$conn->close();
?>