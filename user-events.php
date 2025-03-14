<?php
require_once 'config.php';

// Start the session
session_start();

// Display session messages if any exist
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-info">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']); // Clear the message after displaying
}

// Get the user ID from session
$user_id = $_SESSION['user_id'];

// Check if an event ID is specified in the URL
$selected_event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : null;

// Determine which tab is active (default to "Unregistered" if not specified)
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'unregistered';

// Fetch all events from the database
$sql = "SELECT e.id, e.title, e.start_datetime, e.end_datetime, e.venue, e.event_specification, e.delivery, e.organizer_name,
               (SELECT COUNT(*) FROM registered_users ru WHERE ru.event_id = e.id AND ru.user_id = ?) AS is_registered,
               CASE 
                   WHEN NOW() BETWEEN e.start_datetime AND e.end_datetime THEN 'Ongoing'
                   WHEN NOW() < e.start_datetime THEN 'Upcoming'
                   ELSE 'Past'
               END AS status
        FROM events e  
        ORDER BY e.start_datetime DESC";
$stmt = $conn->prepare($sql);

// Check if the prepare statement was successful
if ($stmt === false) {
    echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    exit();
}

$stmt->bind_param("i", $user_id); // FIXED: Use $user_id instead of $current_user_id
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    // Log the error and display a user-friendly message
    error_log("Query failed in user-events.php: " . $conn->error);
    die("There was a problem loading the events. Please try again later.");
}

$user_sql = "SELECT first_name, last_name FROM users WHERE id = ?";
                
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows > 0) {
    $row = $user_result->fetch_assoc();
    $first_name = $row['first_name'];
    $last_name = $row['last_name'];
    $_SESSION['first_name'] = $first_name; // Update the session
    $_SESSION['last_name'] = $last_name; 
} else {
    $first_name = "Unknown";
    $last_name = ""; // Set a default value
    $_SESSION['first_name'] = $first_name;
    $_SESSION['last_name'] = $last_name;
}

// Separate events into registered and unregistered
$registered_events = [];
$unregistered_events = [];

while ($row = $result->fetch_assoc()) {
    if ($row['is_registered'] > 0) {
        $registered_events[] = $row;
    } else {
        $unregistered_events[] = $row;
    }
}

// If an event is selected, fetch its details and speakers
$selected_event = null;
$speakers = [];
$is_registered = false;

