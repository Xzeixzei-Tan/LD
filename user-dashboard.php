<?php
require_once 'config.php';

// Start the session
session_start();

$user_id = $_SESSION['user_id'];

// Display session messages if any exist
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-info">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']); // Clear the message after displaying
}

// Set the default sort order to ASC (Soonest events first)
$sortOrder = isset($_GET['sort']) && ($_GET['sort'] == 'DESC') ? 'DESC' : 'ASC';

// Fetch upcoming and ongoing events from the database
$sql = "SELECT id, title, specification, start_date, end_date,
        CASE 
            WHEN NOW() BETWEEN start_date AND end_date THEN 'Ongoing'
            ELSE 'Upcoming'
        END AS status
        FROM events 
        WHERE end_date >= NOW()
        ORDER BY start_date $sortOrder";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
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

// Fetch notifications for user
$notif_query = "SELECT id, message, created_at, is_read, notification_subtype, event_id 
                FROM notifications 
                WHERE notification_type = 'user' 
                ORDER BY created_at DESC";
$notif_result = $conn->query($notif_query);

if (!$notif_result) {
    die("Notification query failed: " . $conn->error);
}

// Function to format the event days data into a readable format
function formatEventDaysData($eventDaysData) {
    if (empty($eventDaysData)) {
        return "No specific days information available";
    }
    
    $daysArray = explode('||', $eventDaysData);
    $formattedDays = [];
    
    foreach ($daysArray as $day) {
        // Use a regular expression to extract the parts
        if (preg_match('/^(\d+):(\d{4}-\d{2}-\d{2}):(\d{2}):(\d{2}):(\d{2}):(\d{2})$/', $day, $matches)) {
            $dayNumber = $matches[1];
            $dayDate = $matches[2];
            $startHour = $matches[3];
            $startMinute = $matches[4];
            $endHour = $matches[5];
            $endMinute = $matches[6];
            
            // Format the date and times
            $formattedDate = date('F j, Y', strtotime($dayDate));
            
            // Format the times
            $startTime = date('g:i A', strtotime("2000-01-01 $startHour:$startMinute"));
            $endTime = date('g:i A', strtotime("2000-01-01 $endHour:$endMinute"));
            
            $formattedDays[] = "Day $dayNumber ($formattedDate): $startTime - $endTime";
        }
    }
    
    return implode('<br>', $formattedDays);
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
	<title>Dashboard-Template</title>
</head>
<style type="text/css">
	* {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }

    body, html {
        height: 100%;
        #12753E;
    }

    .sidebar {
    position: fixed;
    width: 250px;
    height: 100vh;
    background-color: #12753E;
    color: #ffffff;
    padding: 2rem 1rem;
    display: flex;
    flex-direction: column;
    justify-content: flex-start; /* Changed from space-between to maintain positions */
    transition: width 0.3s ease;
    z-index: 999;
}

.sidebar.collapsed {
    width: 90px;
    padding: 2rem 0.5rem;
}

.sidebar .logo {
    margin-bottom: 1rem;
    margin-left: 5%;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sidebar.collapsed .logo {
    margin-left: 0;
    justify-content: center;
}

.toggle-btn {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 5px;
    border-radius: 4px;
    transition: background 0.2s;
}

.toggle-btn:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

/* Keep the menu at the same position */
.sidebar .menu {
    margin-top: 50%;
    display: flex;
    flex-direction: column;
    flex-grow: 1; /* Allow it to grow but maintain position */
}

/* Adjust menu items when sidebar is collapsed */
.sidebar.collapsed .menu {
    align-items: center;
    margin-top: 50%; /* Keep the same top margin */
}

.sidebar .menu a {
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
    width: 100%;
}

.sidebar.collapsed .menu a {
    justify-content: center;
    padding: 1rem 0;
    width: 90%;
}

.sidebar .menu a span {
    margin-left: 0.5rem;
    transition: opacity 0.2s;
    font-family: Tilt Warp Regular;
}

.sidebar.collapsed .menu a span {
    opacity: 0;
    width: 0;
    height: 0;
    overflow: hidden;
    display: none;
}

.sidebar .menu a:hover,
.sidebar .menu a.active {
    background-color: white;
    color: #12753E;
}

.sidebar .menu a i {
    margin-right: 0.5rem;
    min-width: 20px;
    text-align: center;
}

.sidebar.collapsed .menu a i {
    margin-right: 0;
    font-size: 1.2rem;
}

/* Fix user profile section for collapsed sidebar */
.user-profile {
    padding: 15px;
    border-top: 1px solid white;
    display: flex;
    align-items: center;
    position: absolute; /* Changed from sticky to absolute for more control */
    bottom: 0;
    left: 0;
    right: 0;
    background-color: #12753E;
    cursor: pointer;
}

.sidebar.collapsed .user-profile {
    justify-content: center;
    padding: 15px 0;
}

.sidebar.collapsed .username {
    display: none;
}

.user-avatar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid white;
    padding: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
}
.username {
        
    font-family: Tilt Warp;
}

.sidebar.collapsed .user-avatar img {
    margin-right: 0;
}

/* Update logout menu position for collapsed sidebar */
.logout-menu {
    position: absolute;
    top: 0;
    bottom: 100%;
    border-radius: 5px;
    padding: 10px;
    display: none;
    z-index: 10000;
    width: 100px;en;
}

.sidebar.collapsed .logout-menu {
    left: -50px;
}

.logout-menu.active {
    display: block;
}

.logout-btn {
        background-color: white; 
        border: 2px solid #12753E;  
        display: block;
        width: 90%;
        padding: 8px 10px;
        color: #12753E;
        border-radius: 4px;
        font-family: 'Tilt Warp', sans-serif;
        font-size: 14px;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s ease;
        position: absolute;
        top: 80%;
        left: 225%;
        z-index: 10001 !important; /* Increase this value significantly */
        box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Optional: add shadow for better visibility */
}

.sidebar.collapsed {
    width: 90px;
    padding: 2rem 0.5rem;
}
/* Content adjustments */
.content {
    flex: 1;
    background-color: #ffffff;
    padding: 4rem;
    margin-left: 17%;
    transition: margin-left 0.3s ease;
}

.content.expanded {
    margin-left: 90px;
}

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .sidebar {
            width: 70px;
        }

        .sidebar-header h2, .menu-text, .username{
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
        font-size: 2.2rem;
        padding: 10px;
        font-family: Montserrat ExtraBold;
        color: black;
    }

    .content-body hr {
        width: 100%;
        border: none;
        height: 2px;
        background-color: #95A613;
        margin-bottom: 20px;
    }

    /* Updated Content Area Styling */
.content-area {
    display: flex;
    padding: 20px 0 40px;
    gap: 30px;
}

.events-section, .notifications-section {
    background-color: white;
    border-radius: 12px;
    border: 1px solid #e0e0e0;
    padding: 25px;
    box-shadow: 0 6px 16px rgba(18, 117, 62, 0.08);
    font-family: 'Wesley Demo', serif;
    transition: all 0.3s ease;
    max-height: fit-content;
    
}

.events-section {
    width: 50%;
    flex: 3;
    max-height: 500px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    position: relative;
    z-index: 1;
}

.events-section:hover, .notifications-section:hover {
    box-shadow: 0 8px 24px rgba(18, 117, 62, 0.12);
}

/* Scrollbar Styling */
.events-section::-webkit-scrollbar,
.notifications-section::-webkit-scrollbar {
    width: 6px;
}

.events-section::-webkit-scrollbar-track,
.notifications-section::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.events-section::-webkit-scrollbar-thumb,
.notifications-section::-webkit-scrollbar-thumb {
    background: #12753E;
    border-radius: 10px;
}

.events-section::-webkit-scrollbar-thumb:hover,
.notifications-section::-webkit-scrollbar-thumb:hover {
    background: #0e5c31;
}

.notifications-section {
    flex: 2;
    max-height: 500px;
    overflow-y: auto;
}

.events-section h2, .notifications-section h2 {
    font-size: 22px;
    font-family: Montserrat ExtraBold;
    font-weight: bold;
    margin-bottom: 20px;
    color: #12753E;
    position: relative;
    border-bottom: 2px solid #f0f2fa;
    padding-bottom: 10px;
    z-index: 10;
}

.event, .notification {
    background-color: #f8fcfa;
    border-left: 4px solid #12753E;
    border-radius: 8px;
    padding: 18px;
    position: relative;
    transition: all 0.2s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
    margin-bottom: 15px;
}

.event:hover, .notification:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(18, 117, 62, 0.1);
    background-color: #edf7f2;
}

