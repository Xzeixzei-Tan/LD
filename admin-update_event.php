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

        // Create a notification for the update
        $notification_message = "Event updated: " . $title;
        $notification_sql = "INSERT INTO notifications (user_id, message, created_at, is_read, notification_type, notification_subtype, event_id) VALUES (?, ?, NOW(), 0, 'user', 'update_event', ?)";
        $notification_stmt = $conn->prepare($notification_sql);
        $user_id = $_SESSION['user_id'] ?? 1; // Default to admin if session not set
        $notification_stmt->bind_param("isi", $user_id, $notification_message, $eventId);
        $notification_stmt->execute();
        $notification_stmt->close();

        // Set success message
        $successMessage = "Event updated successfully!";
        
        // Redirect to the events page with success parameter
        header("Location: admin-events.php?success=update");
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
    <link href="styles/admin-create_events.css" rel="stylesheet">
    <script src="scripts/admin-create_events.js" defer></script>
    <title>Update Event</title>
    
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
        <h1>EVENT UPDATING</h1>
    </div>

    <div class="form-container">
        <h3>Update Event Details</h3>
        <?php if (!empty($successMessage)): ?>
            <div class="success-message" style="display: block;"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <!-- General Information -->
            <div class="form-group">
                <div class="section-title">General Information</div>
                <label for="title">Event Title:</label>
                <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($eventData['title']); ?>">
                
                <label for="specification">Event Specification:</label>
                <select id="specification" name="specification" required>
                    <?php foreach ($specificationOptions as $option): ?>
                        <option value="<?php echo htmlspecialchars($option); ?>" <?php echo ($eventData['specification'] === $option) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($option); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <label for="delivery">Delivery Method:</label>
                <select id="delivery" name="delivery" required>
                    <?php foreach ($deliveryOptions as $option): ?>
                        <option value="<?php echo htmlspecialchars($option); ?>" <?php echo ($eventData['delivery'] === $option) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($option); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <div id="venue-container" style="<?php echo ($eventData['delivery'] === 'Face-to-Face' || $eventData['delivery'] === 'Blended') ? 'display:block;' : 'display:none;'; ?>">
                    <label for="venue">Venue:</label>
                    <input type="text" id="venue" name="venue" value="<?php echo htmlspecialchars($eventData['venue']); ?>">
                </div>
                
                <label for="proponent">Proponent:</label>
                <input type="text" id="proponent" name="proponent" required value="<?php echo htmlspecialchars($eventData['proponent']); ?>">
                
                <div class="form-row">
                    <div class="form-col">
                        <label for="start-date">Start Date:</label>
                        <input type="date" id="start-date" name="start-date" required value="<?php echo htmlspecialchars($eventData['start_date']); ?>">
                    </div>
                    <div class="form-col">
                        <label for="end-date">End Date:</label>
                        <input type="date" id="end-date" name="end-date" required value="<?php echo htmlspecialchars($eventData['end_date']); ?>">
                    </div>
                </div>
                
                <label for="estimated-participants">Estimated Number of Participants:</label>
                <input type="number" id="estimated-participants" name="estimated_participants" min="1" required value="<?php echo htmlspecialchars($estimatedParticipants); ?>">
            </div>

            <!-- Event Days -->
            <div class="form-group">
                <div class="section-title">Event Days</div>
                <div class="date-notice">Please ensure that the dates fall within the start and end dates you specified above.</div>
                <div id="event-days-container">
                    <?php foreach ($eventDays as $index => $day): ?>
                        <div class="event-day">
                            <h4>Day <?php echo $day['day_number']; ?></h4>
                            <input type="hidden" name="event_days[<?php echo $day['day_number']; ?>][day_number]" value="<?php echo $day['day_number']; ?>">
                            
                            <label for="day-date-<?php echo $day['day_number']; ?>">Date:</label>
                            <input type="date" id="day-date-<?php echo $day['day_number']; ?>" name="event_days[<?php echo $day['day_number']; ?>][date]" required value="<?php echo htmlspecialchars($day['day_date']); ?>">
                            
                            <div class="time-inputs">
                                <div>
                                    <label for="start-time-<?php echo $day['day_number']; ?>">Start Time:</label>
                                    <input type="time" id="start-time-<?php echo $day['day_number']; ?>" name="event_days[<?php echo $day['day_number']; ?>][start_time]" required value="<?php echo htmlspecialchars($day['start_time']); ?>">
                                </div>
                                <div>
                                    <label for="end-time-<?php echo $day['day_number']; ?>">End Time:</label>
                                    <input type="time" id="end-time-<?php echo $day['day_number']; ?>" name="event_days[<?php echo $day['day_number']; ?>][end_time]" required value="<?php echo htmlspecialchars($day['end_time']); ?>">
                                </div>
                            </div>
                            <?php if ($index > 0): ?>
                                <button type="button" class="remove-day-btn"><i class="fas fa-times"></i></button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="add-day-btn" class="add-btn">Add Day</button>
            </div>
            
            <!-- Meal Plan -->
            <div class="form-group">
                <div class="section-title">Meal Plan</div>
                <div id="meal-plans-container">
                    <?php foreach ($eventDays as $day): ?>
                        <div class="meal-day" data-day="<?php echo $day['day_number']; ?>">
                            <h4>Day <?php echo $day['day_number']; ?> - <?php echo date('F j, Y', strtotime($day['day_date'])); ?></h4>
                            <div class="checkbox-subgroup">
                                <?php
                                $dayMeals = isset($mealPlans[$day['day_date']]) ? $mealPlans[$day['day_date']] : [];
                                $mealOptions = ['Breakfast', 'AM Snack', 'Lunch', 'PM Snack', 'Dinner'];
                                foreach ($mealOptions as $meal):
                                ?>
                                    <label>
                                        <input type="checkbox" name="meal_plan[<?php echo $day['day_number']; ?>][]" value="<?php echo $meal; ?>" 
                                            <?php echo (is_array($dayMeals) && in_array($meal, $dayMeals)) ? 'checked' : ''; ?>>
                                        <?php echo $meal; ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

                        <!-- Funding Sources -->
                        <div class="form-group">
                <div class="section-title">Funding Sources</div>
                <div class="funding-options-row">
                    <?php foreach ($fundingOptions as $option): ?>
                        <div class="funding-option">
                            <label>
                                <input type="checkbox" name="funding_source[]" value="<?php echo htmlspecialchars($option); ?>" 
                                    <?php echo (isset($fundingSources[$option])) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($option); ?>
                            </label>
                            <div class="amount-field" <?php echo (isset($fundingSources[$option])) ? 'style="display:block;"' : 'style="display:none;"'; ?>>
                                <label for="<?php echo htmlspecialchars($option); ?>_amount">Amount (₱):</label>
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
            </div>
            
            <!-- Speakers -->
            <div class="form-group">
                <div class="section-title">Speakers</div>
                <div id="speakers-container">
                    <?php if (!empty($speakers)): ?>
                        <?php foreach ($speakers as $index => $speaker): ?>
                            <div class="speaker-input">
                                <input type="text" name="speaker[]" value="<?php echo htmlspecialchars($speaker); ?>" placeholder="Enter speaker name">
                                <?php if ($index > 0): ?>
                                    <button type="button" class="remove-speaker-btn"><i class="fas fa-times"></i></button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="speaker-input">
                            <input type="text" name="speaker[]" placeholder="Enter speaker name">
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" id="add-speaker-btn" class="add-btn">Add Speaker</button>
            </div>
            
            <!-- Target Personnel -->
            <div class="form-group">
                <div class="section-title">Target Personnel</div>
                <div class="personnel-selection">
                    <label for="target-personnel">Target Audience:</label>
                    <select id="target-personnel" name="target_personnel" class="target" required>
                        <option value="">Select Target</option>
                        <?php foreach ($targetOptions as $option): ?>
                            <option value="<?php echo htmlspecialchars($option); ?>" 
                                <?php echo isset($targetParticipant['target']) && $targetParticipant['target'] === $option ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($option); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- School Personnel Options -->
                <div id="school-personnel" style="<?php echo (isset($targetParticipant['target']) && ($targetParticipant['target'] === 'School' || $targetParticipant['target'] === 'Both')) ? 'display:block;' : 'display:none;'; ?>">
                    <h4>School Level</h4>
                    <div class="checkbox-subgroup">
                        <?php foreach ($schoolLevelOptions as $option): ?>
                            <label>
                                <input type="checkbox" name="school_level[]" value="<?php echo $option['id']; ?>" 
                                    <?php echo (isset($targetParticipant['school_level']) && in_array($option['id'], $targetParticipant['school_level'])) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($option['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <h4>School Classification</h4>
                    <div class="checkbox-subgroup">
                        <?php foreach ($typeOptions as $option): ?>
                            <label>
                                <input type="checkbox" name="type[]" value="<?php echo $option['id']; ?>" 
                                    <?php echo (isset($targetParticipant['type']) && in_array($option['id'], $targetParticipant['type'])) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($option['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <h4>School Specialization</h4>
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
                
                <!-- Division Personnel Options -->
                <div id="division-personnel" style="<?php echo (isset($targetParticipant['target']) && ($targetParticipant['target'] === 'Division' || $targetParticipant['target'] === 'Both')) ? 'display:block;' : 'display:none;'; ?>">
                    <h4>Departments</h4>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delivery method controls venue field visibility
    const deliverySelect = document.getElementById('delivery');
    const venueContainer = document.getElementById('venue-container');
    
    deliverySelect.addEventListener('change', function() {
        if (this.value === 'Face-to-Face' || this.value === 'Blended') {
            venueContainer.style.display = 'block';
        } else {
            venueContainer.style.display = 'none';
        }
    });
    
    // Target personnel selection controls visibility of related sections
    const targetSelect = document.getElementById('target-personnel');
    const schoolOptions = document.getElementById('school-personnel');
    const divisionOptions = document.getElementById('division-personnel');
    
    targetSelect.addEventListener('change', function() {
        if (this.value === 'School') {
            schoolOptions.style.display = 'block';
            divisionOptions.style.display = 'none';
        } else if (this.value === 'Division') {
            schoolOptions.style.display = 'none';
            divisionOptions.style.display = 'block';
        } else if (this.value === 'Both') {
            schoolOptions.style.display = 'block';
            divisionOptions.style.display = 'block';
        } else {
            schoolOptions.style.display = 'none';
            divisionOptions.style.display = 'none';
        }
    });
    
    // Add event day button functionality
    const addDayBtn = document.getElementById('add-day-btn');
    const eventDaysContainer = document.getElementById('event-days-container');
    const mealPlansContainer = document.getElementById('meal-plans-container');
    
    // Find the highest day number to continue from
    let dayCount = 0;
    document.querySelectorAll('.event-day').forEach(function(dayElement) {
        const dayNumberInput = dayElement.querySelector('input[name$="[day_number]"]');
        if (dayNumberInput) {
            const currentDayNumber = parseInt(dayNumberInput.value);
            if (currentDayNumber > dayCount) {
                dayCount = currentDayNumber;
            }
        }
    });
    
    // Format date for display in a more readable format
    function formatDate(dateString) {
        if (!dateString) return 'Select date above';
        
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: 'long', 
            day: 'numeric', 
            year: 'numeric'
        });
    }
    
    // Update meal plan date display when event day date changes
    function updateMealPlanDate(dayNumber, dateValue) {
        const mealDay = document.querySelector(`.meal-day[data-day="${dayNumber}"]`);
        if (mealDay) {
            const heading = mealDay.querySelector('h4');
            if (heading) {
                heading.textContent = `Day ${dayNumber} - ${formatDate(dateValue)}`;
            }
        }
    }
    
    // Add a new event day
    addDayBtn.addEventListener('click', function() {
        dayCount++;
        
        // Create new event day element
        const newDay = document.createElement('div');
        newDay.className = 'event-day';
        newDay.innerHTML = `
            <h4>Day ${dayCount}</h4>
            <input type="hidden" name="event_days[${dayCount}][day_number]" value="${dayCount}">
            
            <label for="day-date-${dayCount}">Date:</label>
            <input type="date" id="day-date-${dayCount}" name="event_days[${dayCount}][date]" required>
            
            <div class="time-inputs">
                <div>
                    <label for="start-time-${dayCount}">Start Time:</label>
                    <input type="time" id="start-time-${dayCount}" name="event_days[${dayCount}][start_time]" required value="08:00">
                </div>
                <div>
                    <label for="end-time-${dayCount}">End Time:</label>
                    <input type="time" id="end-time-${dayCount}" name="event_days[${dayCount}][end_time]" required value="17:00">
                </div>
            </div>
            <button type="button" class="remove-day-btn"><i class="fas fa-times"></i></button>
        `;
        eventDaysContainer.appendChild(newDay);
        
        // Apply date constraints to the new day
        updateDateConstraints();
        
        // Create new meal plan for this day
        const newMealDay = document.createElement('div');
        newMealDay.className = 'meal-day';
        newMealDay.setAttribute('data-day', dayCount);
        newMealDay.innerHTML = `
            <h4>Day ${dayCount} - Select date above</h4>
            <div class="checkbox-subgroup">
                <label>
                    <input type="checkbox" name="meal_plan[${dayCount}][]" value="Breakfast">
                    Breakfast
                </label>
                <label>
                    <input type="checkbox" name="meal_plan[${dayCount}][]" value="AM Snack">
                    AM Snack
                </label>
                <label>
                    <input type="checkbox" name="meal_plan[${dayCount}][]" value="Lunch">
                    Lunch
                </label>
                <label>
                    <input type="checkbox" name="meal_plan[${dayCount}][]" value="PM Snack">
                    PM Snack
                </label>
                <label>
                    <input type="checkbox" name="meal_plan[${dayCount}][]" value="Dinner">
                    Dinner
                </label>
            </div>
        `;
        mealPlansContainer.appendChild(newMealDay);
        
        // Add event listener for the date input
        const dateInput = newDay.querySelector(`input[name="event_days[${dayCount}][date]"]`);
        dateInput.addEventListener('change', function() {
            updateMealPlanDate(dayCount, this.value);
        });
        
        // Add event listener for the remove button
        const removeBtn = newDay.querySelector('.remove-day-btn');
        removeBtn.addEventListener('click', function() {
            newDay.remove();
            // Remove corresponding meal plan
            const mealDay = document.querySelector(`.meal-day[data-day="${dayCount}"]`);
            if (mealDay) mealDay.remove();
        });
    });
    
    // Set up event listeners for existing event days
    document.querySelectorAll('.event-day').forEach(function(dayElement) {
        const dayNumberInput = dayElement.querySelector('input[name$="[day_number]"]');
        if (!dayNumberInput) return;
        
        const dayNumber = dayNumberInput.value;
        const dateInput = dayElement.querySelector(`input[name="event_days[${dayNumber}][date]"]`);
        
        if (dateInput) {
            // Add change listener to update meal plan date display
            dateInput.addEventListener('change', function() {
                updateMealPlanDate(dayNumber, this.value);
            });
            
            // Initialize meal plan date display
            updateMealPlanDate(dayNumber, dateInput.value);
        }
        
        // Set up remove button for existing day if it exists
        const removeBtn = dayElement.querySelector('.remove-day-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                dayElement.remove();
                // Remove corresponding meal plan
                const mealDay = document.querySelector(`.meal-day[data-day="${dayNumber}"]`);
                if (mealDay) mealDay.remove();
            });
        }
    });
    
    // Add speaker button functionality
    const addSpeakerBtn = document.getElementById('add-speaker-btn');
    const speakersContainer = document.getElementById('speakers-container');
    
    addSpeakerBtn.addEventListener('click', function() {
        const newSpeaker = document.createElement('div');
        newSpeaker.className = 'speaker-input';
        newSpeaker.innerHTML = `
            <input type="text" name="speaker[]" placeholder="Enter speaker name">
            <button type="button" class="remove-speaker-btn"><i class="fas fa-times"></i></button>
        `;
        speakersContainer.appendChild(newSpeaker);
        
        // Add event listener for the remove button
        const removeBtn = newSpeaker.querySelector('.remove-speaker-btn');
        removeBtn.addEventListener('click', function() {
            newSpeaker.remove();
        });
    });
    
    // Add remove functionality to existing speaker remove buttons
    document.querySelectorAll('.remove-speaker-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            this.closest('.speaker-input').remove();
        });
    });
    
    // Toggle funding amount fields based on checkbox selection
    document.querySelectorAll('input[name="funding_source[]"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const amountField = this.closest('.funding-option').querySelector('.amount-field');
            if (this.checked) {
                amountField.style.display = 'block';
            } else {
                amountField.style.display = 'none';
            }
        });
    });
    
    // Set min and max dates for event days based on start and end dates
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    
    function updateDateConstraints() {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        
        if (startDate && endDate) {
            document.querySelectorAll('input[name$="[date]"]').forEach(function(input) {
                input.min = startDate;
                input.max = endDate;
                
                // Check if current value is outside the new range
                if (input.value) {
                    const inputDate = new Date(input.value);
                    const minDate = new Date(startDate);
                    const maxDate = new Date(endDate);
                    
                    if (inputDate < minDate) {
                        input.value = startDate;
                        // Trigger change event to update meal plan
                        const event = new Event('change');
                        input.dispatchEvent(event);
                    } else if (inputDate > maxDate) {
                        input.value = endDate;
                        // Trigger change event to update meal plan
                        const event = new Event('change');
                        input.dispatchEvent(event);
                    }
                }
            });
        }
    }
    
    // Update date constraints when start or end date changes
    startDateInput.addEventListener('change', updateDateConstraints);
    endDateInput.addEventListener('change', updateDateConstraints);
    
    // Initialize date constraints on page load
    updateDateConstraints();
});
    
    // Initialize existing remove day buttons
    document.querySelectorAll('.remove-day-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const dayElement = this.closest('.event-day');
            const dayNumber = dayElement.querySelector('input[name$="[day_number]"]').value;
            dayElement.remove();
            
            // Remove corresponding meal plan
            const mealDay = document.querySelector(`.meal-day[data-day="${dayNumber}"]`);
            if (mealDay) mealDay.remove();
        });
    });
});
        
        // Add meal plan for a specific day
        function addMealPlanForDay(dayNumber) {
            const mealPlansContainer = document.getElementById('meal-plans-container');
            const newMealDay = document.createElement('div');
            newMealDay.className = 'meal-day';
            newMealDay.setAttribute('data-day', dayNumber);
            newMealDay.innerHTML = `
                <h4>Day ${dayNumber} - <span class="date-placeholder">Select date above</span></h4>
                <div class="checkbox-subgroup">
                    <label>
                        <input type="checkbox" name="meal_plan[${dayNumber}][]" value="Breakfast">
                        Breakfast
                    </label>
                    <label>
                        <input type="checkbox" name="meal_plan[${dayNumber}][]" value="AM Snack">
                        AM Snack
                    </label>
                    <label>
                        <input type="checkbox" name="meal_plan[${dayNumber}][]" value="Lunch">
                        Lunch
                    </label>
                    <label>
                        <input type="checkbox" name="meal_plan[${dayNumber}][]" value="PM Snack">
                        PM Snack
                    </label>
                    <label>
                        <input type="checkbox" name="meal_plan[${dayNumber}][]" value="Dinner">
                        Dinner
                    </label>
                </div>
            `;
            mealPlansContainer.appendChild(newMealDay);
            
            // Update meal plan date when event day date changes
            // Update meal plan date display when event day date changes
function updateMealPlanDate(dayNumber, dateValue) {
    const mealDay = document.querySelector(`.meal-day[data-day="${dayNumber}"]`);
    if (mealDay) {
        const heading = mealDay.querySelector('h4');
        if (heading && dateValue) {
            const formattedDate = new Date(dateValue).toLocaleDateString('en-US', {
                month: 'long', 
                day: 'numeric', 
                year: 'numeric'
            });
            heading.textContent = `Day ${dayNumber} - ${formattedDate}`;
        } else if (heading) {
            heading.textContent = `Day ${dayNumber} - Select date above`;
        }
    }
}

// Set up event listeners for existing event days
document.querySelectorAll('.event-day').forEach(function(dayElement) {
    const dayNumberInput = dayElement.querySelector('input[name$="[day_number]"]');
    if (!dayNumberInput) return;
    
    const dayNumber = dayNumberInput.value;
    const dateInput = dayElement.querySelector(`input[name="event_days[${dayNumber}][date]"]`);
    
    if (dateInput) {
        // Ensure this event listener is only added once
        dateInput.addEventListener('change', function() {
            updateMealPlanDate(dayNumber, this.value);
        });
        
        // Initialize meal plan date display with current value
        updateMealPlanDate(dayNumber, dateInput.value);
    }
});
        
        // Add speaker button functionality
        const addSpeakerBtn = document.getElementById('add-speaker-btn');
        const speakersContainer = document.getElementById('speakers-container');
        
        addSpeakerBtn.addEventListener('click', function() {
            const newSpeaker = document.createElement('div');
            newSpeaker.className = 'speaker-input';
            newSpeaker.innerHTML = `
                <input type="text" name="speaker[]" placeholder="Enter speaker name">
                <button type="button" class="remove-speaker-btn"><i class="fas fa-times"></i></button>
            `;
            speakersContainer.appendChild(newSpeaker);
            
            // Add event listener for the remove button
            const removeBtn = newSpeaker.querySelector('.remove-speaker-btn');
            removeBtn.addEventListener('click', function() {
                newSpeaker.remove();
            });
        });
        
        // Add remove functionality to existing speaker remove buttons
        document.querySelectorAll('.remove-speaker-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                this.closest('.speaker-input').remove();
            });
        });
        
        // Toggle funding amount fields based on checkbox selection
        document.querySelectorAll('input[name="funding_source[]"]').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const amountField = this.closest('.funding-option').querySelector('.amount-field');
                if (this.checked) {
                    amountField.style.display = 'block';
                } else {
                    amountField.style.display = 'none';
                }
            });
        });
        
        // Set min and max dates for event days based on start and end dates
        const startDateInput = document.getElementById('start-date');
        const endDateInput = document.getElementById('end-date');
        
        function updateDateConstraints() {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;
            
            if (startDate && endDate) {
                document.querySelectorAll('input[name$="[date]"]').forEach(function(input) {
                    input.min = startDate;
                    input.max = endDate;
                });
            }
        }
        
        startDateInput.addEventListener('change', updateDateConstraints);
        endDateInput.addEventListener('change', updateDateConstraints);
        
        // Initialize date constraints on page load
        updateDateConstraints();
        
        // Initialize existing remove day buttons
        document.querySelectorAll('.remove-day-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const dayElement = this.closest('.event-day');
                const dayNumber = dayElement.querySelector('input[name$="[day_number]"]').value;
                dayElement.remove();
                
                // Remove corresponding meal plan
                const mealDay = document.querySelector(`.meal-day[data-day="${dayNumber}"]`);
                if (mealDay) mealDay.remove();
            });
        });
    });
</script>

</div>
</body>
</html>