if ($selected_event_id) {
    // Fetch event details
    $detail_sql = "SELECT e.*, 
                  (SELECT COUNT(*) FROM registered_users ru WHERE ru.event_id = e.id AND ru.user_id = ?) AS is_registered 
                  FROM events e WHERE e.id = ?";
    $stmt = $conn->prepare($detail_sql);
    $stmt->bind_param("ii", $user_id, $selected_event_id);
    $stmt->execute();
    $detail_result = $stmt->get_result();
    
    if ($detail_result && $detail_result->num_rows > 0) {
        $selected_event = $detail_result->fetch_assoc();
        $is_registered = ($selected_event['is_registered'] > 0);
        
        // Set the active tab based on the selected event's registration status
        $active_tab = $is_registered ? 'registered' : 'unregistered';
    } else {
        // Event not found, set message
        $_SESSION['message'] = "The selected event was not found.";
        header("Location: user-events.php");
        exit();
    }
    $stmt->close();
    
    // Fetch speakers for the selected event
    $speakers_sql = "SELECT speaker_name FROM speakers WHERE event_id = ?";
    $stmt = $conn->prepare($speakers_sql);
    $stmt->bind_param("i", $selected_event_id);
    $stmt->execute();
    $speakers_result = $stmt->get_result();
    
    if ($speakers_result) {
        while ($speaker = $speakers_result->fetch_assoc()) {
            $speakers[] = $speaker['speaker_name'];
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <title>USER-events</title>
    <style> 
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body,
html {
    height: 100%;
}

/* Sidebar styles - unchanged */
.sidebar {
    width: 230px;
    height: 100vh;
    background-color: #12753E;
    color: white;
    display: flex;
    flex-direction: column;
    transition: width 0.3s ease;
    position: fixed;
}

.sidebar-content {
    margin-top: 30%;
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    font-family: Tilt Warp;
}

.sidebar-content a{
    font-family: 'Tilt Warp';
    color: #ffffff;
    text-decoration: none;
    padding: 1rem;
    display: flex;
    align-items: center;
    font-size: 1rem;
    border-radius: 5px;
    transition: background 0.3s;
    font-family: Tilt Warp Regular;
    margin-bottom: .5rem;
}

.sidebar-content span{
    font-family: Tilt Warp;
    font-size: 1rem;
}

.sidebar-content i{
    margin-right: 0.5rem;
}

.sidebar-content a:hover {
    background-color: white;
    color: #12753E; 
}

.sidebar-content .active{
    background-color: white;
    color: #12753E;
}

.user-profile {
    padding: 15px;
    border-top: 1px solid white;
    display: flex;
    align-items: center;
    position: sticky;
    bottom: 0;
    background-color: #12753E;
    width: 100%;
}

#logout {
    float: right;
    border: 1px solid black;
    height: 100%;
    width: 100%;
    color: white;
}

.user-avatar img{
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid white;
    padding: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
    font-family: Tilt Warp;
}

.username {
    font-family: Tilt Warp;
}

.main-content {
    flex: 1;
    padding: 20px;
    background-color: #ecf0f1;
}

/* Responsive adjustments for sidebar */
@media (max-width: 768px) {
    .sidebar {
        width: 70px;
    }

    .sidebar-header h2, .menu-text, .username {
        display: none;
    }

    .menu-item {
        display: flex;
        justify-content: center;
    }

    .user-profile {
        justify-content: center;
    }
}

/* Content area - improved */
.content {
    flex: 1;
    background-color: #f8f9fa;
    padding: 4rem;
    margin-left: 17%;
    font-family: Arial, sans-serif;
}

/* Header styles - unchanged */
.content-header h1 {
    font-size: 1.5rem;
    color: #333333;
    font-family: Wensley Demo;
    margin-left: 32%;
}

.content-header p {
    color: #999;
    font-size: 1rem;
    margin-top: -3%;
    font-family: LT Cushion Light;
    margin-left: 44%;
}

.content-header img {
    float: left;
    margin-left: 22%;
    margin-top: -1%;
    filter: drop-shadow(0px 4px 5px rgba(0, 0, 0, 0.3));
}

/* Content body - improved */
.content-body h1 {
    font-family: Montserrat ExtraBold;
    font-size: 2.2rem;
    padding: 10px;
    color: #12753E;
    letter-spacing: 0.5px;
}

.content-body hr {
    border: 1px solid #95A613;
    margin-bottom: 30px;
}

/* Tabs redesign */
.tabs {
    display: flex;
    border-bottom: 2px solid #e0e0e0;
    margin-bottom: 25px;
    gap: 10px;
}

.tab {
    padding: 12px 25px;
    background-color: #f5f5f5;
    border: none;
    border-radius: 8px 8px 0 0;
    cursor: pointer;
    font-family: Montserrat;
    font-weight: 600;
    font-size: 15px;
    color: #555;
    transition: all 0.3s ease;
    box-shadow: 0 -2px 5px rgba(0,0,0,0.05);
}

.tab:hover {
    background-color: #e8f5ef;
}

.tab.active {
    background-color: #12753E;
    color: white;
    font-weight: bold;
    box-shadow: 0 -2px 8px rgba(18,117,62,0.2);
}

.badge {
    background-color: #95A613;
    color: white;
    border-radius: 50%;
    padding: 3px 8px;
    font-size: 0.8em;
    font-weight: bold;
    font-family: Montserrat;
    margin-left: 8px;
    box-shadow: 0 2px 3px rgba(0,0,0,0.1);
}

/* Tab content */
.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* Events section redesign */
.events-section {
    background-color: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    font-family: 'Wesley Demo', serif;
    flex: 1;
    min-width: 300px;
    transition: all 0.3s ease;
}

.events-section.shrink {
    flex-basis: 70%;
}

.events-section h2 {
    font-size: 24px;
    font-family: Montserrat ExtraBold;
    font-weight: bold;
    margin-bottom: 25px;
    color: #333;
    border-left: 4px solid #12753E;
    padding-left: 15px;
}

/* Event items redesign */
.event {
    background-color:rgb(245, 245, 245);
    border-radius: 10px;
    padding: 22px;
    margin-bottom: 18px;
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 3px 8px rgba(0,0,0,0.05);
    border-left: 4px solid transparent;
}

.events-section {
    background-color: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    font-family: 'Wesley Demo', serif;
    flex: 1;
    min-width: 30%;
    max-height: fit-content;
    border: 0;
    margin-top: 20px;
    transition: all 0.3s ease;
}

.events-section {
    flex-basis: 100%;
    transition: flex-basis 0.3s, transform 0.3s;
}

.event.selected {
    background:rgb(218, 238, 227);
    border-left: 5px solid #12753E;
    transform: translateX(5px);
}

.event.selected h3 {
    color: #12753E;
}

.event.selected p {
    color: #445;
}

.event:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(43, 58, 143, 0.1);
    border-left: 5px solid #12753E;
}

.events-btn {
    text-decoration: none;
    color: inherit;
    display: block;
}

.event-content h3 {
    font-size: 19px;
    margin-bottom: 10px;
    font-family: Montserrat ExtraBold;
    color: #12753E;
    transition: color 0.3s ease;
}

.event-content p {
    font-size: 14px;
    color: #585858;
    font-family: Montserrat Medium;
    line-height: 1.5;
}

.event-content p strong {
    font-weight: bold;
    font-family: Montserrat;
    color: #444;
}

.status-badge {
    background-color: #95A613;
    color: white;
    font-family: Montserrat Medium;
    font-size: 12px;
    padding: 6px 15px;
    border-radius: 20px;
    position: absolute;
    top: 20px;
    right: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    font-weight: 600;
    letter-spacing: 0.5px;
}

/* Status-specific badges */
.status-upcoming {
    background-color: #3498db;
}

.status-ongoing {
    background-color: #e74c3c;
}

.status-past {
    background-color: #7f8c8d;
}

/* Content area layout */
.content-area { 
    display: flex; 
    justify-content: space-between; 
}

/* Details section redesign */
.details-section, #details-section {
    display: none;
    flex-basis: 30%;
    margin-left: 20px;
    margin-top: 2%;
    background-color: white;
    padding: 30px;
    border-radius: 12px;
    border: none;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    max-height: fit-content;
    transition: all 0.3s ease;
}

