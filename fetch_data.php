<?php
require_once 'config.php'; // Ensure database connection is included

function getEnumValues($conn, $table, $column) {
    $options = [];
    $query = "SHOW COLUMNS FROM `$table` LIKE '$column'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        error_log("Error fetching ENUM values: " . mysqli_error($conn));
        return [];
    }

    if ($row = mysqli_fetch_assoc($result)) {
        if (preg_match("/^enum\((.*)\)$/", $row['Type'], $matches)) {
            $options = str_getcsv($matches[1], ",", "'");
        }
    }
    
    return $options;
}

// Fetch ENUM values safely
$specificationOptions = getEnumValues($conn, 'events', 'specification');
$deliveryOptions = getEnumValues($conn, 'events', 'delivery');
$fundingOptions = getEnumValues($conn, 'funding_sources', 'source');
$schoolLevelOptions = getEnumValues($conn, 'school_participants', 'school_level');
$typeOptions = getEnumValues($conn, 'school_participants', 'type');
$specializationOptions = getEnumValues($conn, 'school_participants', 'specialization');
$departmentOptions = getEnumValues($conn, 'division_participants', 'department_name');

// Fetch Target Personnel (Dynamic values)
$targetPersonnelOptions = [];
$query = "SELECT DISTINCT target FROM eligible_participants";
$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $targetPersonnelOptions[] = $row['target'];
    }
} else {
    error_log("Error fetching target personnel: " . mysqli_error($conn));
}
?>
