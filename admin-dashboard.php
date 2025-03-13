<?php
require_once 'config.php';

// Set the default sort order to ASC (Soonest events first)
$sortOrder = isset($_GET['sort']) && ($_GET['sort'] == 'DESC') ? 'DESC' : 'ASC';

// Fetch upcoming and ongoing events from the database
$sql = "SELECT id, title, event_specification, start_datetime, end_datetime,
        CASE 
            WHEN NOW() BETWEEN start_datetime AND end_datetime THEN 'Ongoing'
            ELSE 'Upcoming'
        END AS status
        FROM events 
        WHERE end_datetime >= NOW()
        ORDER BY start_datetime $sortOrder";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Fetch notifications for admin
$notif_query = "SELECT message, created_at, is_read FROM notifications WHERE notification_type = 'admin' ORDER BY created_at DESC";
$notif_result = $conn->query($notif_query);
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

<div class="sidebar">
        
        <div class="menu">
            <a href="admin-dashboard.php" class="active"><i class="fas fa-home"></i>Home</a>
            <a href="admin-events.php"><i class="fas fa-calendar-alt"></i>Events</a>
            <a href="admin-users.php"><i class="fas fa-users"></i>Users</a>
            <a href="admin-notif.php"><i class="fas fa-bell"></i>Notification</a> 
        </div>
    </div>

<div class="content">
    <div class="content-header">
        <img src="styles/photos/DO-LOGO.png" width="70px" height="70px">
        <p>Learning and Development</p>
        <h1>EVENT MANAGEMENT SYSTEM</h1>
    </div><br><br><br><br><br>


            <div class="content-body">
                <h1>Welcome, Admin!</h1>
                <hr><br><br>

                <div class="content-area">
                    <div class="events-section">
                        <h2>Events</h2>
                        <div class="sort-events">
                            <!-- Sort Button to Toggle Order -->
                            <button id="sortButton" onclick="toggleSortOrder()">Sort Events: <?php echo $sortOrder === 'ASC' ? 'Asc' : 'Des'; ?></button>
                        </div>
                        <?php while ($event = $result->fetch_assoc()) : ?>
                            <div class="event">
                                <div class="event-content">
                                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                    <p><strong>Event Specification:</strong> <?php echo htmlspecialchars($event['event_specification']); ?></p>
                                    <p><strong>Start Date:</strong> <?php echo htmlspecialchars($event['start_datetime']); ?></p>
                                    <?php if ($event['status'] === 'Ongoing') : ?>
                                        <span class="ongoing-label">Ongoing</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Notifications Section -->
                    <div class="notifications-section">
                        <h2>Notifications</h2>
                        <?php while ($notif = $notif_result->fetch_assoc()): ?>
                        <div class="notification important">
                            <a id="events-btn" class="<?php echo $notif['is_read'] ? 'read' : 'unread'; ?>" href="select_quiz.php">
                                <div class="notification-content">
                                    <p><?php echo htmlspecialchars($notif['message']); ?></p>
                                    <br><small><?php echo $notif['created_at']; ?></small>
                                <?php endwhile; ?>    
                                </div>
                            </a>
                        </div>
                        <!-- More notifications here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
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
        document.getElementById('sortOrderLabel').textContent = currentSortOrder === 'ASC' ? 'Ascending' : 'Descending';
        document.getElementById('sortButton').textContent = 'Sort Events: ' + (currentSortOrder === 'ASC' ? 'Asc' : 'Des');
    });
</script>
</body>
</html>