#detail-title {
    font-size: 24px;
    font-family: Montserrat Extrabold;
    margin-bottom: 15px;
    color: #12753E;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 10px;
}

.details-section h2 { 
    margin-top: 0;
    font-family: Montserrat Extrabold;
    font-weight: bold;
    margin-bottom: 2%;
    font-size: 22px;
    color: #333;
}

.details-section hr {
    border: 1px solid #f0f0f0;
    margin-bottom: 20px;
}

.detail-items {
    display: flex;
    flex-wrap: wrap;
}

.detail-items-1 {
    margin-top: 2%;
}

.detail-items-2 {
    margin-left: 20%;
    margin-top: 2%;
}

.details-section .detail-item {
    margin-bottom: 20px;
}

.details-section .detail-item h4 {
    margin: 0;
    font-size: 16px;
    font-family: Montserrat;
    color: #555;
    margin-bottom: 5px;
}

.details-section .detail-item p {
    margin: 5px 0 0;
    color: #12753E;
    font-size: 15px;
    font-family: Montserrat Medium;
    font-weight: 600;
}

.expand-btn {
    cursor: pointer;
    float: right;
    transition: transform 0.3s ease;
    background-color: #f2f9f6;
    padding: 8px;
    border-radius: 50%;
    color: #12753E;
}

