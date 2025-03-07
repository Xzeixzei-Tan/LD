<?php
require_once 'config.php';

// Set the default sort order to ASC (Soonest events first)
$sortOrder = isset($_GET['sort']) && ($_GET['sort'] == 'DESC') ? 'DESC' : 'ASC';

// Fetch only upcoming events from the database (ignores past events)
$sql = "SELECT id, title, event_specification, start_datetime FROM events WHERE start_datetime >= NOW() ORDER BY start_datetime $sortOrder";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
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

	<div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-content">
                <div class="menu">
                    <a href="admin-dashboard.php" class="active"><i class="fas fa-home mr-3"></i>Home</a>
                    <a href="admin-events.php"><i class="fas fa-calendar-alt mr-3"></i>Events</a>
                    <a href="admin-users.php"><i class="fas fa-users mr-3"></i>Users</a>
                    <a href="admin-notif.php"><i class="fas fa-bell mr-3"></i>Notification</a> 
                </div>
            </div>
        </div>

        <!-- Main Content -->
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
                            <!-- Sort Icon to Toggle Order -->
                            <i class="fa fa-sort" id="sortIcon" onclick="toggleSortOrder()"></i>
                        </div>
                        <?php while ($event = $result->fetch_assoc()) : ?>
                            <div class="event">
                                <div class="event-content">
                                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                    <p><strong>Event Specification:</strong> <?php echo htmlspecialchars($event['event_specification']); ?></p>
                                    <p><strong>Start Date:</strong> <?php echo htmlspecialchars($event['start_datetime']); ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Notifications Section -->
                    <div class="notifications-section">
                        <h2>Notifications</h2>
                        <div class="notification important">
                            <a class="events-btn" href="select_quiz.php">
                                <div class="notification-content">
                                    <p>Your certificate from "Sample Event" is here. Download it now.</p>
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
</script>

</body>
</html>