.event-content h3 {
    color: #12753E;
    font-size: 18px;
    margin-bottom: 8px;
    font-family: Montserrat ExtraBold;
}

.event-content p {
    font-size: 14px;
    color: #555;
    font-family: Montserrat Medium;
    line-height: 1.4;
}

.event-content p strong {
    font-family: Montserrat;
    color:rgb(84, 95, 89);
}

.event-content span {
    position: absolute;
    bottom: 10px;
    right: 15px;
    background: #12753E;
    color: white;
    padding: 6px 14px;
    border-radius: 20px;
    font-family: Tilt Warp;
    font-size: 12px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    font-weight: 500;
    box-shadow: 0 2px 4px rgba(18, 117, 62, 0.2);
}

.event-content span {
    position: absolute;
    bottom: 10px;
    right: 15px;
    padding: 6px 14px;
    border-radius: 20px;
    font-family: Tilt Warp;
    font-size: 12px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    font-weight: 500;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Status-specific styling */
.event-content span.ongoing {
    background: #12753E;
    color: white;
    animation: pulse 2s infinite;
}

.event-content span.upcoming {
    background: #95A613;
    color: white;
}

/* Add a pulse animation for ongoing events */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(18, 117, 62, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(18, 117, 62, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(18, 117, 62, 0);
    }
}

.event-dates {
    font-size: 13px;
    color: #777;
    margin-top: 5px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
}

.event-dates i {
    margin-right: 5px;
    color: #12753E;
}

.notification {
    border-left: 4px solid #95A613;
    margin-bottom: 15px;
}

/* New Styles for Read Notifications */
.notification .read {
    opacity: 0.7;
}

.notification.read {
    border-left: 4px solid #ccc;
    background-color: #f0f0f0;
}

.notification.read:hover {
    background-color: #e8e8e8;
}

.notification.read p {
    color: #888;
}

.notification.read small {
    color: #aaa;
}

.notification p {
    font-size: 14px;
    font-family: Montserrat Medium;
    color: #555;
    line-height: 1.4;
}


#events-btn.read {
    color: #888;
}

