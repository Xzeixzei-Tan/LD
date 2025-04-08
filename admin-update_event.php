<?php
require_once 'config.php'; // Include your database connection file

// Check if event ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: admin-events.php');
    exit;
}

$eventId = (int)$_GET['id'];

// Fetch ENUM values for `specification`
$specificationOptions = [];
$result = $conn->query("SHOW COLUMNS FROM events LIKE 'specification'");
if ($row = $result->fetch_assoc()) {
    preg_match("/^enum\((.*)\)$/", $row['Type'], $matches);
    $specificationOptions = str_getcsv($matches[1], ",", "'");
}

// Fetch ENUM values for `delivery`
$deliveryOptions = [];
$result = $conn->query("SHOW COLUMNS FROM events LIKE 'delivery'");
if ($row = $result->fetch_assoc()) {
    preg_match("/^enum\((.*)\)$/", $row['Type'], $matches);
    $deliveryOptions = str_getcsv($matches[1], ",", "'");
}

// Fetch ENUM values for `funding_source`
$fundingOptions = [];
$result = $conn->query("SHOW COLUMNS FROM funding_sources LIKE 'source'");
if ($row = $result->fetch_assoc()) {
    preg_match("/^enum\((.*)\)$/", $row['Type'], $matches);
    $fundingOptions = str_getcsv($matches[1], ",", "'");
}

$targetOptions = [];
$result = $conn->query("SHOW COLUMNS FROM eligible_participants LIKE 'target'");
if ($row = $result->fetch_assoc()) {
    preg_match("/^enum\((.*)\)$/", $row['Type'], $matches);
    $targetOptions = str_getcsv($matches[1], ",", "'");
}

// Fetch school level options from the school_level table
$schoolLevelOptions = [];
$result = $conn->query("SELECT id, name FROM school_level");
while ($row = $result->fetch_assoc()) {
    $schoolLevelOptions[] = $row;
}

// Fetch type options from the classification table
$typeOptions = [];
$result = $conn->query("SELECT id, name FROM classification");
while ($row = $result->fetch_assoc()) {
    $typeOptions[] = $row;
}

// Fetch specialization options from the specialization table
$specializationOptions = [];
$result = $conn->query("SELECT id, name FROM specialization");
while ($row = $result->fetch_assoc()) {
    $specializationOptions[] = $row;
}

// Fetch ENUM values for `department_name`
$departmentOptions = [];
$result = $conn->query("SHOW COLUMNS FROM division_participants LIKE 'department_name'");
if ($row = $result->fetch_assoc()) {
    preg_match("/^enum\((.*)\)$/", $row['Type'], $matches);
    $departmentOptions = str_getcsv($matches[1], ",", "'");
}

// Fetch event data
$eventData = [];
$result = $conn->query("SELECT * FROM events WHERE id = $eventId");
if ($result->num_rows > 0) {
    $eventData = $result->fetch_assoc();
} else {
    header('Location: admin-events.php');
    exit;
}

// Get estimated_participants from the events table
$estimatedParticipants = $eventData['estimated_participants'] ?? 0;

// Fetch event days
$eventDays = [];
$result = $conn->query("SELECT * FROM event_days WHERE event_id = $eventId ORDER BY day_number");
while ($row = $result->fetch_assoc()) {
    $eventDays[] = $row;
}

// Fetch funding sources
$fundingSources = [];
$result = $conn->query("SELECT * FROM funding_sources WHERE event_id = $eventId");
while ($row = $result->fetch_assoc()) {
    $fundingSources[$row['source']] = $row['amount'];
}

// Fetch meal plans
$mealPlans = [];
$result = $conn->query("SELECT * FROM meal_plan WHERE event_id = $eventId");
while ($row = $result->fetch_assoc()) {
    $mealPlans[$row['day_date']] = json_decode($row['meal_types']) ?? explode(', ', $row['meal_types']);
}

// Convert meal plans to a format easier to work with in JavaScript
$mealPlansForJS = array();
foreach ($mealPlans as $date => $mealTypes) {
    // Make sure the meal types is always an array
    if (is_string($mealTypes)) {
        $mealTypes = explode(', ', $mealTypes);
    }
    $mealPlansForJS[$date] = $mealTypes;
}

// Fetch speakers
$speakers = [];
$result = $conn->query("SELECT * FROM speakers WHERE event_id = $eventId");
while ($row = $result->fetch_assoc()) {
    $speakers[] = $row['name'];
}

