<?php
require_once 'config.php';  // Include the database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $id = $_POST['id'] ?? null;
    $event_title = $_POST['title'];
    $event_specification = $_POST['specification'];
    $event_delivery = $_POST['delivery'];
    $event_venue = $_POST['venue'] ?? null;
    $funding_sources = $_POST['funding_source'];
    $mooe_amount = $_POST['mooe_amount'] ?? null;
    $sef_amount = $_POST['sef_amount'] ?? null;
    $psf_amount = $_POST['psf_amount'] ?? null;
    $other_specify = $_POST['other_specify'] ?? null;
    $other_amount = $_POST['other_amount'] ?? null;
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $proponents = $_POST['proponent'];
    $speakers = $_POST['speaker'];
    $levels = $_POST['level'] ?? [];
    $participants = $_POST['type'] ?? [];
    $specializations = $_POST['specialization'] ?? [];
    $sectors = $_POST['sector'] ?? [];
    $division_positions = $_POST['division_position'] ?? [];
    $division_specializations = $_POST['division_specialization'] ?? [];

    // Database connection
    $mysqli = new mysqli("localhost", "root", "", "do_gentri");

    // Check connection
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    if ($id) {
        // Update existing event
        $sql = "UPDATE events SET title = ?, specification = ?, delivery = ?, venue = ?, start_date = ?, end_date = ? WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssssssi", $event_title, $event_specification, $event_delivery, $event_venue, $start_date, $end_date, $id);
    } else {
        // Insert new event
        $stmt = $mysqli->prepare("INSERT INTO events (title, specification, delivery, venue, start_date, end_date, proponent) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $event_title, $event_specification, $event_delivery, $event_venue, $start_date, $end_date, $proponents);
    }

    // Execute the statement
    if ($stmt->execute()) {
        $event_id = $stmt->insert_id;

        // Insert funding sources
        foreach ($funding_sources as $source) {
            $amount = null;
            if ($source == 'mooe') {
                $amount = $mooe_amount;
            } elseif ($source == 'sef') {
                $amount = $sef_amount;
            } elseif ($source == 'psf') {
                $amount = $psf_amount;
            } elseif ($source == 'other') {
                $amount = $other_amount;
            }
            $stmt = $mysqli->prepare("INSERT INTO funding_sources (event_id, source, amount, other_specify) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $event_id, $source, $amount, $other_specify);
            $stmt->execute();
        }

        // Insert speakers
        foreach ($speakers as $speaker) {
            $stmt = $mysqli->prepare("INSERT INTO speakers (event_id, name) VALUES (?, ?)");
            $stmt->bind_param("is", $event_id, $speaker);
            $stmt->execute();
        }

        // Insert eligible participants for School Personnel
        foreach ($levels as $level) {
            foreach ($participants as $participant) {
                foreach ($specializations as $specialization) {
                    $stmt = $mysqli->prepare("INSERT INTO eligible_participants (event_id, personnel_type, level, type, specialization) VALUES (?, 'School Personnel', ?, ?, ?)");
                    $stmt->bind_param("isss", $event_id, $level, $participant, $specialization);
                    $stmt->execute();
                }
            }
        }

        // Insert eligible participants for Division Personnel
        foreach ($sectors as $sector) {
            foreach ($division_positions as $position) {
                foreach ($division_specializations as $division_specialization) {
                    $stmt = $mysqli->prepare("INSERT INTO eligible_participants (event_id, personnel_type, division_position, division_specialization) VALUES (?, 'Division Personnel', ?, ?)");
                    $stmt->bind_param("iss", $event_id, $position, $division_specialization);
                    $stmt->execute();
                }
            }
        }

        // Redirect to a success page or display a success message
        header('Location: admin-events.php');
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $mysqli->close();
} else {
    // Redirect to the form page if accessed directly
    header('Location: admin-create_events.php');
    exit();
}
?>
