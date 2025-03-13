<?php
require_once 'config.php'; // Include database connection

$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = $_POST['title'];
    $specification = $_POST['specification'];
    $delivery = $_POST['delivery'];
    $venue = isset($_POST['venue']) ? $_POST['venue'] : '';
    $startDate = $_POST['start-date'];
    $endDate = $_POST['end-date'];
    $proponent = $_POST['proponent'];
    $createdAt = date('Y-m-d H:i:s');
    $updatedAt = date('Y-m-d H:i:s');

    // Insert into events table
    $stmt = $conn->prepare("INSERT INTO events (title, specification, delivery, venue, start_date, end_date, proponent, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $title, $specification, $delivery, $venue, $startDate, $endDate, $proponent, $createdAt, $updatedAt);
    $stmt->execute();
    $eventId = $stmt->insert_id;
    $stmt->close();

    // Insert funding sources
    if (isset($_POST['funding_source'])) {
        foreach ($_POST['funding_source'] as $source) {
            $amount = $_POST[$source . '_amount'];
            $stmt = $conn->prepare("INSERT INTO funding_sources (event_id, source, amount) VALUES (?, ?, ?)");
            $stmt->bind_param("isd", $eventId, $source, $amount);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Insert meal plans
    foreach ($_POST as $key => $value) {
        if (preg_match('/^meal-(.+)-day-(\d+)$/', $key, $matches)) {
            $mealType = $matches[1];
            $day = $matches[2];
            $stmt = $conn->prepare("INSERT INTO meal_plan (event_id, day, meal_type) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $eventId, $day, $mealType);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Insert speakers
    if (isset($_POST['speaker']) && is_array($_POST['speaker'])) {
        foreach ($_POST['speaker'] as $speaker) {
            if (!empty($speaker)) {
                $stmt = $conn->prepare("INSERT INTO speakers (event_id, name, created_at, updated_at) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $eventId, $speaker, $createdAt, $updatedAt);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // Success message and redirect
    $successMessage = "Event created successfully!";
    header("Location: admin-events.php?success=1");
    exit();
}
?>
