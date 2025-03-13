<?php
require_once 'config.php'; // Include your database connection file

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
    $createdAt = date('Y-m-d H:i:s');
    $updatedAt = date('Y-m-d H:i:s');

    // Insert data into the events table
    $stmt = $conn->prepare("INSERT INTO events (title, specification, delivery, venue, start_date, end_date, proponent, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $title, $specification, $delivery, $venue, $startDate, $endDate, $proponent, $createdAt, $updatedAt);
    $stmt->execute();
    $eventId = $stmt->insert_id;
    $stmt->close();

    // Insert funding sources into the funding_sources table
    if (isset($_POST['funding_source'])) {
        foreach ($_POST['funding_source'] as $source) {
            $amount = $_POST[$source . '_amount'];
            $stmt = $conn->prepare("INSERT INTO funding_sources (event_id, source, amount) VALUES (?, ?, ?)");
            $stmt->bind_param("isd", $eventId, $source, $amount);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Insert meal plans into the meal_plan table
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

    // Insert speakers into the speaker table
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

    // Insert target personnel into the eligible_participant table first
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

    // Set success message
    $successMessage = "Event created successfully!";
    
    // Redirect to the events page with success parameter
    header("Location: admin-events.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="styles/admin-create_events.css" rel="stylesheet">
    <script src="scripts/admin-create_events.js" defer></script>
    <title>Create Events</title>
    <style>
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            border-left: 5px solid #28a745;
            font-weight: bold;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            animation: fadeIn 0.5s;
            position: relative;
            display: none;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .meal-day {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="menu">
                <a href="admin-dashboard.php"><i class="fas fa-home mr-3"></i>Home</a>
                <a href="admin-events.php" class="active"><i class="fas fa-calendar-alt mr-3"></i>Events</a>
                <a href="admin-users.php"><i class="fas fa-users mr-3"></i>Users</a>
                <a href="admin-notif.php"><i class="fas fa-bell mr-3"></i>Notification</a> 
            </div>
        </div>

        <div class="content">
            <div class="content-header">
                <img src="styles/photos/DO-LOGO.png" width="70px" height="70px">
                <p>Learning and Development</p>
                <h1>EVENT MANAGEMENT SYSTEM</h1>
            </div><br><br><br><br><br>

            <div class="content-body">
                <h1>Events</h1>
                <hr><br><br>

                <div class="form-container">
                    <h3>CREATE AN EVENT</h3>
                    
                    <form id="create-event-form" method="POST">
                        <!-- Basic Event Details -->
                        <div class="form-group">
                            <div class="section-title">Basic Event Details</div>
                                
                            <label>Event Title:</label>
                            <input type="text" name="title" placeholder="Enter event title" required>
                                
                            <label>Specification of Event:</label>
                            <select name="specification" required>
                                <option value="">Select event specification</option>
                                <?php foreach ($specificationOptions as $option): ?>
                                    <option value="<?= $option ?>"><?= ucfirst($option) ?></option>
                                <?php endforeach; ?>
                            </select>
                                
                            <label>Delivery:</label>
                            <select id="event-mode" name="delivery" required>
                                <option value="">Select delivery</option>
                                <?php foreach ($deliveryOptions as $option): ?>
                                    <option value="<?= $option ?>"><?= ucfirst(str_replace('-', ' ', $option)) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <div id="venue-field">
                                <label>Venue:</label>
                                <input type="text" name="venue" placeholder="Enter venue" required>
                            </div>

                            <label>Funding Source:</label>
                            <div class="checkbox-group">
                                <?php foreach ($fundingOptions as $option): ?>
                                    <div class="funding-option">
                                        <label>
                                            <input type="checkbox" name="funding_source[]" value="<?= $option ?>" onchange="toggleAmountField('<?= $option ?>')">
                                            <?= ucfirst(str_replace('-', ' ', $option)) ?>
                                        </label>
                                        <div id="<?= $option ?>-amount" class="amount-field" style="display: none;">
                                            <label>Amount:</label>
                                            <input type="number" name="<?= $option ?>_amount" placeholder="Enter amount">
                                        </div>
                                    </div><br>
                                <?php endforeach; ?>
                            </div>

                            <div class="form-row">
                                <div class="form-col">
                                    <label for="start-date">Start Date:</label>
                                    <input type="date" id="start-date" name="start-date" required>
                                </div>

                                <div class="form-col">
                                    <label for="end-date">End Date:</label>
                                    <input type="date" id="end-date" name="end-date" required>
                                </div>
                            </div>
                            <div id="date-range-container" required></div>
                        </div>

                        <!-- Add this inside your form where appropriate -->
                        <div id="meal-plan-field" style="display: none;">
                            <label>Meal Plan</label>
                            <div id="meal-plan-container"></div>
                        </div>

                        <!-- Organizers & Trainers -->
                        <div class="form-group">
                            <div class="section-title">Organizers & Trainers</div>
                                
                            <label>Proponents:</label>
                            <input type="text" name="proponent" placeholder="Enter organizer name" required>
                                
                            <div id="speakers-container">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <label>Speaker/Resource Person:</label>
                                </div>
                                <div class="speaker-input-group">
                                    <input type="text" name="speaker[]" placeholder="Enter speaker/resource person">
                                    <button type="button" class="add-speaker-btn" onclick="addSpeakerField()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="section-title">Target Personnel</div>
                            <div class="personnel-selection">
                                <label>Target Participants: </label>
                                <select id="target-personnel" name="target_personnel" required>
                                    <option value="">Select target personnel</option>
                                    <?php foreach ($targetOptions as $option): ?>
                                        <option value="<?= $option ?>"><?= ucfirst($option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div id="school-personnel" class="checkbox-group" style="display: none;">
                            <label>School Level:</label>
                                <div class="checkbox-subgroup">
                                    <?php foreach ($schoolLevelOptions as $option): ?>
                                        <label><input type="checkbox" name="school_level[]" value="<?= $option['id'] ?>"> <?= ucfirst($option['name']) ?></label>
                                    <?php endforeach; ?>
                                </div>

                                <label>Type:</label>
                                <div class="checkbox-subgroup">
                                    <?php foreach ($typeOptions as $option): ?>
                                        <label><input type="checkbox" name="type[]" value="<?= $option['id'] ?>"> <?= ucfirst($option['name']) ?></label>
                                    <?php endforeach; ?>
                                </div>

                                <label>Specialization:</label>
                                <div class="checkbox-subgroup">
                                    <?php foreach ($specializationOptions as $option): ?>
                                        <label><input type="checkbox" name="specialization[]" value="<?= $option['id'] ?>"> <?= ucfirst($option['name']) ?></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div id="division-personnel" class="checkbox-group" style="display: none;">
                                <label>Unit/Department:</label>
                                <div class="checkbox-subgroup">
                                    <label><input type="checkbox" id="select-all-division" onclick="selectAllDivision()"> Select All</label>
                                    <?php foreach ($departmentOptions as $option): ?>
                                        <label><input type="checkbox" name="department[]" value="<?= $option ?>" class="division-checkbox"> <?= ucfirst($option) ?></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-group">
                            <button type="submit" class="submit-btn">Create Event</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>