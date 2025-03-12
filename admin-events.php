<?php
require_once 'config.php';

// Get the current date and time
$currentDateTime = date('Y-m-d H:i:s');
error_log("Current Date and Time: " . $currentDateTime);

// SQL query to fetch event details, funding, speakers, participants, and meal plans
$sql = "SELECT 
            e.id, e.title, e.specification, e.delivery, e.venue, 
            e.start_date, e.end_date, 
            (SELECT COUNT(*) FROM registered_users r WHERE r.event_id = e.id) AS participant_count,
            GROUP_CONCAT(DISTINCT f.source SEPARATOR ', ') AS funding_sources,
            GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ') AS speakers,
            GROUP_CONCAT(DISTINCT 
                CASE 
                    WHEN p.type = 'school' THEN CONCAT(p.level, ' ', p.specialization)
                    WHEN p.type = 'division' THEN p.division_position
                    WHEN p.type = 'all' THEN 'All Personnel'
                END 
                SEPARATOR ', ') AS eligible_participants,
            GROUP_CONCAT(DISTINCT m.day SEPARATOR ', ') AS meal_days,
            GROUP_CONCAT(DISTINCT m.meal_type SEPARATOR ', ') AS meal_types,
            CASE 
                WHEN NOW() BETWEEN e.start_date AND e.end_date THEN 'Ongoing'
                ELSE 'Upcoming'
            END AS status
        FROM events e
        LEFT JOIN funding_sources f ON e.id = f.event_id
        LEFT JOIN speakers s ON e.id = s.event_id
        LEFT JOIN eligible_participants p ON e.id = p.event_id
        LEFT JOIN meal_plan m ON e.id = m.event_id
        WHERE e.end_date >= NOW()
        GROUP BY e.id
        ORDER BY e.start_date ASC";

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
    <link href="styles/admin-events.css" rel="stylesheet">
    <title>Event Management System</title>
</head>
<body>

<div class="container">
    <div class="sidebar">
        <div class="menu">
            <a href="admin-dashboard.php"><i class="fas fa-home mr-3"></i>Home</a>
            <a href="admin-events.php" class="active"><i class="fas fa-calendar-alt mr-3"></i>Events</a>
            <a href="admin-users.php"><i class="fas fa-users mr-3"></i>Users</a>
            <a href="admin-notif.php"><i class="fas fa-bell mr-3"></i>Notification</a>
            <a href="#archive" class="archive-link"><i class="fas fa-archive"></i> Archive</a>
        </div>
    </div>

    <div class="content">
        <div class="content-header">
            <img src="styles/photos/DO-LOGO.png" width="70px" height="70px">
            <p>Learning and Development</p>
            <h1>EVENT MANAGEMENT SYSTEM</h1>
        </div>

        <div class="content-body">
            <h1>Events</h1>
            <hr>
            <a class="create-btn" href="admin-create_events.php">Create an Event!</a>

            <div class="content-area">
                <div class="events-section">
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<a class="events-btn" href="javascript:void(0);" onclick="showDetails(' . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . ')">';
                            echo '<div class="event">';
                            echo '<div class="event-content">';
                            echo '<h3>' . htmlspecialchars($row["title"]) . '</h3>';
                            if ($row["status"] === "Ongoing") {
                                echo '<span class="ongoing-label">Ongoing</span>';
                            }
                            echo '</div>';
                            echo '<a class="update-btn" href="admin-create_events.php?id=' . $row["id"] . '">Update</a>';
                            echo '</div></a>';
                        }
                    } else {
                        echo "<p>No upcoming or ongoing events found.</p>";
                    }
                    ?>
                </div>
                <div class="details-section" id="details-section">
                    <h2>Details</h2>
                    <div class="detail-item"><h3 id="detail-title"></h3></div>
                    <div class="detail-item"><h3>Format:</h3><p id="detail-format"></p></div>
                    <div class="detail-item"><h3>Start:</h3><p id="detail-start"></p></div>
                    <div class="detail-item"><h3>End:</h3><p id="detail-end"></p></div>
                    <div class="detail-item"><h3>Location:</h3><p id="detail-location"></p></div>
                    <div class="detail-item"><h3>Participants:</h3><p id="detail-participant_count"></p></div>
                    <div class="detail-item"><h3>Funding Sources:</h3><p id="detail-funding_sources"></p></div>
                    <div class="detail-item"><h3>Speakers:</h3><p id="detail-speakers"></p></div>
                    <div class="detail-item"><h3>Eligible Participants:</h3><p id="detail-eligible_participants"></p></div>
                    <div class="detail-item"><h3>Meal Plan:</h3><p id="detail-meal_plan"></p></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showDetails(eventData) {
    document.getElementById('detail-title').textContent = eventData.title;
    document.getElementById('detail-description').textContent = eventData.description;
    document.getElementById('detail-format').textContent = eventData.format;
    document.getElementById('detail-start').textContent = eventData.start_datetime;
    document.getElementById('detail-end').textContent = eventData.end_datetime;
    document.getElementById('detail-location').textContent = eventData.location || "Not specified";
    document.getElementById('detail-participant_count').textContent = eventData.participant_count;
    document.getElementById('detail-funding_sources').textContent = eventData.funding_sources || "Not specified";
    document.getElementById('detail-speakers').textContent = eventData.speakers || "Not specified";
    document.getElementById('detail-eligible_participants').textContent = eventData.eligible_participants || "Not specified";
    document.getElementById('detail-meal_plan').textContent = eventData.meal_days && eventData.meal_types ? eventData.meal_days + ': ' + eventData.meal_types : "Not specified";
}
</script>
</body>
</html>
<?php
$conn->close();
?>