.notification-content {
    transition: all 0.2s ease;
}

.notification.read .notification-content {
    opacity: 0.7;
}

.notification.important {
    border-left: 4px solidrgb(218, 60, 42);
    background-color: #fef9f9;
}

.notification.important:hover {
    background-color: #fdf3f2;
}

.events-btn {
    text-decoration: none;
    color: #333;
    display: block;
    height: fit-content;
    width: 100%;
}

</style>
<body>
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
    <div class="logo">
        <button id="toggleSidebar" class="toggle-btn">
            <i class="fas fa-bars"></i>
        </button>
    </div>



       <div class="menu">
        <a href="user-dashboard.php" class="active">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="user-events.php">
            <i class="fas fa-calendar-alt"></i>
            <span>Events</span>
        </a>
        <a href="user-notif.php">
            <i class="fas fa-bell mr-3"></i>
            <span>Notification</span>
        </a>

</div>
 <!-- Modified user profile with logout menu -->
        <div class="user-profile" id="userProfileToggle">
            <div class="user-avatar"><img src="styles/photos/me.jpg"></div>
            <div class="username"><?php echo htmlspecialchars($_SESSION['first_name']); ?> <?php echo isset($_SESSION['last_name']) ? htmlspecialchars($_SESSION['last_name']) : ''; ?></div>
            <!-- Add logout menu -->
            <div class="logout-menu" id="logoutMenu">
                <a href="login.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>


    <div class="content">
    	<div class="content-header">
	    	<img src="styles/photos/DO-LOGO.png" width="70px" height="70px">
	    	<p>Learning and Development</p>
	    	<h1>EVENT MANAGEMENT SYSTEM</h1>
    	</div><br><br><br>

    	<div class="content-body">
	    	<h1>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h1>
	    	<hr><br>

            <div class="content-area">
                <div class="events-section">
                <h2>Events</h2>
                <?php while ($event = $result->fetch_assoc()) : ?>    
                        <div class="event">
                            <a class="events-btn" href="user-events.php?event_id=<?php echo urlencode($event['id']); ?>">
                            <div class="event-content">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p><strong>Event Specification:</strong> <?php echo htmlspecialchars($event['specification']); ?></p>
                                <div class="event-dates">
                                    <i class="fas fa-calendar-day"></i>
                                    <?php 
                                        $start_date = new DateTime($event['start_date']);
                                        $end_date = new DateTime($event['end_date']);
                                        echo $start_date->format('M d') . ' - ' . $end_date->format('M d, Y'); 
                                    ?>
                                </div>
                                <?php if ($event["status"] === "Ongoing") { ?>
                                    <span class="ongoing"><i class="fas fa-circle"></i> Ongoing</span>
                                <?php } else { ?>
                                    <span class="upcoming"><i class="fas fa-hourglass-start"></i> Upcoming</span>
                                <?php } ?>        
                            </div>
                            </a>
                        </div>
                    <?php endwhile; ?>
                    </div>
                    <div class="notifications-section">
                        <h2>Notifications</h2>
                        <?php while ($notif = $notif_result->fetch_assoc()): ?>
                            <div class="notification <?php echo $notif['is_read'] ? 'read' : 'important'; ?>">
                                <?php 
                                // Determine the redirect URL based on notification type
                                if (!empty($notif['event_id']) && $notif['notification_subtype'] == 'certificate') {
                                    $redirect_url = "user-notif.php?event_id=" . urlencode($notif['event_id']);
                                } elseif (!empty($notif['event_id']) && $notif['notification_subtype'] == 'new_event') {
                                    $redirect_url = "user-events.php?event_id=" . urlencode($notif['event_id']);
                                } elseif (!empty($notif['event_id']) && $notif['notification_subtype'] == 'event_reminder') {
                                    $redirect_url = "user-events.php?event_id=" . urlencode($notif['event_id']);
                                } elseif (!empty($notif['event_id']) && $notif['notification_subtype'] == 'event_registration') {
                                    $redirect_url = "user-events.php?event_id=" . urlencode($notif['event_id']);
                                } 
                                else {
                                    $redirect_url = "user-notif.php?event_id=" . urlencode($notif['event_id']);
                                }
                                
                                // You need to make sure your query also fetches the notification ID
                                $notification_id = $notif['id']; // Add id to your SELECT statement if not already included
                                ?>
                                
                                <a id="events-btn" class="<?php echo $notif['is_read'] ? 'read' : 'unread'; ?>" 
                                   href="mark_notification_read.php?notification_id=<?php echo $notification_id; ?>&redirect=<?php echo urlencode($redirect_url); ?>">
                                    <div class="notification-content">
                                        <p><?php echo htmlspecialchars($notif['message']); ?></p>
                                        <br><small><?php echo $notif['created_at']; ?></small>    
                                    </div>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                </div>
            </div>
    	</div>
    </div>
