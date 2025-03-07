<?php
require_once 'config.php'; // Ensure this is at the top

// Fetch events from the database
$sql = "SELECT id, title, description, event_mode, start_datetime, end_datetime, venue FROM events ORDER BY start_datetime ASC";
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
<style type="text/css">
    /* Add your styles here */
</style>
<body>

<div class="container">
    <div class="sidebar">
        <div class="menu">
            <a href="admin-dashboard.php" class="active"><i class="fas fa-home mr-3"></i>Home</a>
            <a href="admin-events.php"><i class="fas fa-calendar-alt mr-3"></i>Events</a>
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
	    	<h1>Welcome, Admin!</h1>
	    	<hr><br><br>

            <div class="content-area">
                <div class="events-section">
                    <h2>Events</h2>
                    <?php while ($event = $result->fetch_assoc()) : ?>
                        <div class="event">
                            <div class="event-content">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p style="margin-bottom: 5%;"><?php echo htmlspecialchars($event['description']); ?></p>
                                <p><strong>Event Mode:</strong> <?php echo htmlspecialchars($event['event_mode']); ?></p>
                                <p><strong>Start Date:</strong> <?php echo htmlspecialchars($event['start_datetime']); ?></p>
                                <p><strong>End Date:</strong> <?php echo htmlspecialchars($event['end_datetime']); ?></p>
                                <p><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="notifications-section">
                    <h2>Notifications</h2>
                    <div class="notification important">
                        <a class="events-btn" href="select_quiz.php">
                        <div class="notification-content">
                            <p>Your certificate from "Sample Event" is here. Download it now.</p>
                        </div></a>
                    </div>

                    <div class="notification">
                        <div class="notification-content">
                            <p>Sample event notification</p>
                        </div>
                    </div>

                    <div class="notification">
                        <div class="notification-content">
                            <p>Sample event notification</p>
                        </div>
                    </div>

                    <div class="notification">
                        <div class="notification-content">
                            <p>Sample event notification</p>
                        </div>
                    </div>
                </div>
            </div>
    	</div>
    </div>
</div>
</body>
</html>
