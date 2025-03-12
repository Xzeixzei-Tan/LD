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
$user_id = 8;

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

/* Responsive adjustments */
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
.content {
    flex: 1;
    background-color: #ffffff;
    padding: 4rem;
    margin-left: 17%;
}

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

.content-body h1 {
    font-family: Montserrat ExtraBold;
    font-size: 2rem;
    padding: 10px;
    color: ##12753E;
}

.content-body hr {
    border: 1px solid #95A613;
}

.create-btn {
    float: right;
    bottom: 1%;
    right: 3%;
    padding: 11px 15px;
    font-family: Montserrat;
    font-weight: bold;
    font-size: 13px;
    color: white;
    text-decoration: none;
    background-color: #12753E;
    border-radius: 5px;
}

.content-area {
    display: flex;
    justify-content: space-between;
}

.events-section {
    background-color: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    font-family: 'Wesley Demo', serif;
    flex: 1;
    min-width: 300px;
}

.events-section {
    flex-basis: 100%;
    transition: flex-basis 0.3s;
}

.event.selected {
    background: #12753E;
}

.event.selected h3 {
    color: white;
}

.event.selected p {
    color: rgb(231, 231, 231);
}

#details-section {
    display: none;
    flex-basis: 30%;
    margin-left: 20px;
    background-color: white;
    padding: 30px;
    border-radius: 8px;
    border: 2px solid #12753E;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    max-height: fit-content;
}

.events-section.shrink {
    flex-basis: 70%;
}

.details-section h2 {
    margin-top: 0;
    font-family: Montserrat Extrabold;
    font-weight: bold;
    margin-bottom: 2%;
}

#detail-title {
    font-family: Montserrat Extrabold;
    color: #12753E;
}

.details-section .detail-item {
    margin-bottom: 15px;
}

.details-section .detail-item h3 {
    margin: 0;
    font-size: 1em;
    font-family: Montserrat;
    color: #12753E;
}

.details-section .detail-item p {
    margin: 5px 0 0;
    color: #000000;
    font-size: .8em;
    font-family: Montserrat Medium
}

.events-section h2 {
    font-size: 22px;
    font-family: Montserrat ExtraBold;
    font-weight: bold;
    margin-bottom: 20px;
    color: #333;
}

.event {
    background-color: #d7f3e4;
    border-radius: 5px;
    padding: 20px;
    margin-bottom: 15px;
    position: relative;
    cursor: pointer;
}

.event-content h3 {
    font-size: 18px;
    margin-bottom: 5px;
    font-family: Montserrat ExtraBold;
    color: #12753E;
}

.event-content p {
    font-size: 14px;
    color: #585858;
    font-family: Montserrat;
}

.event-content p strong{
    font-size: 13px;
    font-family: Montserrat Medium;
}

.notification p {
    font-size: 14px;
    font-family: Montserrat;
}

.events-btn {
    text-decoration: none;
    color: black;
    display: block;
}

.content-area { 
    display: flex; 
    justify-content: space-between; 
}
.details-section { 
    display: none; 
    flex-basis: 30%; 
    margin-left: 20px; 
    background-color: #f9f9f9; 
    padding: 20px; 
    border-radius: 8px; 
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); 
}
.events-section { 
    flex-basis: 100%; 
    transition: flex-basis 0.3s; 
}
.events-section.shrink { 
    flex-basis: 70%; 
}

#detail-title {
    font-size: 24px;
    font-family: Montserrat Extrabold;
    margin-bottom: 10px;
    color: #12753E;
}

.details-section h2 { 
    margin-top: 3%; 
    margin-bottom: 2%;
}
.details-section hr{
    margin-bottom: 2%;
}
.details-section .detail-item { 
    margin-bottom: 15px; 
}

.details-section .detail-item h4 {
    font-family: Montserrat;
    font-size: 18px;
    margin-bottom: 5px;
}

.details-section .detail-item p { 
    font-family: Montserrat Medium;
    font-size: 16px; 
    color: #555;
}

.detail-items{
    display: flex;
}

.detail-items-1{
    margin-top: 2%;
}

.detail-items-2{
    margin-left: 30%;
    margin-top: 2%;
}
.expand-btn { 
    cursor: pointer; 
    float: right; 
    transition: transform 0.3s ease;
}
.expand { 
    flex-basis: 100% !important; 
}
.hidden { 
    display: none; 
}

/* New styles for expanded content */
.expanded-content {
    display: none;
}

.details-section.expand .expanded-content {
    display: block;
}

.details-section {
    transition: all 0.3s ease;
}

.details-section.expand .expand-btn {
    transform: rotate(180deg);
}

/* Tabs styles */
.tabs {
    display: flex;
    border-bottom: 2px solid #e0e0e0;
    margin-bottom: 20px;
}

.tab {
    padding: 10px 20px;
    background-color: #f5f5f5;
    border: none;
    border-radius: 5px 5px 0 0;
    margin-right: 5px;
    cursor: pointer;
    font-family: Montserrat;
    font-weight: 600;
    color: #555;
    transition: all 0.3s ease;
}

.tab.active {
    background-color: #12753E;
    color: white;
    font-weight: bold;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.badge {
    background-color: #95A613;
    color: white;
    border-radius: 50%;
    padding: 2px 8px;
    font-size: 0.8em;
    font-family: Montserrat;
    margin-left: 5px;
}

.status-badge {
    background-color:rgb(142, 159, 8);
    color: white;
    font-family: Montserrat Medium;
    font-size: 12px;
    padding: 5px 15px;
    border-radius: 12px;
    position: absolute;
    top: 10%;
    right: 2%;
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
            <div class="username">Jess Constante</div>
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
                    <a class="create-btn" href="unregister.php?event_id=<?php echo urlencode($selected_event['id']); ?>" style="background-color: #95A613; border-style: none; cursor: pointer;" onclick="return confirm('Are you sure you want to unregister from this event?');">Unregister</a>
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
            selectedEvent.style.backgroundColor = '#95A613'; // Flash with a different color
            
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