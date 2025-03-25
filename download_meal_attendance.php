<?php
require_once 'config.php';

// Check if event ID is provided
if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    die("Error: No event ID provided");
}

$event_id = intval($_GET['event_id']);

// Get event information including multiple dates
$event_sql = "SELECT title, start_date, end_date FROM events WHERE id = ?";
$event_stmt = $conn->prepare($event_sql);
$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();

if ($event_result->num_rows === 0) {
    die("Error: Event not found");
}

$event_row = $event_result->fetch_assoc();
$event_title = $event_row['title'];
$start_date = new DateTime($event_row['start_date']);
$end_date = new DateTime($event_row['end_date']);

// Set headers for CSV download
$filename = "Meal-Attendance_" . preg_replace('/[^a-z0-9]+/', '_', strtolower($event_title)) . "_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for proper Excel UTF-8 handling
fprintf($output, "\xEF\xBB\xBF");

// Get participant information
$sql = "SELECT u.id, u.first_name, u.middle_name, u.last_name, u.suffix, 
        u.sex, u.contact_no
        FROM registered_users ru
        JOIN users u ON ru.user_id = u.id
        WHERE ru.event_id = ? AND u.deleted_at IS NULL
        ORDER BY u.last_name, u.first_name";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

// Calculate number of days
$interval = $start_date->diff($end_date);
$total_days = $interval->days + 1; // Add 1 to include start date

// Write multi-day event header
$event_header = ['EVENT: ' . $event_title];
fputcsv($output, $event_header);
fputcsv($output, []); // Add empty row for spacing

// Prepare days row
$days_row = ['', '', '']; // Align with NO., NAME, SEX columns
$meal_types = ['Breakfast', 'AM Snack', 'Lunch', 'PM Snack'];

// Generate days header
for ($day = 1; $day <= $total_days; $day++) {
    $current_date = clone $start_date;
    $current_date->modify('+' . ($day - 1) . ' days');
    
    // Only add day header for the first meal type
    $days_row[] = 'Day ' . $day . ' (' . $current_date->format('M d, Y') . ')';
    $days_row[] = ''; // AM Snack
    $days_row[] = ''; // Lunch
    $days_row[] = ''; // PM Snack
}

// Prepare header row
$header = ['NO.', 'NAME', 'SEX'];

// Generate header with meal types
for ($day = 1; $day <= $total_days; $day++) {
    $header = array_merge($header, $meal_types);
}

// Write the days row
fputcsv($output, $days_row);

// Write the header row
fputcsv($output, $header);

// Write data rows
if ($result->num_rows > 0) {
    $count = 1;
    while ($row = $result->fetch_assoc()) {
        // Format name with middle initial and suffix
        $middle_initial = !empty($row["middle_name"]) ? " " . substr($row["middle_name"], 0, 1) . "." : "";
        $suffix = !empty($row["suffix"]) ? " " . $row["suffix"] : "";
        $full_name = $row["first_name"] . $middle_initial . " " . $row["last_name"] . $suffix;
        
        // Create CSV row
        $csv_row = [
            $count,
            $full_name,
            $row["sex"]
        ];
        
        // Add empty signature columns for each day's meals
        for ($day = 1; $day <= $total_days; $day++) {
            $csv_row = array_merge($csv_row, [
                '', // Breakfast signature
                '', // AM Snack signature
                '', // Lunch signature
                ''  // PM Snack signature
            ]);
        }
        
        fputcsv($output, $csv_row);
        $count++;
    }
} else {
    // Add a row indicating no participants
    $empty_row = ['No registered participants found'];
    fputcsv($output, $empty_row);
}

// Close the file handle
fclose($output);
$conn->close();
exit;
?>