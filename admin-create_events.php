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

// Fetch meal plan data if event ID is available
// This would typically be used when editing an existing event
$mealTypesArray = [];
if (isset($_GET['id'])) {
    $eventId = (int)$_GET['id'];
    $result = $conn->query("SELECT * FROM meal_plan WHERE event_id = $eventId");
    while ($row = $result->fetch_assoc()) {
        // Get the raw JSON value and remove quotes
        $jsonValue = json_decode($row['meal_types']);
        
        // If the JSON value is a string, split it into an array
        $mealTypesArray = explode(', ', $jsonValue);
        
        // Now you can work with $mealTypesArray as a normal PHP array
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
    $createdAt = date('Y-m-d H:i:s');
    $updatedAt = date('Y-m-d H:i:s');

    // Insert data into the events table
    $stmt = $conn->prepare("INSERT INTO events (title, specification, delivery, venue, start_date, end_date, proponent, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $title, $specification, $delivery, $venue, $startDate, $endDate, $proponent, $createdAt, $updatedAt);
    $stmt->execute();
    $eventId = $stmt->insert_id;
    $stmt->close();

    // Insert event days into the event_days table
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
    // Insert meal plans into the meal_plan table
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

    $user_notification_message = "New event created. Click for more info: " . $title;
    $user_notification_sql = "INSERT INTO notifications (user_id, message, created_at, is_read, notification_type, notification_subtype, event_id) VALUES (?, ?, NOW(), 0, 'user', 'new_event', ?)";
    $user_notification_stmt = $conn->prepare($user_notification_sql);
    $user_notification_stmt->bind_param("isi", $user_id, $user_notification_message, $eventId);
    $user_notification_stmt->execute();
    $user_notification_stmt->close();

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
        .remove-speaker-btn{
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 50%;
            padding: 7px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
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
            /* Row layout for funding options */
    .funding-options-row {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 15px;
        padding: 10px;
    }

    .funding-option {
        flex: 0 0 auto;
        min-width: 200px;
        margin-bottom: 15px;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        transition: all 0.2s ease;
        background-color: #fff;
    }

    .funding-option:hover {
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .funding-option label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
        cursor: pointer;
        background-color: #fff;
    }

    .funding-option input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        margiin-left: 5px;s
    }

    /* Style for the amount field */
    .amount-field.show {
        animation: slideDown 0.3s ease forwards;
    }

    .amount-field label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        color: #4CAF50;
        font-size: 14px;
    }

    .amount-field .input-with-symbol {
        position: relative;
        display: flex;
        align-items: center;
    }

    .amount-field .currency-symbol {
        position: absolute;
        left: 12px;
        top: 35%;
        transform: translateY(-50%);
        color: #666;
        font-weight: 500;
    }

    .amount-field input[type="number"] {
        width: 100%;
        padding: 8px 12px 8px 30px;
        border-radius: 4px;
        font-size: 15px;
    
    }

    .amount-field input[type="number"]:focus {
        outline: none;
        box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .funding-options-row {
            flex-direction: column;
            gap: 10px;
            padding: 5px;
        }
        
        .funding-option {
            width: 100%;
            min-width: auto;
            padding: 15px;
        }
    }
    
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .section-title {
            font-family: Montserrat ExtraBold;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            color: black;
        }
        
        .meal-day {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .event-day {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .time-inputs {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }

        .time-inputs div {
            flex: 1;
        }

        .time-inputs label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            font-size: 14px;
            color: #555;
        }

        .time-inputs input[type="time"] {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
            cursor: pointer;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .time-inputs input[type="time"]:hover {
            border-color: #aaa;
        }

        .time-inputs input[type="time"]:focus {
            border-color: #4CAF50;
            outline: none;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
        }

        .time-inputs input[type="time"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            opacity: 0.8;
        }

        .time-inputs input[type="time"]::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .form-col {
            flex: 1;
        }

        .form-col label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form-col input[type="date"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
            cursor: pointer;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-col input[type="date"]:hover {
            border-color: #aaa;
        }

        .form-col input[type="date"]:focus {
            border-color: #4CAF 
        }

        .meal-day {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            
        }

        .meal-day h4 {
            
            margin-top: -0%;
            color: #2b3a8f;
            font-size: 16px;
            padding-bottom: 8px;
           
        
        }

        .specializations-1{
            display: flex;
        }

        /* Adjust the Target Personnel section layout */
.form-group .section-title {
    margin-bottom: 1rem;
}

.date-notice {
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 6px;
    border: 1px dashed #ddd;
    color: #6c757d;
    text-align: center;
    font-family: Montserrat;
    font-style: italic;
    margin: 15px 0;
}

.personnel-selection {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.personnel-selection label {
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.target {
    width: 100%;
}

#target-personnel {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 5px;
    font-family: Montserrat Light;
    margin-top: 0rem;
}

/* Style the dropdown options */
#target-personnel option {
    padding: 0.5rem;
    font-family: Montserrat Light;
}



       /* For the specialization sections - making them two rows */
.checkbox-subgroup {
    background-color: white;
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    padding: 15px;
}

.checkbox-subgroup label {
    display: flex;
    align-items: center;
    padding: 12px;
    border-radius: 4px;
    margin: 0;
    transition: all 0.2s ease;
    min-width: 120px;
    width: calc(50% - 10px); /* Make each item take up half the width minus the gap */
    box-sizing: border-box;
    background-color: white;
}

.checkbox-subgroup input[type="checkbox"] {
    margin-right: 8px;
}

/* Specific layout adjustments for division personnel section */
#division-personnel .checkbox-subgroup {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

#division-personnel .checkbox-subgroup label {
    width: 100%;
}

/* For school personnel sections */
#school-personnel .checkbox-subgroup {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

#school-personnel .checkbox-subgroup label {
    width: 100%;
}

/* Keep meal plan styles intact */
.meal-day {
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 15px;
}

.meal-day h4 {
    margin-top: 0;
    margin-bottom: -2%;
    color: #2b3a8f;
    font-size: 16px;
    padding-bottom: 8px;
}

.meal-day  {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    gap: 10px;
}

.meal-day .checkbox-subgroup label {
    width: auto;
    min-width: 100px;
    flex-grow: 1;
}

/* Improved styling for form sections */
.form-group {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.form-group {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.section-title {
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 1.2rem;
    margin-bottom: 1.2rem;
    padding-bottom: 0.7rem;
    color: #2b3a8f;
    
}

/* Improved input styling */
input[type="text"], 
input[type="number"], 
input[type="date"],
input[type="time"],
select {
    border: 1px solid #ddd;
    padding: 12px;
    border-radius: 6px;
    background-color: white;
    width: 100%;
    box-sizing: border-box;
    transition: border-color 0.3s, box-shadow 0.3s;
    margin-bottom: 15px;
}

input[type="text"]:focus, 
input[type="number"]:focus, 
input[type="date"]:focus,
input[type="time"]:focus,
select:focus {
    border-color: #2b3a8f;
    outline: none;
    box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
}

/* Better responsive layout */
@media (max-width: 768px) {
    #school-personnel 
    #division-personnel .checkbox-subgroup {
        grid-template-columns: 1fr;
        padding: 10px;
        
    }
    .checkbox-subgroup {
    background-color: white;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 5px;
}
    
     label {
        width: 100%;
        font-family: Montserrat Medium;
        
    }
    .check-subgroup label {
        width: 100%;
        
    }
    
    .form-row {
        flex-direction: column;
    }
}

        /* Target all section titles for consistent styling */
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: black;
            padding-bottom: 8px;
           
        }

        /* Consistent styling for event days */
        .event-day {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #ddd;
            background-color: white;
        }

        .event-day h4 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #2b3a8f;
            font-size: 16px;
            padding-bottom: 8px;
           
        }
        .submit-btn {
            font-family: Montserrat ExtraBold;
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            width: 60%;
            transition: background-color 0.3s;
            margin-top: 20px;
            margin-left: auto;
            margin-right: auto;
            display: block;
        }

        .submit-btn:hover {
            background-color: #45a049;
        }

        .submit-btn:active {
            background-color: #3e8e41;
            transform: translateY(1px);
        }
        .form-container h3{
            color: #4CAF50;
            font-family: Montserrat ExtraBold;
     }

     .form-group h4 {
        margin-bottom: 10px;
     }
     input[type=number] {
        font-family: Montserrat Light; 
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
                                
                            <h4>Event Title:</h4>
                            <input type="text" name="title" placeholder="Enter event title" required>
                                
                            <h4>Specification of Event:</h4>
                            <select name="specification" required>
                                <option value="">Select event specification</option>
                                <?php foreach ($specificationOptions as $option): ?>
                                    <option value="<?= $option ?>"><?= ucfirst($option) ?></option>
                                <?php endforeach; ?>
                            </select>
                                
                            <h4>Delivery:</h4>
                            <select id="event-mode" name="delivery" required>
                                <option value="">Select delivery</option>
                                <?php foreach ($deliveryOptions as $option): ?>
                                    <option value="<?= $option ?>"><?= ucfirst(str_replace('-', ' ', $option)) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <div id="venue-field">
                                <h4>Venue:</h4>
                                <input type="text" name="venue" placeholder="Enter venue" required>
                            </div>

                            <h4>Funding Source:</h4>
                            <div class="checkbox-group">
                                <div class="funding-options-row">
                                    <?php foreach ($fundingOptions as $option): ?>
                                        <div class="funding-option">
                                            <label>
                                                <input type="checkbox" name="funding_source[]" value="<?= $option ?>" onchange="toggleAmountField('<?= $option ?>')">
                                                <?= ucfirst(str_replace('-', ' ', $option)) ?>
                                            </label>
                                            <div id="<?= $option ?>-amount" class="amount-field" style="display: none;">
                                                <h4>Amount:</h4>
                                                <div class="input-with-symbol">
                                                    <span class="currency-symbol">₱</span>
                                                    <input type="number" name="<?= $option ?>_amount" placeholder="Enter amount">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-col">
                                    <h4 for="start-date">Start Date:</h4>
                                    <input type="date" id="start-date" name="start-date" required onchange="generateDayFields()">
                                </div>

                                <div class="form-col">
                                    <h4 for="end-date">End Date:</h4>
                                    <input type="date" id="end-date" name="end-date" required onchange="generateDayFields()">
                                </div>
                            </div>
                            
                            <!-- Event Days Section -->
                            <div class="section-title">Event Days Schedule</div>
                            <div id="event-days-container"></div>
                        </div>

                        <!-- INSERT MEAL PLAN SECTION HERE -->
                        <?php
                        // Meal Plan section of your form
                        echo '<div class="form-group">';
                        echo '<div class="section-title">Meal Plan</div>';
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

                        
                        <!-- Add this code inside the meal plan form-group section, right before the closing </div> -->
                        <div class="form-group">
                            <div class="form-col">
                                <h4>No. of Estimated Participants:</h4>
                                <input type="number" id="estimated-participants" name="estimated_participants" min="1" placeholder="Enter estimated number of participants" required>
                            </div>
                        </div>
                        <!-- Organizers & Trainers -->
                        <div class="form-group">
                            <div class="section-title">Organizers & Trainers</div>
                                
                            <h4>Proponents:</h4>
                            <input type="text" name="proponent" placeholder="Enter organizer name" required>
                                
                            <div id="speakers-container">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <h4>Speaker/Resource Person:</h4>
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
                                <h4>Target Participants: </h4>
                                <div class="target">
                                <select id="target-personnel" name="target_personnel" required>
                                    <option value="">Select target personnel</option>
                                    <?php foreach ($targetOptions as $option): ?>
                                        <option value="<?= $option ?>"><?= ucfirst($option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                                    </div>

                            <div id="school-personnel" class="checkbox-group" style="display: none;">
                            <h4>School Level:</h4> <br>
                                <div class="checkbox-subgroup">
                                    <?php foreach ($schoolLevelOptions as $option): ?>
                                        <label><input type="checkbox" name="school_level[]" value="<?= $option['id'] ?>"> <?= ucfirst($option['name']) ?></label>
                                    <?php endforeach; ?>
                                </div>
                                <br>
                                <h4>Type:</h4> <br>
                                <div class="checkbox-subgroup">
                                    <?php foreach ($typeOptions as $option): ?>
                                        <label><input type="checkbox" name="type[]" value="<?= $option['id'] ?>"> <?= ucfirst($option['name']) ?></label>
                                    <?php endforeach; ?>
                                </div>
                                <br>
                                <h4>Specialization:</h4> <br>
                                <div class="checkbox-subgroup">
                                    <?php foreach ($specializationOptions as $option): ?>
                                        <label><input type="checkbox" name="specialization[]" value="<?= $option['id'] ?>"> <?= ucfirst($option['name']) ?></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <br>
                            <div id="division-personnel" class="checkbox-group" style="display: none;">
                            <h4>Unit/Department:</h4> <br>
                                <div class="checkbox-subgroup">
                                    <label><input type="checkbox" id="select-all-division" onclick="selectAllDivision()"> Select All</label>
                                    <?php foreach ($departmentOptions as $option): ?>
                                        <label><input type="checkbox" name="department[]" value="<?= $option ?>" class="division-checkbox"> <?= ucfirst($option) ?></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                            <button type="submit" class="submit-btn">Create Event</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script>
// Function to toggle amount field visibility
function toggleAmountField(source) {
    const amountField = document.getElementById(source + '-amount');
    const checkbox = document.querySelector(`input[name="funding_source[]"][value="${source}"]`);
    if (checkbox.checked) {
        amountField.style.display = 'block';
    } else {
        amountField.style.display = 'none';
    }
}

// Function to add speaker field
function addSpeakerField() {
    const container = document.getElementById('speakers-container');
    const newGroup = document.createElement('div');
    newGroup.className = 'speaker-input-group';
    newGroup.innerHTML = `
        <input type="text" name="speaker[]" placeholder="Enter speaker/resource person">
        <button type="button" class="remove-speaker-btn" onclick="removeSpeakerField(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(newGroup);
}

// Function to remove speaker field
function removeSpeakerField(button) {
    const group = button.parentElement;
    group.remove();
}

// Function to select all division checkboxes
function selectAllDivision() {
    const selectAll = document.getElementById('select-all-division');
    const checkboxes = document.querySelectorAll('.division-checkbox');
    checkboxes.forEach((checkbox) => {
        checkbox.checked = selectAll.checked;
    });
}

// Function to generate day fields based on start and end dates
function generateDayFields() {
    const startDate = new Date(document.getElementById('start-date').value);
    const endDate = new Date(document.getElementById('end-date').value);
    
    const eventDaysContainer = document.getElementById('event-days-container');
    const mealPlanContainer = document.getElementById('meal-plan-container');
    
    // Clear containers
    eventDaysContainer.innerHTML = '';
    mealPlanContainer.innerHTML = '';
    
    // Check if dates are valid
    if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
        // Show message when dates are not set
        const dateNotice = '<div class="date-notice">Please set up Start and End Date first.</div>';
        eventDaysContainer.innerHTML = dateNotice;
        mealPlanContainer.innerHTML = dateNotice;
        return;
    }
    
    // Calculate the difference in days
    const dayDiff = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
    
    if (dayDiff <= 0) {
        const errorNotice = '<div class="date-notice">End date must be after start date.</div>';
        eventDaysContainer.innerHTML = errorNotice;
        mealPlanContainer.innerHTML = errorNotice;
        return;
    }
    
    // Create day fields for each day in the range
    for (let i = 0; i < dayDiff; i++) {
        const currentDate = new Date(startDate);
        currentDate.setDate(startDate.getDate() + i);
        const dateString = currentDate.toISOString().split('T')[0];
        const dayNumber = i + 1;
        
        // Create event day field
        const dayDiv = document.createElement('div');
        dayDiv.className = 'event-day';
        dayDiv.innerHTML = `
            <h4>Day ${dayNumber} - ${dateString}</h4>
            <input type="hidden" name="event_days[${dayNumber}][date]" value="${dateString}">
            <div class="time-inputs">
                <div>
                    <label>Start Time:</label>
                    <input type="time" name="event_days[${dayNumber}][start_time]" value="08:00">
                </div>
                <div>
                    <label>End Time:</label>
                    <input type="time" name="event_days[${dayNumber}][end_time]" value="17:00">
                </div>
            </div>
        `;
        eventDaysContainer.appendChild(dayDiv);
        
        // Create meal plan field for this day
        const mealDiv = document.createElement('div');
        mealDiv.className = 'meal-day';
        mealDiv.innerHTML = `
            <h4>Meals for Day ${dayNumber} - ${dateString}</h4>
            <div class="checkbox-subgroup">
                <label><input type="checkbox" name="meal_plan[${dayNumber}][]" value="Breakfast"> Breakfast</label>
                <label><input type="checkbox" name="meal_plan[${dayNumber}][]" value="AM Snack"> AM Snack</label>
                <label><input type="checkbox" name="meal_plan[${dayNumber}][]" value="Lunch"> Lunch</label>
                <label><input type="checkbox" name="meal_plan[${dayNumber}][]" value="PM Snack"> PM Snack</label>
                <label><input type="checkbox" name="meal_plan[${dayNumber}][]" value="Dinner"> Dinner</label>
            </div>
        `;
        mealPlanContainer.appendChild(mealDiv);
    }
}

// Initialize the page when DOM content is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Make sure day containers have default message when page loads
    const eventDaysContainer = document.getElementById('event-days-container');
    const mealPlanContainer = document.getElementById('meal-plan-container');
    
    if (eventDaysContainer) {
        // Add default notice if no dates are set
        if (!document.getElementById('start-date').value || !document.getElementById('end-date').value) {
            eventDaysContainer.innerHTML = '<div class="date-notice">Please set up Start and End Date first.</div>';
        }
    }
    
    if (mealPlanContainer) {
        // Add default notice if no dates are set
        if (!document.getElementById('start-date').value || !document.getElementById('end-date').value) {
            mealPlanContainer.innerHTML = '<div class="date-notice">Please set up Start and End Date first.</div>';
        }
    }
    
    // Setup event listeners
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    
    if (startDateInput) {
        startDateInput.addEventListener('change', generateDayFields);
    }
    
    if (endDateInput) {
        endDateInput.addEventListener('change', generateDayFields);
    }
    
    // Event listeners for delivery mode and target personnel
    const deliverySelect = document.getElementById('event-mode');
    if (deliverySelect) {
        deliverySelect.addEventListener('change', function() {
            const venueField = document.getElementById('venue-field');
            if (this.value === 'online') {
                venueField.style.display = 'none';
                venueField.querySelector('input').required = false;
            } else {
                venueField.style.display = 'block';
                venueField.querySelector('input').required = true;
            }
        });
        
        // Initialize venue field based on current delivery selection
        if (deliverySelect.value === 'online') {
            const venueField = document.getElementById('venue-field');
            if (venueField) {
                venueField.style.display = 'none';
                venueField.querySelector('input').required = false;
            }
        }
    }
    
    const targetSelect = document.getElementById('target-personnel');
    if (targetSelect) {
        targetSelect.addEventListener('change', function() {
            const target = this.value;
            const schoolSection = document.getElementById('school-personnel');
            const divisionSection = document.getElementById('division-personnel');
            
            schoolSection.style.display = (target === 'School' || target === 'Both') ? 'block' : 'none';
            divisionSection.style.display = (target === 'Division' || target === 'Both') ? 'block' : 'none';
        });
        
        // Initialize sections based on current selection
        const target = targetSelect.value;
        const schoolSection = document.getElementById('school-personnel');
        const divisionSection = document.getElementById('division-personnel');
        
        if (schoolSection) {
            schoolSection.style.display = (target === 'School' || target === 'Both') ? 'block' : 'none';
        }
        
        if (divisionSection) {
            divisionSection.style.display = (target === 'Division' || target === 'Both') ? 'block' : 'none';
        }
    }
});

// Make sure the date inputs trigger the generation of day fields
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    
    if (startDateInput) {
        startDateInput.addEventListener('change', generateDayFields);
    }
    
    if (endDateInput) {
        endDateInput.addEventListener('change', generateDayFields);
    }
});
    </script>
</body>
</html>