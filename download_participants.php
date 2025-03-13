<?php
require_once 'config.php';

// Check if event ID is provided
if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    die("Error: No event ID provided");
}

$event_id = intval($_GET['event_id']);

// Get event information
$event_sql = "SELECT title FROM events WHERE id = ?";
$event_stmt = $conn->prepare($event_sql);
$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();

if ($event_result->num_rows === 0) {
    die("Error: Event not found");
}

$event_row = $event_result->fetch_assoc();
$event_title = $event_row['title'];

// Get participant information by joining registered_users with users table
$sql = "SELECT u.id, u.first_name, u.middle_name, u.last_name, u.suffix, 
        u.sex, u.contact_no, u.email, c.name as classification_name, 
        cp.name as position_name 
        FROM registered_users ru
        JOIN users u ON ru.user_id = u.id
        LEFT JOIN users_lnd ul ON u.id = ul.user_id
        LEFT JOIN class_position cp ON ul.position_id = cp.id 
        LEFT JOIN classification c ON ul.classification_id = c.id 
        WHERE ru.event_id = ? AND u.deleted_at IS NULL
        ORDER BY u.last_name, u.first_name";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

// Set headers for CSV download
$filename = "participants_" . preg_replace('/[^a-z0-9]+/', '_', strtolower($event_title)) . "_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for proper Excel UTF-8 handling
fprintf($output, "\xEF\xBB\xBF");

// Set header row
fputcsv($output, [
    'No.',
    'Name',
    'Sex',
    'Contact Number',
    'Email',
    'School/Division Assignment',
    'Position',
    'Signature' // Added new column for signature
]);

// Write data rows
if ($result->num_rows > 0) {
    $count = 1;
    while ($row = $result->fetch_assoc()) {
        // Format name with middle initial and suffix
        $middle_initial = !empty($row["middle_name"]) ? " " . substr($row["middle_name"], 0, 1) . "." : "";
        $suffix = !empty($row["suffix"]) ? " " . $row["suffix"] : "";
        $full_name = $row["first_name"] . $middle_initial . " " . $row["last_name"] . $suffix;

        fputcsv($output, [
            $count,
            $full_name,
            $row["sex"],
            $row["contact_no"],
            $row["email"],
            $row["classification_name"] ?? "Not Assigned",
            $row["position_name"] ?? "Not Assigned",
            '' // Empty string for signature field
        ]);
        $count++;
    }
} else {
    // Add a row indicating no participants
    fputcsv($output, ['No registered participants found']);
}

// Close the file handle
fclose($output);
$conn->close();
exit;
?>