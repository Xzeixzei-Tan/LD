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

// Fetch ENUM values for `school_level`, `type`, and `specialization`
$schoolLevelOptions = [];
$result = $conn->query("SHOW COLUMNS FROM school_participants LIKE 'school_level'");
if ($row = $result->fetch_assoc()) {
    preg_match("/^enum\((.*)\)$/", $row['Type'], $matches);
    $schoolLevelOptions = str_getcsv($matches[1], ",", "'");
}

$typeOptions = [];
$result = $conn->query("SHOW COLUMNS FROM school_participants LIKE 'type'");
if ($row = $result->fetch_assoc()) {
    preg_match("/^enum\((.*)\)$/", $row['Type'], $matches);
    $typeOptions = str_getcsv($matches[1], ",", "'");
}

$specializationOptions = [];
$result = $conn->query("SHOW COLUMNS FROM school_participants LIKE 'specialization'");
if ($row = $result->fetch_assoc()) {
    preg_match("/^enum\((.*)\)$/", $row['Type'], $matches);
    $specializationOptions = str_getcsv($matches[1], ",", "'");
}

// Fetch ENUM values for `department_name`
$departmentOptions = [];
$result = $conn->query("SHOW COLUMNS FROM division_participants LIKE 'department_name'");
if ($row = $result->fetch_assoc()) {
    preg_match("/^enum\((.*)\)$/", $row['Type'], $matches);
    $departmentOptions = str_getcsv($matches[1], ",", "'");
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
                    <form id="create-event-form" method="POST" action="admin-create_event_process.php">
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
                            <select id="event-mode" name="delivery" onchange="toggleVenueField(); toggleMealPlanField();" required>
                                <option value="">Select delivery</option>
                                <?php foreach ($deliveryOptions as $option): ?>
                                    <option value="<?= $option ?>"><?= ucfirst(str_replace('-', ' ', $option)) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <div id="venue-field">
                                <label>Venue:</label>
                                <input type="text" name="venue" placeholder="Enter venue">
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
                                    <input type="date" id="start-date" name="start-date">
                                </div>

                                <div class="form-col">
                                    <label for="end-date">End Date:</label>
                                    <input type="date" id="end-date" name="end-date">
                                </div>
                            </div>
                            <div id="date-range-container"></div>
                            <div id="same-time-checkbox-container"></div>
                        </div>

                        <!-- Add this inside your form where appropriate -->
                        <div id="meal-plan-field">
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
                                <button type="button" id="school-btn" class="personnel-btn active" onclick="showSchoolPersonnel()">
                                    <i class="fas fa-school"></i> School Personnel
                                </button>
                                <button type="button" id="division-btn" class="personnel-btn" onclick="showDivisionPersonnel()">
                                    <i class="fas fa-building"></i> Division Personnel
                                </button>
                                <button type="button" id="all-btn" class="personnel-btn" onclick="showAllPersonnel()">
                                    <i class="fas fa-users"></i> All Personnel
                                </button>
                            </div>

                            <div id="school-personnel" class="checkbox-group" style="display: none;">
                                <label>School Level:</label>
                                <?php foreach ($schoolLevelOptions as $option): ?>
                                    <label><input type="checkbox" name="school_level[]" value="<?= $option ?>"> <?= ucfirst($option) ?></label>
                                <?php endforeach; ?>
                                
                                <label>Type:</label>
                                <?php foreach ($typeOptions as $option): ?>
                                    <label><input type="checkbox" name="type[]" value="<?= $option ?>"> <?= ucfirst($option) ?></label>
                                <?php endforeach; ?>
                                
                                <label>Specialization:</label>
                                <?php foreach ($specializationOptions as $option): ?>
                                    <label><input type="checkbox" name="specialization[]" value="<?= $option ?>"> <?= ucfirst($option) ?></label>
                                <?php endforeach; ?>
                            </div>

                            <div id="division-personnel" class="checkbox-group" style="display: none;">
                            <label>Unit/Department:</label>
                            <label><input type="checkbox" id="select-all-division" onclick="selectAllDivision()"> Select All</label>
                                <?php foreach ($departmentOptions as $option): ?>
                                    <label><input type="checkbox" name="department[]" value="<?= $option ?>" class="division-checkbox"> <?= ucfirst($option) ?></label>
                                <?php endforeach; ?>
                            </div>

                            <div id="all-personnel" class="checkbox-group" style="display: none;">
                                <label><input type="checkbox" id="select-all-all" onclick="selectAllAll()"> Select All</label>
                                <label>School Level:</label>
                                <?php foreach ($schoolLevelOptions as $option): ?>
                                    <label><input type="checkbox" name="all_school_level[]" value="<?= $option ?>" class="all-checkbox"> <?= ucfirst($option) ?></label>
                                <?php endforeach; ?>
                                
                                <label>Type:</label>
                                <?php foreach ($typeOptions as $option): ?>
                                    <label><input type="checkbox" name="all_type[]" value="<?= $option ?>" class="all-checkbox"> <?= ucfirst($option) ?></label>
                                <?php endforeach; ?>
                                
                                <label>Specialization:</label>
                                <?php foreach ($specializationOptions as $option): ?>
                                    <label><input type="checkbox" name="all_specialization[]" value="<?= $option ?>" class="all-checkbox"> <?= ucfirst($option) ?></label>
                                <?php endforeach; ?>
                                
                                <label>Unit/Department:</label>
                                <?php foreach ($departmentOptions as $option): ?>
                                    <label><input type="checkbox" name="all_department[]" value="<?= $option ?>" class="all-checkbox"> <?= ucfirst($option) ?></label>
                                <?php endforeach; ?>
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
