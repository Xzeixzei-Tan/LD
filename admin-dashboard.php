<?php
require_once 'config.php';

// Set the default sort order to ASC (Soonest events first)
$sortOrder = isset($_GET['sort']) && ($_GET['sort'] == 'DESC') ? 'DESC' : 'ASC';

// Check if an event ID is specified in the URL
$selected_event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : null;

$selected_event = null;

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
    
// Fetch notifications for admin
$notif_query = "SELECT message, created_at, is_read, notification_subtype, event_id FROM notifications WHERE notification_type = 'admin' ORDER BY created_at DESC";
$notif_result = $conn->query($notif_query);

if (!$notif_result) {
    die("Notification query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="styles/admin-dashboard.css" rel="stylesheet">
    <title>Dashboard-Template</title>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="logo">
        <button id="toggleSidebar" class="toggle-btn">
            <i class="fas fa-bars"></i>
        </button>
    </div>
        
    <div class="menu">
        <a href="admin-dashboard.php" class="active">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="admin-events.php">
            <i class="fas fa-calendar-alt"></i>
            <span>Events</span>
        </a>
        <a href="admin-users.php">
            <i class="fas fa-users"></i>
            <span>Users</span>
        </a>
    </div>
</div>

<div class="content" id="mainContent">
    <div class="content-header">
        <img src="styles/photos/DO-LOGO.png" width="70px" height="70px">
        <p>Learning and Development</p>
        <h1>EVENT MANAGEMENT SYSTEM</h1>
    </div><br><br><br>


    <div class="content-body">
        <h1>Welcome, Admin!</h1>
        <hr><br><br>

        <div class="content-area">
            <div class="events-section">
                <h2>Events</h2>
                <div class="sort-events">
                    <!-- Sort Button to Toggle Order -->
                    <button id="sortButton" onclick="toggleSortOrder()"><i class="fa fa-sort" aria-hidden="true"></i>Sort Events: <?php echo $sortOrder === 'ASC' ? 'Asc' : 'Des'; ?></button>
                </div>
                <?php while ($event = $result->fetch_assoc()) : ?>
                    <div class="event">
                        <div class="event-content">
                            <a style="text-decoration: none;" href="admin-events.php?event_id=<?php echo urlencode($event['id']); ?>">
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
                        </div></a>
                    </div>
                <?php endwhile; ?>
                <?php if ($result->num_rows == 0): ?>
                    <p style="font-family: Montserrat; color: gray;">No events available yet.</p>
                <?php endif; ?>
            </div>

            <!-- Notifications Section -->
                <div class="notifications-section">
                    <h2>Notifications</h2>
                    <?php while ($notif = $notif_result->fetch_assoc()): ?>
                    <div class="notification important">
                        <?php if (!empty($notif['event_id']) && $notif['notification_subtype'] == 'admin_event_registration'): ?>
                            <a id="events-btn" class="<?php echo $notif['is_read'] ? 'read' : 'unread'; ?>" href="admin-events.php?event_id=<?php echo urlencode($notif['event_id']); ?>">
                        <?php else: ?>
                            <a id="events-btn" class="<?php echo $notif['is_read'] ? 'read' : 'unread'; ?>" href="admin-users.php">
                        <?php endif; ?>
                            <div class="notification-content">
                                <p><?php echo htmlspecialchars($notif['message']); ?></p>
                                <br><small><?php echo $notif['created_at']; ?></small>    
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
                <?php if ($notif_result->num_rows == 0): ?>
                    <p style="font-family: Montserrat; color: gray;">No notifications available yet.</p>
                <?php endif; ?>
            </div>          
        </div>
    </div>
</div>

<script>
    // Function to toggle sidebar
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('mainContent');
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
            
            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    });

    function toggleSortOrder() {
        // Get the current sort order from the URL
        const currentSortOrder = new URLSearchParams(window.location.search).get('sort') || 'ASC';

        // Toggle sort order
        const newSortOrder = (currentSortOrder === 'ASC') ? 'DESC' : 'ASC';

        // Update the URL to reflect the new sort order
        window.location.href = window.location.pathname + '?sort=' + newSortOrder;
    }

    // Update the sort order label and button text on page load
    document.addEventListener('DOMContentLoaded', function() {
        const currentSortOrder = new URLSearchParams(window.location.search).get('sort') || 'ASC';
        // Using getElementById with a null check since the element might not exist
        const sortOrderLabel = document.getElementById('sortOrderLabel');
        if (sortOrderLabel) {
            sortOrderLabel.textContent = currentSortOrder === 'ASC' ? 'Ascending' : 'Descending';
        }
        
        const sortButton = document.getElementById('sortButton');
        if (sortButton) {
            sortButton.textContent = 'Sort Events: ' + (currentSortOrder === 'ASC' ? 'Asc' : 'Des');
        }
    });
</script>

</body>
</html>