.expand-btn:hover {
    background-color: #12753E;
    color: white;
}

/* Expanded content */
.expanded-content {
    display: none;
}

.details-section.expand .expanded-content {
    display: block;
}

.details-section.expand .expand-btn {
    transform: rotate(180deg);
}

/* Register/Unregister button */
.create-btn {
    float: right;
    padding: 12px 22px;
    font-family: Montserrat;
    font-weight: bold;
    font-size: 14px;
    color: white;
    text-decoration: none;
    background-color:rgb(17, 118, 62);
    border-radius: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 8px rgba(18,117,62,0.2);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.create-btn:hover {
    background-color: #0e5c31;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(18,117,62,0.3);
}

/* Expand/collapse helpers */
.expand { 
    flex-basis: 100% !important; 
}

.hidden { 
    display: none; 
}

/* Alert message styling */
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-family: Montserrat;
}

.alert-info {
    background-color: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

/* Notification styling */
.notification p {
    font-size: 14px;
    font-family: Montserrat;
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .detail-items-2 {
        margin-left: 0;
    }
    
    .detail-items {
        flex-direction: column;
    }
    
    .content {
        margin-left: 80px;
        padding: 2rem;
    }
    
    .content-header h1 {
        margin-left: 20%;
    }
    
    .content-header p {
        margin-left: 30%;
    }
    
    .content-header img {
        margin-left: 10%;
    }
}

@media (max-width: 768px) {
    .content-area {
        flex-direction: column;
    }
    
    .events-section.shrink {
        flex-basis: 100%;
    }
    
    .details-section, #details-section {
        margin-left: 0;
        margin-top: 20px;
    }
}