</div>

<!-- Add JavaScript for the user profile toggle and logout menu -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // User profile toggle and logout menu
    const userProfileToggle = document.getElementById('userProfileToggle');
    const logoutMenu = document.getElementById('logoutMenu');
    const sidebar = document.getElementById('sidebar');
    const content = document.querySelector('.content');
    const toggleBtn = document.getElementById('toggleSidebar');
    const userAvatar = document.querySelector('.user-avatar');

    // Check if sidebar state is saved in localStorage
    const isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    
    // Set initial state based on localStorage
    if (isSidebarCollapsed) {
        sidebar.classList.add('collapsed');
        content.classList.add('expanded');
        userAvatar.style.cursor = 'default'; // Make avatar non-clickable
        userProfileToggle.style.pointerEvents = 'none'; // Disable click events
    } else {
        userAvatar.style.cursor = 'pointer'; // Make avatar clickable
        userProfileToggle.style.pointerEvents = 'auto'; // Enable click events
    }

    // Toggle sidebar when button is clicked
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('expanded');
            
            // Update avatar clickability based on sidebar state
            if (sidebar.classList.contains('collapsed')) {
                userAvatar.style.cursor = 'default';
                userProfileToggle.style.pointerEvents = 'none';
            } else {
                userAvatar.style.cursor = 'pointer';
                userProfileToggle.style.pointerEvents = 'auto';
            }
            
            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    }

    // Toggle logout menu when user profile is clicked
    if (userProfileToggle) {
        userProfileToggle.addEventListener('click', function(event) {
             // Only toggle logout menu if sidebar is not collapsed
             if (!sidebar.classList.contains('collapsed')) {
                event.stopPropagation();
                logoutMenu.classList.toggle('active');
            }
        });
    }

    // Close the logout menu when clicking outside
    document.addEventListener('click', function(event) {
        if (logoutMenu && userProfileToggle && !userProfileToggle.contains(event.target)) {
            logoutMenu.classList.remove('active');
        }
    });

    // Sort events functionality
    const sortButton = document.getElementById('sortButton');
    if (sortButton) {
        sortButton.addEventListener('click', function() {
            // Get the current sort order from the URL
            const currentSortOrder = new URLSearchParams(window.location.search).get('sort') || 'ASC';
            
            // Toggle sort order
            const newSortOrder = (currentSortOrder === 'ASC') ? 'DESC' : 'ASC';
            
            // Update the URL to reflect the new sort order
            window.location.href = window.location.pathname + '?sort=' + newSortOrder;
        });
    }

    // Update the sort order label and button text on page load
    const currentSortOrder = new URLSearchParams(window.location.search).get('sort') || 'ASC';
    // Using getElementById with a null check since the element might not exist
    const sortOrderLabel = document.getElementById('sortOrderLabel');
    if (sortOrderLabel) {
        sortOrderLabel.textContent = currentSortOrder === 'ASC' ? 'Ascending' : 'Descending';
    }
    
    if (sortButton) {
        sortButton.textContent = 'Sort Events: ' + (currentSortOrder === 'ASC' ? 'Asc' : 'Des');
    }
});
</script>
</body>
</html>