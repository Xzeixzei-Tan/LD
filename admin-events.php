<?php
require_once 'config.php';

// Get the current date and time
$currentDateTime = date('Y-m-d H:i:s');

// Debugging: Output the current date and time
error_log("Current Date and Time: " . $currentDateTime);

// SQL query to fetch event details, meal plan, and count registered users
$sql = "SELECT 
            e.id, e.title, e.event_specification, e.delivery, 
            e.start_datetime, e.end_datetime, e.venue,
            (SELECT COUNT(*) FROM registered_users ru WHERE ru.event_id = e.id) AS user_count,
            GROUP_CONCAT(DISTINCT fs.source SEPARATOR ', ') AS funding_sources,  
            GROUP_CONCAT(DISTINCT s.speaker_name SEPARATOR ', ') AS speakers,  
            GROUP_CONCAT(DISTINCT CONCAT(ep.school_level, ' ', ep.specialization) SEPARATOR ', ') AS eligible_participants,  
            GROUP_CONCAT(DISTINCT mp.day SEPARATOR ', ') AS meal_days, 
            GROUP_CONCAT(DISTINCT mp.meal_type SEPARATOR ', ') AS meal_types,  -- This fetches the meal plan details
            CASE 
                WHEN NOW() BETWEEN e.start_datetime AND e.end_datetime THEN 'Ongoing'
                ELSE 'Upcoming'
            END AS status
        FROM events e
        LEFT JOIN funding_sources fs ON e.id = fs.event_id
        LEFT JOIN speakers s ON e.id = s.event_id
        LEFT JOIN eligible_participants ep ON e.id = ep.event_id
        LEFT JOIN meal_plan mp ON e.id = mp.event_id
        WHERE e.end_datetime >= NOW()
        GROUP BY e.id
        ORDER BY e.start_datetime ASC";

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
            <a href="#archive" class="archive-link">
                <i class="fas fa-archive"></i> Archive
            </a>
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
                            echo '<p><strong>Event Specification:</strong> ' . htmlspecialchars(substr($row["event_specification"], 0, 100)) . '</p>';
                            if ($row["status"] === "Ongoing") {
                                echo '<span class="ongoing-label">Ongoing</span>';
                            }
                            echo '</div></a>';
                            echo '</div>';
                        }
                    } else {
                        echo "<p>No upcoming or ongoing events found.</p>";
                    }
                    ?>
                </div>

                <div class="details-section" id="details-section">
                    <i class="fas fa-expand expand-btn" onclick="toggleExpand()"></i>
                    <h2>Details</h2>
                    <div class="detail-item">
                        <h3 id="detail-title"></h3>
                        <h4>Event Specification:</h4>
                        <p id="detail-event_specification"></p>
                    </div>
                    <div class="detail-item">
                        <h3>Delivery:</h3>
                        <p id="detail-delivery"></p>
                    </div>
                    <div class="detail-item">
                        <h3>Start:</h3>
                        <p id="detail-start"></p>
                    </div>
                    <div class="detail-item">
                        <h3>End:</h3>
                        <p id="detail-end"></p>
                    </div>
                    <div class="detail-item">
                        <h3>Venue:</h3>
                        <p id="detail-venue"></p>
                    </div>
                    <div class="detail-item">
                        <h3>Registered Users:</h3>
                        <p id="detail-user_count"></p> <!-- New element for user count -->
                    </div>
                    <div class="detail-item">
                        <h3>Funding Sources:</h3>
                        <p id="detail-funding_sources"></p> <!-- New element for funding sources -->
                    </div>
                    <div class="detail-item">
                        <h3>Speakers:</h3>
                        <p id="detail-speakers"></p> <!-- New element for speakers -->
                    </div>
                    <div class="detail-item">
                        <h3>Eligible Participants:</h3>
                        <p id="detail-eligible_participants"></p> <!-- New element for eligible participants -->
                    </div>
                    <div class="detail-item">
                        <h3>Meal Plan:</h3>
                        <p id="detail-meal_plan"></p> <!-- New element for meal plan -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentEvent = null;

function showDetails(eventData) {
    const detailsSection = document.getElementById('details-section');
    const eventsSection = document.querySelector('.events-section');

    if (currentEvent === eventData.id) {
        detailsSection.style.display = 'none';
        eventsSection.classList.remove('shrink');
        currentEvent = null;
    } else {
        document.getElementById('detail-title').textContent = eventData.title;
        document.getElementById('detail-event_specification').textContent = eventData.event_specification;
        document.getElementById('detail-delivery').textContent = eventData.delivery;
        document.getElementById('detail-start').textContent = eventData.start_datetime;
        document.getElementById('detail-end').textContent = eventData.end_datetime;
        document.getElementById('detail-venue').textContent = eventData.venue || "Not specified";
        document.getElementById('detail-user_count').textContent = eventData.user_count;
        document.getElementById('detail-funding_sources').textContent = eventData.funding_sources || "Not specified";
        document.getElementById('detail-speakers').textContent = eventData.speakers || "Not specified";
        document.getElementById('detail-eligible_participants').textContent = eventData.eligible_participants || "Not specified";
        
        // Display the meal plan information
        if (eventData.meal_days && eventData.meal_types) {
            const mealDays = eventData.meal_days.split(', ').join(', ');
            const mealTypes = eventData.meal_types.split(', ').join(', ');
            document.getElementById('detail-meal_plan').textContent = `${mealDays}: ${mealTypes}`;
        } else {
            document.getElementById('detail-meal_plan').textContent = "Not specified";
        }

        detailsSection.style.display = 'block';
        eventsSection.classList.add('shrink');
        currentEvent = eventData.id;
    }
}

function selectEvent(event) {
    document.querySelectorAll('.event').forEach(div => {
        div.classList.remove('selected');
    });

    event.currentTarget.classList.add('selected');
}

function toggleExpand() {
    let detailsSection = document.getElementById('details-section');
    let eventsSection = document.querySelector('.events-section');
    let expandIcon = document.querySelector('.expand-btn');

    if (detailsSection.classList.contains('expand')) {
        detailsSection.classList.remove('expand');
        eventsSection.classList.remove('hidden');
        expandIcon.classList.replace('fa-compress', 'fa-expand');
    } else {
        detailsSection.classList.add('expand');
        eventsSection.classList.add('hidden');
        expandIcon.classList.replace('fa-expand', 'fa-compress');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const eventDivs = document.querySelectorAll('.event');
    eventDivs.forEach(div => {
        div.addEventListener('click', selectEvent);
    });
});
</script>

</body>
</html>

<?php
$conn->close();
?>