</style>
</head>
<body>
<div class="sidebar">
        <div class="sidebar-content">
            <a href="user-dashboard.php" class="menu-item">
                <span class="menu-icon"><i class="fas fa-home mr-3"></i></span>
                <span class="menu-text">Home</span>
            </a>
            <a href="user-events.php" class="menu-item active">
                <span class="menu-icon"><i class="fas fa-calendar-alt mr-3"></i></span>
                <span class="menu-text">Events</span>
            </a>
            <a href="user-notif.php" class="menu-item">
                <span class="menu-icon"><i class="fas fa-bell mr-3"></i></span>
                <span class="menu-text">Notification</span>
            </a>

            <!-- Add more menu items as needed -->
        </div>
        <div class="user-profile">
            <div class="user-avatar"><img src="styles/photos/jess.jpg"></div>
            <div class="username"><?php echo htmlspecialchars($_SESSION['first_name']); ?> <?php echo isset($_SESSION['last_name']) ? htmlspecialchars($_SESSION['last_name']) : ''; ?></div>
        </div>
    </div>

    <div class="content">
        <div class="content-header">
            <img src="styles/photos/DO-LOGO.png" width="70px" height="70px">
            <p>Learning and Development</p>
            <h1>EVENT MANAGEMENT SYSTEM</h1>
        </div><br><br><br>

        <div class="content-body">
            <h1>Events</h1>
            <hr><br>

            <div class="content-area">
                <div class="events-section <?php echo $selected_event ? 'shrink' : ''; ?>">
                    <div class="tabs">
                        <button class="tab <?php echo $active_tab == 'registered' ? 'active' : ''; ?>" onclick="switchTab('registered')">
                            Registered <span class="badge"><?php echo count($registered_events); ?></span>
                        </button>
                        <button class="tab <?php echo $active_tab == 'unregistered' ? 'active' : ''; ?>" onclick="switchTab('unregistered')">
                            Unregistered <span class="badge"><?php echo count($unregistered_events); ?></span>
                        </button>
                    </div>

                    <!-- Registered Events Tab -->
                    <div id="registered-tab" class="tab-content <?php echo $active_tab == 'registered' ? 'active' : ''; ?>">
                        <h2>Registered Events</h2>
                        <?php
                        if (count($registered_events) > 0) {
                            foreach ($registered_events as $row) {
                                $isSelected = ($selected_event_id == $row['id']) ? 'selected' : '';
                                echo '<div class="event ' . $isSelected . '">';
                                echo '<a class="events-btn" href="user-events.php?event_id=' . urlencode($row['id']) . '&tab=registered">';
                                echo '<div class="event-content">';
                                echo '<h3>' . htmlspecialchars($row["title"]) . '</h3>';
                                echo '<p>'. '<strong>Event Specification: '. '</strong>' . htmlspecialchars($row["event_specification"]) . '</p>';
                                echo '<p>' . '<strong>Date: '. '</strong>' . date('M d, Y', strtotime($row["start_datetime"])) . '</p>';
                                echo '<span class="status-badge status-' . strtolower($row["status"]) . '">' . htmlspecialchars($row["status"]) . '</span>';
                                echo '</div></a>';
                                echo '</div>';
                            }
                        } else {
                            echo "<p>You haven't registered for any events yet.</p>";
                        }
                        ?>
                    </div>

                    <!-- Unregistered Events Tab -->
                    <div id="unregistered-tab" class="tab-content <?php echo $active_tab == 'unregistered' ? 'active' : ''; ?>">
                        <h2>Available Events</h2>
                        <?php
                        if (count($unregistered_events) > 0) {
                            foreach ($unregistered_events as $row) {
                                $isSelected = ($selected_event_id == $row['id']) ? 'selected' : '';
                                echo '<div class="event ' . $isSelected . '">';
                                echo '<a class="events-btn" href="user-events.php?event_id=' . urlencode($row['id']) . '&tab=unregistered">';
                                echo '<div class="event-content">';
                                echo '<h3>' . htmlspecialchars($row["title"]) . '</h3>';
                                echo '<p>' . '<strong>Event Specification: '. '</strong>' . htmlspecialchars($row["event_specification"]) . '</p>';
                                echo '<p>'. '<strong>Date: '. '</strong>' . date('M d, Y', strtotime($row["start_datetime"])) . '</p>';
                                echo '</div></a>';
                                echo '</div>';
                            }
                        } else {
                            echo "<p>No available events found.</p>";
                        }
                        ?>
                    </div>
                </div>  

                <div class="details-section" id="details-section" <?php echo $selected_event ? 'style="display: block;"' : ''; ?>>
                    <i class="fas fa-expand expand-btn" onclick="toggleExpand()"></i>
                    <h2>Details</h2>
                    <hr>
                    <h3 id="detail-title"><?php echo htmlspecialchars($selected_event["title"]); ?></h3>
                    <div class="detail-items">
                        <div class="detail-items-1"> 
                            <?php if ($selected_event): ?>
                            <div class="detail-item">
                                <h4>Delivery:</h4>
                                <p id="detail-mode"><?php echo htmlspecialchars($selected_event["delivery"]); ?></p>
                            </div>

                            <div class="detail-item">
                                <h4>Venue:</h4>
                                <p id="detail-venue"><?php echo htmlspecialchars($selected_event["venue"] ?? "Not specified"); ?></p>
                            </div>

                            <div class="detail-item expanded-content">
                                <h4>Event Specification:</h4>
                                <p id="detail-specification"><?php echo htmlspecialchars($selected_event["event_specification"]); ?></p>
                            </div>

                            <div class="detail-item">
                                <h4>Start:</h4>
                                <p id="detail-start"><?php echo htmlspecialchars($selected_event["start_datetime"]); ?></p>
                            </div>
                            <div class="detail-item">
                                <h4>End:</h4>
                                <p id="detail-end"><?php echo htmlspecialchars($selected_event["end_datetime"]); ?></p>
                            </div>
                        </div>
                        <div class="detail-items-2">
                            <div class="detail-item expanded-content">
                                <h4>Organizer:</h4>
                                <p id="detail-organizer"><?php echo htmlspecialchars($selected_event["organizer_name"] ?? "Not specified"); ?></p>
                            </div>

                            <div class="detail-item expanded-content">
                                <h4>Speaker(s):</h4>
                                <p id="detail-speakers">
                                <?php 
                                if (!empty($speakers)) {
                                    echo htmlspecialchars(implode(", ", $speakers));
                                } else {
                                    echo "Not specified";
                                }
                                ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <br>
                    <?php if (!$is_registered): ?>
                    <a class="create-btn" href="register.php?event_id=<?php echo urlencode($selected_event['id']); ?>">Register</a>
                    <?php else: ?>
                    <a class="create-btn" href="unregister.php?event_id=<?php echo urlencode($selected_event['id']); ?>" style="background-color:rgb(117, 130, 14); border-style: none; cursor: pointer;" onclick="return confirm('Are you sure you want to unregister from this event?');">Unregister</a>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="detail-item">
                        <p>Select an event to view details</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Function to switch tabs
