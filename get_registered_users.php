<?php
// Include database configuration
require_once 'config.php';

// Check if event_id is provided
if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Event ID is required']);
    exit;
}

$eventId = intval($_GET['event_id']);

// Query to get registered users for the event
$sql = "SELECT 
            ru.id AS registration_id, 
            ru.registration_date,
            CONCAT(u.first_name, ' ', 
                CASE WHEN u.middle_name IS NOT NULL AND u.middle_name != '' THEN CONCAT(u.middle_name, ' ') ELSE '' END,
                u.last_name,
                CASE WHEN u.suffix IS NOT NULL AND u.suffix != '' THEN CONCAT(' ', u.suffix) ELSE '' END
            ) AS name,
            u.email,
            u.contact_no AS phone,
            cp.name AS position,
            cp.classification_id
        FROM registered_users ru
        JOIN users u ON ru.user_id = u.id
        LEFT JOIN users_lnd ul ON ru.user_id = ul.user_id
        LEFT JOIN class_position cp ON ul.position_id = cp.id
        WHERE ru.event_id = ?
        ORDER BY ru.registration_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$result = $stmt->get_result();

$users = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Create designation from position and classification if available
        $designation = '';
        if (!empty($row['position'])) {
            $designation = $row['position'];
            if (!empty($row['classification'])) {
                $designation .= ' (' . $row['classification'] . ')';
            }
        }
        
        $users[] = [
            'id' => $row['registration_id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'designation' => $designation,
            'registration_date' => $row['registration_date']
        ];
    }
}

// Close the statement
$stmt->close();

// Set the response content type to JSON
header('Content-Type: application/json');

// Output the JSON data
echo json_encode($users);

// Close the database connection
$conn->close();
?>
