<?php
require_once 'config.php';

// Initialize response array
$response = array(
    'status' => 'error',
    'message' => '',
    'data' => array()
);

// Check if search term is provided
if (!isset($_GET['term'])) {
    $response['message'] = 'Search term is required';
    echo json_encode($response);
    exit;
}

$searchTerm = '%' . $_GET['term'] . '%';

// Base SQL query
$sql = "SELECT u.id, u.first_name, u.middle_name, u.last_name, u.suffix, u.sex, 
        u.contact_no, u.email, c.name as classification_name, cp.name as position_name 
        FROM users u 
        LEFT JOIN users_lnd ul ON u.id = ul.user_id
        LEFT JOIN class_position cp ON ul.position_id = cp.id 
        LEFT JOIN classification c ON ul.classification_id = c.id 
        WHERE u.deleted_at IS NULL 
        AND (
            u.first_name LIKE ? OR 
            u.last_name LIKE ? OR 
            u.email LIKE ? OR 
            u.contact_no LIKE ? OR
            c.name LIKE ? OR
            cp.name LIKE ?
        )";

// Add classification filter if provided
if (isset($_GET['classification']) && !empty($_GET['classification'])) {
    $sql .= " AND c.name = ?";
}

// Add position filter if provided
if (isset($_GET['position']) && !empty($_GET['position'])) {
    $sql .= " AND cp.name = ?";
}

$sql .= " ORDER BY u.id";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    $response['message'] = 'Error preparing statement: ' . $conn->error;
    echo json_encode($response);
    exit;
}

// Create parameter types and array
$paramTypes = "ssssss"; // 6 initial s parameters for search term
$paramValues = array($searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);

// Add classification parameter if needed
if (isset($_GET['classification']) && !empty($_GET['classification'])) {
    $paramTypes .= "s";
    $paramValues[] = $_GET['classification'];
}

// Add position parameter if needed
if (isset($_GET['position']) && !empty($_GET['position'])) {
    $paramTypes .= "s";
    $paramValues[] = $_GET['position'];
}

// Create a reference array for bind_param
$paramRefs = array();
$paramRefs[] = &$paramTypes;
foreach ($paramValues as $key => $value) {
    $paramRefs[] = &$paramValues[$key];
}

// Call bind_param with references
call_user_func_array(array($stmt, 'bind_param'), $paramRefs);

// Execute the statement
if (!$stmt->execute()) {
    $response['message'] = 'Error executing statement: ' . $stmt->error;
    echo json_encode($response);
    exit;
}

// Get results
$result = $stmt->get_result();
$users = array();

if ($result->num_rows > 0) {
    $count = 1;
    while ($row = $result->fetch_assoc()) {
        // Format name with middle initial and suffix
        $middle_initial = !empty($row["middle_name"]) ? " " . substr($row["middle_name"], 0, 1) . "." : "";
        $suffix = !empty($row["suffix"]) ? " " . $row["suffix"] : "";
        $full_name = $row["first_name"] . $middle_initial . " " . $row["last_name"] . $suffix;
        
        $users[] = array(
            'id' => $row['id'],
            'index' => $count,
            'name' => $full_name,
            'sex' => $row['sex'],
            'contact_no' => $row['contact_no'],
            'classification' => $row['classification_name'] ?? 'Not Assigned',
            'position' => $row['position_name'] ?? 'Not Assigned',
            'email' => $row['email']
        );
        $count++;
    }
    
    $response['status'] = 'success';
    $response['data'] = $users;
} else {
    $response['status'] = 'success';
    $response['message'] = 'No users found matching your search criteria';
}

// Close statement
$stmt->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>