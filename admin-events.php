<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "do_gentri"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch events
$sql = "SELECT title, description, event_mode, start_datetime, end_datetime, venue FROM events"; // Adjust the query based on your table structure
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="styles/events-admin.css" rel="stylesheet"> <!-- Link to the external CSS file -->
    <title>Events</title>
</head>

<body>
<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="menu">
            <a href="dashboard-admin.php"><i class="fas fa-home mr-3"></i>Home</a>
            <a href="events-admin.php" class="active"><i class="fas fa-calendar-alt mr-3"></i>Events</a>
            <a href="users-admin.php"><i class="fas fa-users mr-3"></i>Users</a>
            <a href="notif-admin.php"><i class="fas fa-bell mr-3"></i>Notification</a>
            <br><br><br><br><br><br><br><br><br><br><br><br><br>
            <a href="profile-admin.php"><i class="fas fa-user-circle mr-3"></i>Profile</a>
        </div>
    </div>

    <div class="content">
        <div class="content-header">
            <img src="DO-LOGO.png" width="70px" height="70px">
            <p>Learning and Development</p>
            <h1>EVENT MANAGEMENT SYSTEM</h1>
        </div><br><br><br><br><br>

        <div class="content-body">
            <a class="join-btn" href="">CREATE AN EVENT</a>

            <div class="content-body">
                <h1>Events</h1>
                <hr><br><br>

                <div class="content-area">
                    <div class="events-section">
                        <?php
                        if ($result->num_rows > 0) {
                            // Output data of each row
                            while($row = $result->fetch_assoc()) {
                                echo '<div class="event">';
                                echo '<div class="event-content">';
                                echo '<h3>' . $row["title"] . '</h3>';
                                echo '<p>' . $row["description"] . '</p>';
                                echo '</div>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p>No events</p>';
                        }
                        $conn->close();
                        ?>
                    </div>

                    <div class="details-section">
                        <h2>Digital Teaching Strategies <br> for 21st Century Learners</h2>
                        <hr>
                        <br>
                        <div class="event-target">
                            <p>Target Participants: sample</p>
                            <p>Event Mode: Face-to-face</p>
                        </div>
                        <br><br>
                        <div class="event-date">
                            <p>Date &amp; Time: March 15, 2025 at 9:00 AM</p>
                            <p>Location: DepEd Division of General Trias City - Conference Hall</p>
                        </div>
                        <br><br><br>
                        <div class="notification">
                            <div class="event-desc">
                                <p>Description: A training program designed to equip educators with innovative teaching strategies using digital tools and technology in the classroom.</p>
                            </div>
                        </div>
                        <hr>
                        <br><br>
                        <div class="reg-text">
                            <p>Registered <br>Participants:</p><p class="reg-part">50</p><br><br>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>