// Fetch target participant info
$targetParticipant = null;
$result = $conn->query("SELECT * FROM eligible_participants WHERE event_id = $eventId");
if ($row = $result->fetch_assoc()) {
    $targetParticipant = $row;
    
    // Fetch school participants if applicable
    if ($targetParticipant['target'] === 'School' || $targetParticipant['target'] === 'Both') {
        $result = $conn->query("SELECT * FROM school_participants WHERE eligible_participant_id = {$targetParticipant['id']}");
        if ($schoolData = $result->fetch_assoc()) {
            $targetParticipant['school_level'] = explode(',', $schoolData['school_level'] ?? '');
            $targetParticipant['type'] = explode(',', $schoolData['type'] ?? '');
            $targetParticipant['specialization'] = explode(',', $schoolData['specialization'] ?? '');
        }
    }
    
    // Fetch division participants if applicable
    if ($targetParticipant['target'] === 'Division' || $targetParticipant['target'] === 'Both') {
        $result = $conn->query("SELECT * FROM division_participants WHERE eligible_participant_id = {$targetParticipant['id']}");
        $targetParticipant['departments'] = [];
        while ($deptRow = $result->fetch_assoc()) {
            $targetParticipant['departments'][] = $deptRow['department_name'];
        }
    }
}

$successMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $title = $_POST['title'];
    $specification = $_POST['specification'];
    $delivery = $_POST['delivery'];
    $venue = isset($_POST['venue']) ? $_POST['venue'] : '';
    $startDate = $_POST['start-date'];
    $endDate = $_POST['end-date'];
    $proponent = $_POST['proponent'];
    $updatedAt = date('Y-m-d H:i:s');
    $estimatedParticipants = $_POST['estimated_participants'] ?? 0;

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Update events table
        $stmt = $conn->prepare("UPDATE events SET title = ?, specification = ?, delivery = ?, venue = ?, start_date = ?, end_date = ?, proponent = ?, updated_at = ?, estimated_participants = ? WHERE id = ?");
        $stmt->bind_param("ssssssssii", $title, $specification, $delivery, $venue, $startDate, $endDate, $proponent, $updatedAt, $estimatedParticipants, $eventId);
        $stmt->execute();
        $stmt->close();

        // Delete existing event days to replace with updated ones
        $conn->query("DELETE FROM event_days WHERE event_id = $eventId");

        // Insert updated event days
        if (isset($_POST['event_days']) && is_array($_POST['event_days'])) {
            foreach ($_POST['event_days'] as $dayNumber => $dayData) {
                if (isset($dayData['date']) && !empty($dayData['date'])) {
                    $dayDate = $dayData['date'];
                    $startTime = isset($dayData['start_time']) ? $dayData['start_time'] : '08:00';
                    $endTime = isset($dayData['end_time']) ? $dayData['end_time'] : '17:00';
                    
                    $stmt = $conn->prepare("INSERT INTO event_days (event_id, day_date, day_number, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("isiss", $eventId, $dayDate, $dayNumber, $startTime, $endTime);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        // Delete existing funding sources
        $conn->query("DELETE FROM funding_sources WHERE event_id = $eventId");

        // Insert updated funding sources
        if (isset($_POST['funding_source'])) {
            foreach ($_POST['funding_source'] as $source) {
                $amount = $_POST[$source . '_amount'];
                $stmt = $conn->prepare("INSERT INTO funding_sources (event_id, source, amount) VALUES (?, ?, ?)");
                $stmt->bind_param("isd", $eventId, $source, $amount);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Delete existing meal plans
        $conn->query("DELETE FROM meal_plan WHERE event_id = $eventId");

        // Insert updated meal plans
        if (isset($_POST['meal_plan']) && is_array($_POST['meal_plan'])) {
            foreach ($_POST['meal_plan'] as $dayNumber => $meals) {
                if (isset($_POST['event_days'][$dayNumber]['date']) && !empty($meals)) {
                    $dayDate = $_POST['event_days'][$dayNumber]['date'];
                    
                    // Create a simple comma-separated string of meal types
                    $mealTypesString = implode(', ', $meals);
                    
                    // Insert the meal plan record
                    $stmt = $conn->prepare("INSERT INTO meal_plan (event_id, day_date, meal_types) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $eventId, $dayDate, $mealTypesString);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        // Delete existing speakers
        $conn->query("DELETE FROM speakers WHERE event_id = $eventId");

        // Insert updated speakers
        if (isset($_POST['speaker']) && is_array($_POST['speaker'])) {
            foreach ($_POST['speaker'] as $speaker) {
                if (!empty($speaker)) {
                    $stmt = $conn->prepare("INSERT INTO speakers (event_id, name, created_at, updated_at) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("isss", $eventId, $speaker, $updatedAt, $updatedAt);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        // Delete existing target personnel data
        // First, get eligible_participant_id
        $eligibleParticipantId = null;
        $result = $conn->query("SELECT id FROM eligible_participants WHERE event_id = $eventId");
        if ($row = $result->fetch_assoc()) {
            $eligibleParticipantId = $row['id'];
            
            // Delete related data
            if ($eligibleParticipantId) {
                $conn->query("DELETE FROM school_participants WHERE eligible_participant_id = $eligibleParticipantId");
                $conn->query("DELETE FROM division_participants WHERE eligible_participant_id = $eligibleParticipantId");
                $conn->query("DELETE FROM eligible_participants WHERE id = $eligibleParticipantId");
            }
        }

        // Insert updated target personnel data
        if (isset($_POST['target_personnel']) && !empty($_POST['target_personnel'])) {
            $target = $_POST['target_personnel'];
            $stmt = $conn->prepare("INSERT INTO eligible_participants (event_id, target) VALUES (?, ?)");
            $stmt->bind_param("is", $eventId, $target);
            $stmt->execute();
            $eligibleParticipantId = $stmt->insert_id;
            $stmt->close();

            // Insert into school_participants if "school" or "both" is selected
            if ($target === 'School' || $target === 'Both') {
                // Create comma-separated strings of IDs
                $schoolLevelIds = isset($_POST['school_level']) ? implode(',', $_POST['school_level']) : NULL;
                $classificationIds = isset($_POST['type']) ? implode(',', $_POST['type']) : NULL;
                $specializationIds = isset($_POST['specialization']) ? implode(',', $_POST['specialization']) : NULL;

                $stmt = $conn->prepare("INSERT INTO school_participants (eligible_participant_id, school_level, type, specialization) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $eligibleParticipantId, $schoolLevelIds, $classificationIds, $specializationIds);
                $stmt->execute();
                $stmt->close();
            }

            // Insert into division_participants if "division" or "both" is selected
            if ($target === 'Division' || $target === 'Both') {
                if (isset($_POST['department']) && is_array($_POST['department'])) {
                    foreach ($_POST['department'] as $department) {
                        $stmt = $conn->prepare("INSERT INTO division_participants (eligible_participant_id, department_name) VALUES (?, ?)");
                        $stmt->bind_param("is", $eligibleParticipantId, $department);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            }
        }

        // Commit the transaction
        $conn->commit();

        // Get all registered participants
$participantsSQL = "SELECT 
                    ru.id AS registration_id,
                    ru.user_id,
                    CONCAT(u.first_name, ' ', 
                        CASE WHEN u.middle_name IS NOT NULL AND u.middle_name != '' 
                             THEN CONCAT(UPPER(SUBSTRING(u.middle_name, 1, 1)), '. ') 
                             ELSE '' END,
                        u.last_name,
                        CASE WHEN u.suffix IS NOT NULL AND u.suffix != '' THEN CONCAT(' ', u.suffix) ELSE '' END
                    ) AS name,
                    u.email
                FROM registered_users ru
                JOIN users u ON ru.user_id = u.id
                WHERE ru.event_id = ?";
$stmt = $conn->prepare($participantsSQL);
$stmt->bind_param("i", $eventId); // Make sure this variable name matches your event ID variable
$stmt->execute();
$participantsResult = $stmt->get_result();

// Create notification message
$notification_message = "Event updated: " . $title;

// Loop through each registered user and create a notification
while ($participant = $participantsResult->fetch_assoc()) {
    $participant_user_id = $participant['user_id'];
    
    // Insert notification for this specific user
    $notification_sql = "INSERT INTO notifications (user_id, message, created_at, is_read, notification_type, notification_subtype, event_id) VALUES (?, ?, NOW(), 0, 'user', 'update_event', ?)";
    $notification_stmt = $conn->prepare($notification_sql);
    $notification_stmt->bind_param("isi", $participant_user_id, $notification_message, $eventId);
    $notification_stmt->execute();
    $notification_stmt->close();
}

// Close the participant query statement
$stmt->close();

// Set success message
$successMessage = "Event updated successfully!";

        // Redirect to the same page with success parameter
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $eventId . "&success=update");
        exit();
    } catch (Exception $e) {
        // Roll back the transaction if something failed
        $conn->rollback();
        $errorMessage = "Error updating event: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="styles/admin-update-events.css" rel="stylesheet">
    <title>Update Event</title>

    <style>
/* Modal Styles */
/* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s;
        }

        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 30px;
            border-radius: 8px;
            width: 400px;
            max-width: 90%;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.4s;
        }

        .modal h2 {
            margin-top: 0;
            color: #333;
            font-family: Arial, sans-serif;
        }

        .modal p {
            color: #666;
            margin-bottom: 25px;
            font-family: Arial, sans-serif;
        }

        #close-modal-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            font-family: Arial, sans-serif;
        }

        #close-modal-btn:hover {
            background-color: #45a049;
        }

        @keyframes fadeIn {
            from {opacity: 0;}
            to {opacity: 1;}
        }

        @keyframes slideIn {
            from {transform: translateY(-50px); opacity: 0;}
            to {transform: translateY(0); opacity: 1;}
        }

        /* Check Mark Animation */
        .success-checkmark {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            position: relative;
        }
        .modal-content h2 {
            font-family: Montserrat;
        }
        .modal-content p {
            font-family: Montserrat;
        }

        .check-icon {
            width: 80px;
            height: 80px;
            position: relative;
            border-radius: 50%;
            box-sizing: content-box;
            border: 4px solid #4CAF50;
        }

        .check-icon::before {
            top: 3px;
            left: -2px;
            width: 30px;
            transform-origin: 100% 50%;
            border-radius: 100px 0 0 100px;
        }

        .check-icon::after {
            top: 0;
            left: 30px;
            width: 60px;
            transform-origin: 0 50%;
            border-radius: 0 100px 100px 0;
            animation: rotate-circle 4.25s ease-in;
        }

        .check-icon::before, .check-icon::after {
            content: '';
            height: 100px;
            position: absolute;
            background: #FFFFFF;
            transform: rotate(-45deg);
        }

        .icon-line {
            height: 5px;
            background-color: #4CAF50;
            display: block;
            border-radius: 2px;
            position: absolute;
            z-index: 10;
        }

        .icon-line.line-tip {
            top: 46px;
            left: 14px;
            width: 25px;
            transform: rotate(45deg);
            animation: icon-line-tip 1s;
        }

        .icon-line.line-long {
            top: 38px;
            right: 8px;
            width: 47px;
            transform: rotate(-45deg);
            animation: icon-line-long 1s;
        }

        .icon-circle {
            top: -4px;
            left: -4px;
            z-index: 10;
            width: 80px;
            height: 80px;
            border-radius: 60%;
            position: absolute;
            box-sizing: content-box;
            border: 4px solid rgba(76, 175, 80, 0.5);
        }

        .icon-fix {
            top: 8px;
            width: 5px;
            left: 26px;
            z-index: 1;
            height: 85px;
            position: absolute;
            transform: rotate(-45deg);
            background-color: #FFFFFF;
        }

        @keyframes rotate-circle {
            0% {
                transform: rotate(-45deg);
            }
            5% {
                transform: rotate(-45deg);
            }
            12% {
                transform: rotate(-405deg);
            }
            100% {
                transform: rotate(-405deg);
            }
        }

        @keyframes icon-line-tip {
            0% {
                width: 0;
                left: 1px;
                top: 19px;
            }
            54% {
                width: 0;
                left: 1px;
                top: 19px;
            }
            70% {
                width: 50px;
                left: -8px;
                top: 37px;
            }
            84% {
                width: 17px;
                left: 21px;
                top: 48px;
            }
            100% {
                width: 25px;
                left: 14px;
                top: 46px;
            }
        }

        @keyframes icon-line-long {
            0% {
                width: 0;
                right: 46px;
                top: 54px;
            }
            65% {
                width: 0;
                right: 46px;
                top: 54px;
            }
            84% {
                width: 55px;
                right: 0px;
                top: 35px;
            }
            100% {
                width: 47px;
                right: 8px;
                top: 38px;
            }
        }

       
</style>

    
</head>
<body>
<div class="sidebar" id="sidebar">
    <div class="logo">
        <button id="toggleSidebar" class="toggle-btn">
            <i class="fas fa-bars"></i>
        </button>
    </div>
        
    <div class="menu">
        <a href="admin-dashboard.php">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="admin-events.php" class="active">
            <i class="fas fa-calendar-alt"></i>
            <span>Events</span>
        </a>
        <a href="admin-users.php">
            <i class="fas fa-users"></i>
            <span>Users</span>
        </a>
    </div>
</div>

<div class="content" id="content">
    <div class="content-header">
        <img src="styles/photos/DO-LOGO.png" width="70px" height="70px">
        <p>Learning and Development</p>
                <h1>EVENT MANAGEMENT SYSTEM</h1>
    </div>
    
    <div class="content-body">
                <br><br><br><br>
                <br>

    <div class="form-container">
        <h3>Update Events Details</h3>
        <?php if (!empty($successMessage)): ?>
            <div class="success-message" style="display: block;"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $eventId; ?>">
            <!-- General Information -->
            <div class="form-group-1">
                <div class="section-title">Event Details</div>
                <h4>Event Title:</h4>
                <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($eventData['title']); ?>">
                
                <h4>Event Specification:</h4>
                <select id="specification" name="specification" required>
                    <?php foreach ($specificationOptions as $option): ?>
                        <option value="<?php echo htmlspecialchars($option); ?>" <?php echo ($eventData['specification'] === $option) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($option); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <h4>Delivery:</h4>
                <select id="delivery" name="delivery" required>
                    <?php foreach ($deliveryOptions as $option): ?>
                        <option value="<?php echo htmlspecialchars($option); ?>" <?php echo ($eventData['delivery'] === $option) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($option); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <div id="venue-field">
                                <h4>Venue/Platform</h4>
                                <input type="text" name="venue" placholder="Enter venue" required value="<?php echo htmlspecialchars($eventData['venue']); ?>">
                            </div>
                
                <!-- Funding Sources -->
                <h4>Funding Sources</h4>
                <div class="funding-options-row">
                    <?php foreach ($fundingOptions as $option): ?>
                        <div class="funding-option">
                            <label>
                                <input type="checkbox" name="funding_source[]" value="<?php echo htmlspecialchars($option); ?>" 
                                    <?php echo (isset($fundingSources[$option])) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($option); ?>
                            </label>
                            
                            <div class="amount-field" <?php echo (isset($fundingSources[$option])) ? 'style="display:block;"' : 'style="display:none;"'; ?>>
                                <label for="<?php echo htmlspecialchars($option); ?>_amount">Amount</label>
                                <div class="input-with-symbol">
                                    <span class="currency-symbol">₱</span>
                                    <input type="number" step="0.01" min="0" id="<?php echo htmlspecialchars($option); ?>_amount" 
                                        name="<?php echo htmlspecialchars($option); ?>_amount" 
                                        value="<?php echo isset($fundingSources[$option]) ? htmlspecialchars($fundingSources[$option]) : ''; ?>">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <h4>Start Date:</h4>
                        <input type="date" id="start-date" name="start-date" required value="<?php echo htmlspecialchars($eventData['start_date']); ?>" onchange="generateDayFields()">
                    </div>

                    <div class="form-col">
                        <h4>End Date:</h4>
                        <input type="date" id="end-date" name="end-date" required value="<?php echo htmlspecialchars($eventData['end_date']); ?>" onchange="generateDayFields()">
                    </div>
                </div>
                    
                <!-- Event Days Section -->
                <div class="section-title">Event Days Schedule</div>
                <div id="event-days-container"></div>
            <br>
            <!-- INSERT MEAL PLAN SECTION HERE -->
                <?php
            // Meal Plan section of your form
            
            echo '<div id="meal-plan-section" class="section-title">Meal Plan</div>';
            echo '<div id="meal-plan-container">';
            // Only generate this if start and end dates are set
            if (!empty($_POST['start-date']) && !empty($_POST['end-date'])) {
                $startDate = new DateTime($_POST['start-date']);
                $endDate = new DateTime($_POST['end-date']);
                $dayDiff = $endDate->diff($startDate)->days + 1;
                
                for ($i = 1; $i <= $dayDiff; $i++) {
                    $currentDate = clone $startDate;
                    $currentDate->modify('+' . ($i - 1) . ' days');
                    $dateString = $currentDate->format('Y-m-d');
                    
                    echo '<div class="meal-day">';
                    echo '<h4>Meals for Day ' . $i . ' - ' . $dateString . '</h4>';
                    echo '<div class="checkbox-subgroup">';
                    
                    $mealOptions = ['Breakfast', 'AM Snack', 'Lunch', 'PM Snack', 'Dinner'];
                    foreach ($mealOptions as $meal) {
                        echo '<label>';
                        echo '<input type="checkbox" name="meal_plan[' . $i . '][]" value="' . $meal . '"> ' . $meal;
                        echo '</label>';
                    }
                    
                    echo '</div>';
                    echo '</div>';
                }
            }
            echo '</div>';
            echo '</div>';
            ?>


             <div class="form-group-2">
             <div class="form-col">
                <h4>No. of Estimated Participants:</h4>
                <input type="number" id="estimated-participants" name="estimated_participants" min="1" placeholder="Enter estimated number of participants" required value="<?php echo htmlspecialchars($estimatedParticipants); ?>">
            </div>
            </div>

            <!-- Speakers -->
            <div class="form-group-3">
                            <div class="section-title">Organizers & Trainers</div>
                                
                            <h4>Proponents:</h4>
                            <input type="text" name="proponent" placeholder="Enter organizer name" required value="<?php echo htmlspecialchars($eventData['proponent']); ?>">
                                
                            <div id="speakers-container">
                                    <div class="speakers-header">
                                        <h4>Speaker/Resource Person:</h4>
                                        <button type="button" class="add-speaker-btn" onclick="addNewSpeaker()">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                    <?php 
                    // If there are existing speakers, create input fields for them
                    if (!empty($speakers)) {
                        foreach ($speakers as $index => $speaker) {
                            echo '<div class="speaker-input-group">';
                            echo '<input type="text" name="speaker[]" placeholder="Enter speaker/resource person" value="' . htmlspecialchars($speaker) . '">';
                            // Add remove button for all speakers except the first one
                            if ($index > 0) {
                                echo '<button type="button" class="remove-speaker-btn"><i class="fas fa-times"></i></button>';
                            }
                            echo '</div>';
                        }
                    } else {
                        // Default empty input
                        echo '<div class="speaker-input-group">';
                        echo '<input type="text" name="speaker[]" placeholder="Enter speaker/resource person">';
                        echo '</div>';
                    }
                    ?>
</div>
                </div>
            

            <!-- Target Personnel -->
            <div class="form-group-4">
                <div class="section-title">Target Personnel</div>
                <div class="personnel-selection">
                    <label for="target-personnel">Target Participants:</label>
                    <select id="target-personnel" name="target_personnel" class="target" required>
                        <option value="">Select Target</option>
                        <?php foreach ($targetOptions as $option): ?>
                            <option value="<?php echo htmlspecialchars($option); ?>" 
                                <?php echo isset($targetParticipant['target']) && $targetParticipant['target'] === $option ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($option); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                
                
                <!-- School Personnel Options -->
                <div id="school-personnel" style="<?php echo (isset($targetParticipant['target']) && ($targetParticipant['target'] === 'School' || $targetParticipant['target'] === 'Both')) ? 'display:block;' : 'display:none;'; ?>">
                    <h4>School Level:</h4>
                    <br>
                    <div class="checkbox-subgroup">
                        <?php foreach ($schoolLevelOptions as $option): ?>
                            <label>
                                <input type="checkbox" name="school_level[]" value="<?php echo $option['id']; ?>" 
                                    <?php echo (isset($targetParticipant['school_level']) && in_array($option['id'], $targetParticipant['school_level'])) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($option['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <br>
                    
                    <h4>Type:</h4>
                    <br>
                    <div class="checkbox-subgroup">
                        <?php foreach ($typeOptions as $option): ?>
                            <label>
                                <input type="checkbox" name="type[]" value="<?php echo $option['id']; ?>" 
                                    <?php echo (isset($targetParticipant['type']) && in_array($option['id'], $targetParticipant['type'])) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($option['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <br>
                    
                    <h4>Specialization:</h4>
                    <br>
                    <div class="checkbox-subgroup">
                        <?php foreach ($specializationOptions as $option): ?>
                            <label>
                                <input type="checkbox" name="specialization[]" value="<?php echo $option['id']; ?>" 
                                    <?php echo (isset($targetParticipant['specialization']) && in_array($option['id'], $targetParticipant['specialization'])) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($option['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <br>
                <!-- Division Personnel Options -->
                <div id="division-personnel" style="<?php echo (isset($targetParticipant['target']) && ($targetParticipant['target'] === 'Division' || $targetParticipant['target'] === 'Both')) ? 'display:block;' : 'display:none;'; ?>">
                    <h4>Departments</h4>
                    <br>
                    <div class="checkbox-subgroup">
                        <?php foreach ($departmentOptions as $option): ?>
                            <label>
                                <input type="checkbox" name="department[]" value="<?php echo htmlspecialchars($option); ?>"
                                    <?php echo (isset($targetParticipant['departments']) && in_array($option, $targetParticipant['departments'])) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($option); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
    
            <button type="submit" class="submit-btn">Update Event</button>
        </form>
    </div>
</div>
</div>
</div>

    <div id="success-modal" class="modal">
            <div class="modal-content">
                <div class="success-checkmark">
                    <div class="check-icon">
                        <span class="icon-line line-tip"></span>
                        <span class="icon-line line-long"></span>
                        <div class="icon-circle"></div>
                        <div class="icon-fix"></div>
                    </div>
                </div>
                <h2>Success!</h2>
                <p>Event has been updated successfully.</p>
                <button id="close-modal-btn">OK</button>
            </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const phpMealPlans = <?php echo json_encode($mealPlansForJS); ?>;
    const eventDays = <?php echo json_encode($eventDays); ?>;
    // ================= SIDEBAR FUNCTIONALITY =================
    const sidebar = document.querySelector('.sidebar');
    const content = document.getElementById('content');
    const toggleBtn = document.getElementById('toggleSidebar');

    // Check if sidebar state is saved in localStorage
    const isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    
    // Set initial state based on localStorage
    if (isSidebarCollapsed) {
        sidebar.classList.add('collapsed');
        content.classList.add('expanded');
    }
    
    toggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        content.classList.toggle('expanded');
        
        // Save sidebar state to localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    });

    // ================= SMART TITLE CASE FUNCTIONALITY =================
    // Enhanced list of conjunction and preposition words
    const lowercaseWords = [
        'and', 'or', 'but', 'nor', 'for', 'yet', 'so', 
        'the', 'a', 'an', 'as', 'at', 'by', 'for', 'in',
        'in', 'on', 'at', 'to', 'of', 'with', 'by', 
        'from', 'into', 'onto', 'over', 'under', 'if', 'once', 
        'how', 'no', 'not', 'off', 'out', 'up', 'down',
    ];

    function smartTitleCase(input) {
        // Trim and split the input into words
        const words = input.trim().split(/\s+/);

        // Special words mapping
        const specialWords = {
            'ph': 'PH',
            'do': 'DO',
            'it': 'IT',
            'hr': 'HR'
        };

        // Process each word
        const processedWords = words.map((word, index) => {
            // Convert to lowercase
            let processedWord = word.toLowerCase();

            // Check special words first
            if (specialWords[processedWord]) {
                return specialWords[processedWord];
            }
            
            // If first word or not a lowercase word, capitalize first letter
            if (index === 0 || !lowercaseWords.includes(processedWord)) {
                processedWord = processedWord.charAt(0).toUpperCase() + processedWord.slice(1);
            }
            
            return processedWord;
        });

        // Join the words back together
        return processedWords.join(' ');
    }

    function applySmartCase(input) {
        // Only apply if input is not empty and not just whitespace
        if (input && input.value && input.value.trim()) {
            input.value = smartTitleCase(input.value);
        }
    }

    // Apply to specific inputs
    const inputsToCapitalize = [
        document.querySelector('input[name="title"]'),
        document.querySelector('input[name="proponent"]'),
        document.querySelector('input[name="venue"]')
    ];

    // Add blur event listeners
    inputsToCapitalize.forEach(input => {
        if (input) {
            input.addEventListener('blur', function() {
                applySmartCase(this);
            });
        }
    });

    

    // ================= SPEAKERS MANAGEMENT =================
    const speakersContainer = document.getElementById('speakers-container');
    
    if (speakersContainer) {
        // For dynamically added speaker inputs
        speakersContainer.addEventListener('blur', function(event) {
            if (event.target.name === 'speaker[]') {
                applySmartCase(event.target);
            }
        }, true);
        
        // Add speaker button functionality
        window.addNewSpeaker = function() {
            const newSpeaker = document.createElement('div');
            newSpeaker.className = 'speaker-input-group';
            newSpeaker.innerHTML = `
                <input type="text" name="speaker[]" placeholder="Enter speaker/resource person">
                <button type="button" class="remove-speaker-btn"><i class="fas fa-times"></i></button>
            `;
            speakersContainer.appendChild(newSpeaker);

            // Add smart case to new speaker input
            const newInput = newSpeaker.querySelector('input[name="speaker[]"]');
            newInput.addEventListener('blur', function() {
                applySmartCase(this);
            });
        };
        
        // Remove speaker button functionality
        speakersContainer.addEventListener('click', function(event) {
            const removeBtn = event.target.closest('.remove-speaker-btn');
            if (removeBtn) {
                const speakerGroups = speakersContainer.querySelectorAll('.speaker-input-group');
                const speakerGroup = removeBtn.closest('.speaker-input-group');
                
                // If there's only one speaker group
                if (speakerGroups.length === 1) {
                    const input = speakerGroup.querySelector('input');
                    input.value = ''; // Clear the input
                } else {
                    // Remove the speaker group
                    speakerGroup.remove();
                }
            }
        });
    }

    // ================= DELIVERY METHOD & VENUE FUNCTIONALITY =================
    const deliverySelect = document.getElementById('delivery');
    const venueField = document.getElementById('venue-field');
    const mealPlanContainer = document.getElementById('meal-plan-container');
    const mealPlanSection = document.getElementById('meal-plan-section');
    
    if (deliverySelect && venueField) {
        function updateVenueVisibility() {
            const venueInput = venueField.querySelector('input[name="venue"]');
            
            // Always show venue field and keep it required regardless of delivery method
            venueField.style.display = 'block';
            if (venueInput) {
                venueInput.setAttribute('required', '');
            }
            
            // Always show meal plan container and section title
            if (mealPlanContainer) {
                mealPlanContainer.style.display = 'block';
            }
            // Also show the section title
            if (mealPlanSection) {
                mealPlanSection.style.display = 'block';
            }
        }
        
        // Initial setup when page loads
        updateVenueVisibility();
        
        // Add event listener for when delivery selection changes
        deliverySelect.addEventListener('change', updateVenueVisibility);
        
        // Run again after a short delay to ensure it catches any initial state issues
        setTimeout(updateVenueVisibility, 100);
    }

    // ================= TARGET PERSONNEL FUNCTIONALITY =================
    const targetSelect = document.getElementById('target-personnel');
    const schoolOptions = document.getElementById('school-personnel');
    const divisionOptions = document.getElementById('division-personnel');
    
    if (targetSelect && schoolOptions && divisionOptions) {
        function updateTargetVisibility() {
            if (targetSelect.value === 'School') {
                schoolOptions.style.display = 'block';
                divisionOptions.style.display = 'none';
            } else if (targetSelect.value === 'Division') {
                schoolOptions.style.display = 'none';
                divisionOptions.style.display = 'block';
            } else if (targetSelect.value === 'Both') {
                schoolOptions.style.display = 'block';
                divisionOptions.style.display = 'block';
            } else {
                schoolOptions.style.display = 'none';
                divisionOptions.style.display = 'none';
            }
        }
        
        // Initial setup
        updateTargetVisibility();
        
        // Add event listener
        targetSelect.addEventListener('change', updateTargetVisibility);
    }

    // ================= FUNDING SOURCE FUNCTIONALITY =================
    function toggleFundingAmountField() {
        const fundingCheckboxes = document.querySelectorAll('input[name="funding_source[]"]');
        
        fundingCheckboxes.forEach(function(checkbox) {
            const amountField = checkbox.closest('.funding-option')?.querySelector('.amount-field');
            
            if (amountField) {
                // Initially set display based on checkbox state
                if (checkbox.checked) {
                    amountField.style.display = 'block';
                } else {
                    amountField.style.display = 'none';
                }
                
                // Add change event listener
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        amountField.style.display = 'block';
                    } else {
                        amountField.style.display = 'none';
                        // Clear the amount input when unchecked
                        const amountInput = amountField.querySelector('input[type="number"]');
                        if (amountInput) {
                            amountInput.value = '';
                        }
                    }
                });
            }
        });
    }

    // Call funding source toggle on page load
    toggleFundingAmountField();

    // ================= MEAL PLAN STORAGE =================
    // Store meal plan selections
    let savedMealSelections = {};

    // Save meal selections before regenerating the meal plan container
    function saveMealSelections() {
        const mealCheckboxes = document.querySelectorAll('input[name^="meal_plan["]');
        savedMealSelections = {};
        
        mealCheckboxes.forEach(checkbox => {
            const name = checkbox.name;
            if (checkbox.checked) {
                if (!savedMealSelections[name]) {
                    savedMealSelections[name] = [];
                }
                savedMealSelections[name].push(checkbox.value);
            }
        });
    }

    // Restore meal selections after regenerating the meal plan container
    function restoreMealSelections() {
        Object.keys(savedMealSelections).forEach(name => {
            const checkboxes = document.querySelectorAll(`input[name="${name}"]`);
            checkboxes.forEach(checkbox => {
                if (savedMealSelections[name].includes(checkbox.value)) {
                    checkbox.checked = true;
                }
            });
        });
    }

    // ================= EVENT DAYS FUNCTIONALITY =================
    // Define generateDayFields function in global scope
    window.generateDayFields = function() {
        const startDate = document.getElementById('start-date')?.value;
        const endDate = document.getElementById('end-date')?.value;
        const eventDaysContainer = document.getElementById('event-days-container');
        const mealPlanContainer = document.getElementById('meal-plan-container');

        if (!startDate || !endDate || !eventDaysContainer) {
            return;
        }

        // Save meal selections before clearing containers
        saveMealSelections();

        // Clear existing fields
        eventDaysContainer.innerHTML = '';
        if (mealPlanContainer) {
            mealPlanContainer.innerHTML = '';
        }

        const start = new Date(startDate);
        const end = new Date(endDate);
        
        if (isNaN(start.getTime()) || isNaN(end.getTime())) {
            return;
        }
        
        const dayDiff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
        
        if (dayDiff <= 0 || dayDiff > 31) {
            return;
        }

        // Create event days for scheduling
        for (let i = 1; i <= dayDiff; i++) {
            const currentDate = new Date(start);
            currentDate.setDate(start.getDate() + i - 1);
            const formattedDate = currentDate.toISOString().split('T')[0];
            
            // Check if we have existing data for this day
            let startTime = "08:00";
            let endTime = "17:00";

            // Look for matching day in eventDays PHP array
            if (typeof eventDays !== 'undefined' && eventDays.length > 0) {
                for (let j = 0; j < eventDays.length; j++) {
                    if (eventDays[j].day_date === formattedDate) {
                        startTime = eventDays[j].start_time;
                        endTime = eventDays[j].end_time;
                        break;
                    }
                }
            }

            // Event Days Section
            const dayDiv = document.createElement('div');
            dayDiv.className = 'event-day';
            dayDiv.innerHTML = `
                <h4>Day ${i} - ${formattedDate}</h4>
                <input type="hidden" name="event_days[${i}][date]" value="${formattedDate}">
                <div class="time-inputs">
                    <label>Start Time:
                        <input type="time" name="event_days[${i}][start_time]" value="${startTime}">
                    </label>
                    <label>End Time:
                        <input type="time" name="event_days[${i}][end_time]" value="${endTime}">
                    </label>
                </div>
            `;
            eventDaysContainer.appendChild(dayDiv);

            // Always create meal plan sections regardless of delivery method
            if (mealPlanContainer) {
                const mealDiv = document.createElement('div');
                mealDiv.className = 'meal-day';
                
                // Create meal plan checkboxes with proper checked state
                const mealTypes = ['Breakfast', 'AM Snack', 'Lunch', 'PM Snack', 'Dinner'];
                let checkboxesHtml = '';
                
                mealTypes.forEach(mealType => {
                    // Check if this meal type was previously selected for this date
                    const isChecked = phpMealPlans[formattedDate] && 
                                    phpMealPlans[formattedDate].includes(mealType);
                    
                    checkboxesHtml += `
                        <label>
                            <input type="checkbox" name="meal_plan[${i}][]" value="${mealType}" ${isChecked ? 'checked' : ''}> ${mealType}
                        </label>
                    `;
                });
                    
                mealDiv.innerHTML = `
                    <h4>Meals for Day ${i} - ${formattedDate}</h4>
                    <div class="checkbox-subgroup">
                        ${checkboxesHtml}
                    </div>
                `;
                mealPlanContainer.appendChild(mealDiv);
            }
        }
        
        // Restore previously checked meal selections
        restoreMealSelections();
        
        // Always show meal plan sections
        if (mealPlanContainer) mealPlanContainer.style.display = 'block';
        if (mealPlanSection) mealPlanSection.style.display = 'block';
    };

    // If both dates are already set, generate the day fields
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    if (startDateInput && endDateInput && startDateInput.value && endDateInput.value) {
        generateDayFields();
    }
});

// Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('success-modal');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const demoButton = document.getElementById('demo-button');
    
    // Check URL parameters for success message
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === 'update') {
        console.log("Success parameter detected, showing modal");
        showModal();
        // Remove the parameter from URL to prevent showing modal on refresh
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    
    function showModal() {
        modal.style.display = 'block';
    }
    
    function closeModal() {
        modal.style.display = 'none';
    }
    
    // Close modal when clicking the close button
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            closeModal();
            // Redirect to admin-events.php
            window.location.href = 'admin-events.php';
        });
    }
            
    // Close modal when clicking outside the modal content
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });
});
</script>

</div>
</body>
</html>