function switchTab(tabName) {
    // Hide all tab contents
    var tabContents = document.getElementsByClassName('tab-content');
    for (var i = 0; i < tabContents.length; i++) {
        tabContents[i].classList.remove('active');
    }
    
    // Remove active class from all tabs
    var tabs = document.getElementsByClassName('tab');
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove('active');
    }
    
    // Show the selected tab content and mark tab as active
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Explicitly activate the clicked tab button
    document.querySelector('.tab:nth-child(' + (tabName === 'registered' ? '1' : '2') + ')').classList.add('active');
    
    // Update URL with the active tab parameter
    if (window.location.href.includes('event_id=')) {
        // If there's an event ID in the URL, preserve it
        var eventId = new URLSearchParams(window.location.search).get('event_id');
        window.history.replaceState(null, null, `?event_id=${eventId}&tab=${tabName}`);
    } else {
        window.history.replaceState(null, null, `?tab=${tabName}`);
    }
}

// Auto-scroll to selected event
document.addEventListener('DOMContentLoaded', function() {
    // Check if there's a selected event
    const selectedEvent = document.querySelector('.event.selected');
    
    // If a selected event exists, scroll to it
    if (selectedEvent) {
        // Smooth scroll to the element
        selectedEvent.scrollIntoView({
            behavior: 'smooth',
            block: 'center' // Centers the element in the viewport
        });
        
        // Optional: Add a brief highlight effect
        setTimeout(function() {
            selectedEvent.style.transition = 'background-color 0.5s';
            const originalBackground = selectedEvent.style.backgroundColor;
            selectedEvent.style.backgroundColor = '#20cd6d'; // Flash with a different color
            
            setTimeout(function() {
                selectedEvent.style.backgroundColor = originalBackground;
            }, 700);
        }, 300);
    }
});

function toggleExpand() {
    let detailsSection = document.getElementById('details-section');
    let eventsSection = document.querySelector('.events-section');
    let expandIcon = document.querySelector('.expand-btn');
    let expandedContent = document.querySelectorAll('.expanded-content');

    if (detailsSection.classList.contains('expand')) {
        // Collapse
        detailsSection.classList.remove('expand');
        eventsSection.classList.remove('hidden');
        expandIcon.classList.replace('fa-compress', 'fa-expand');
    } else {
        // Expand
        detailsSection.classList.add('expand');
        eventsSection.classList.add('hidden');
        expandIcon.classList.replace('fa-expand', 'fa-compress');
    }
}
</script>

</body>
</html>

<?php
$conn->close();
?>