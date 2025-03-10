<?php
require_once 'config.php';

$sql = "SELECT id, title, event_specification, delivery, start_datetime, end_datetime, venue FROM events ORDER BY start_datetime DESC";
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
    <title>Dashboard-Template</title>
    <style>
        .content-area { display: flex; justify-content: space-between; }
        .details-section { display: none; flex-basis: 30%; margin-left: 20px; background-color: #f9f9f9; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .events-section { flex-basis: 100%; transition: flex-basis 0.3s; }
        .events-section.shrink { flex-basis: 70%; }
        .details-section h2 { margin-top: 0; }
        .details-section .detail-item { margin-bottom: 15px; }
        .details-section .detail-item h3 { margin: 0; font-size: 1.2em; }
        .details-section .detail-item p { margin: 5px 0 0; color: #555; }
        .expand-btn { cursor: pointer; float: right; }
        .expand { flex-basis: 100% !important; }
        .hidden { display: none; }
    </style>
</head>
<body>

<div class="container">
    <div class="sidebar">
        <div class="menu">
            <a href="admin-dashboard.php"><i class="fas fa-home mr-3"></i>Home</a>
            <a href="admin-events.php" class="active"><i class="fas fa-calendar-alt mr-3"></i>Events</a>
            <a href="admin-users.php"><i class="fas fa-users mr-3"></i>Users</a>
            <a href="admin-notif.php"><i class="fas fa-bell mr-3"></i>Notification</a>
            <a href="admin-archives.php"><i class="fa fa-archive" aria-hidden="true"></i>Archives</a>
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
                            echo '</div></a>';
                            echo '</div>';
                        }
                    } else {
                        echo "<p>No events found.</p>";
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
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showDetails(eventData) {
    document.getElementById('detail-title').textContent = eventData.title;
    document.getElementById('detail-event_specification').textContent = eventData.event_specification;
    document.getElementById('detail-delivery').textContent = eventData.delivery;
    document.getElementById('detail-start').textContent = eventData.start_datetime;
    document.getElementById('detail-end').textContent = eventData.end_datetime;
    document.getElementById('detail-venue').textContent = eventData.venue || "Not specified";

    document.getElementById('details-section').style.display = 'block';
    document.querySelector('.events-section').classList.add('shrink');
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

// Modify the PHP to add the onclick event
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
