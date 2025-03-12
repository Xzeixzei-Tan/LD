<?php
require_once 'config.php';

// Get the current date and time
$currentDateTime = date('Y-m-d H:i:s');
error_log("Current Date and Time: " . $currentDateTime);

<<<<<<< HEAD
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
=======
// Auto-update archived status (as a fallback to the MySQL event)
$updateArchivedSQL = "UPDATE events SET archived = 1 WHERE end_datetime < NOW() AND archived = 0";
$conn->query($updateArchivedSQL);

// Check if we're viewing archived events
$viewArchived = isset($_GET['view']) && $_GET['view'] === 'archived';

// Base SQL query to fetch event details
$baseSQL = "SELECT 
            e.id, e.title, e.event_specification, e.delivery, 
            e.start_datetime, e.end_datetime, e.venue, e.archived,
            (SELECT COUNT(*) FROM registered_users ru WHERE ru.event_id = e.id) AS user_count,
            GROUP_CONCAT(DISTINCT fs.source SEPARATOR ', ') AS funding_sources,  
            GROUP_CONCAT(DISTINCT s.speaker_name SEPARATOR ', ') AS speakers,  
            GROUP_CONCAT(DISTINCT CONCAT(ep.school_level, ' ', ep.specialization) SEPARATOR ', ') AS eligible_participants,  
            GROUP_CONCAT(DISTINCT mp.day SEPARATOR ', ') AS meal_days, 
            GROUP_CONCAT(DISTINCT mp.meal_type SEPARATOR ', ') AS meal_types,
            CASE 
                WHEN NOW() BETWEEN e.start_datetime AND e.end_datetime THEN 'Ongoing'
                WHEN e.archived = 1 THEN 'Archived'
>>>>>>> 6588783dfbc6bda536b1a5be83291290a238f979
                ELSE 'Upcoming'
            END AS status
        FROM events e
        LEFT JOIN funding_sources f ON e.id = f.event_id
        LEFT JOIN speakers s ON e.id = s.event_id
<<<<<<< HEAD
        LEFT JOIN eligible_participants p ON e.id = p.event_id
        LEFT JOIN meal_plan m ON e.id = m.event_id
        WHERE e.end_date >= NOW()
        GROUP BY e.id
        ORDER BY e.start_date ASC";
=======
        LEFT JOIN eligible_participants ep ON e.id = ep.event_id
        LEFT JOIN meal_plan mp ON e.id = mp.event_id";

// Add the WHERE clause based on whether we're viewing archived events
if ($viewArchived) {
    $sql = $baseSQL . " WHERE e.archived = 1 GROUP BY e.id ORDER BY e.end_datetime DESC";
    $pageTitle = "Archived Events";
} else {
    $sql = $baseSQL . " WHERE e.archived = 0 AND e.end_datetime >= NOW() GROUP BY e.id ORDER BY e.start_datetime ASC";
    $pageTitle = "Upcoming and Ongoing Events";
}
>>>>>>> 6588783dfbc6bda536b1a5be83291290a238f979

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
    <style>
        .archive-toggle {
            margin-top: 20px;
            margin-bottom: 20px;
        }
        
        .archive-toggle a {
            padding: 8px 16px;
            background-color: #f1f1f1;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
        }
        
        .archive-toggle a.active {
            background-color: #4CAF50;
            color: white;
        }
        
        .archived-label {
            background-color: #888;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            margin-left: 10px;
        }
        
        .ongoing-label {
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            margin-left: 10px;
        }
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
<<<<<<< HEAD
            <a href="#archive" class="archive-link"><i class="fas fa-archive"></i> Archive</a>
=======
            </a>
>>>>>>> 6588783dfbc6bda536b1a5be83291290a238f979
        </div>
    </div>

    <div class="content">
        <div class="content-header">
            <img src="styles/photos/DO-LOGO.png" width="70px" height="70px">
            <p>Learning and Development</p>
            <h1>EVENT MANAGEMENT SYSTEM</h1>
        </div>

        <div class="content-body">
<<<<<<< HEAD
            <h1>Events</h1>
            <hr>
            <a class="create-btn" href="admin-create_events.php">Create an Event!</a>
=======
            <h1><?php echo $pageTitle; ?></h1>
            <hr><br>

            <?php if (!$viewArchived): ?>
                <a class="create-btn" href="admin-create_events.php">Create an Event!</a>
            <?php endif; ?>

            <div class="archive-toggle">
                <a href="admin-events.php" class="<?php echo !$viewArchived ? 'active' : ''; ?>">Current Events</a>
                <a href="admin-events.php?view=archived" class="<?php echo $viewArchived ? 'active' : ''; ?>">Archived Events</a>
            </div>
>>>>>>> 6588783dfbc6bda536b1a5be83291290a238f979

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
                                echo '<span>Ongoing...</span>';
                            }
                            echo '</div>';
                            echo '<a class="update-btn" href="admin-create_events.php?id=' . $row["id"] . '">Update</a>';
                            echo '</div></a>';
                        }
                    } else {
                        if ($viewArchived) {
                            echo "<p>No archived events found.</p>";
                        } else {
                            echo "<p>No upcoming or ongoing events found.</p>";
                        }
                    }
                    ?>
                </div>
                <div class="details-section" id="details-section">
                    <h2>Details</h2>
<<<<<<< HEAD
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
=======
                    <hr>
                    <h3 id="detail-title"></h3>
                <div class="detail-items">
                    <div class="detail-items-1">
                        <div class="detail-item">
                            <h4>Event Specification:</h4>
                            <p id="detail-event_specification"></p>
                        </div>
                        <div class="detail-item">
                            <h4>Delivery:</h4>
                            <p id="detail-delivery"></p>
                        </div>
                        <div class="detail-item">
                            <h4>Start:</h4>
                            <p id="detail-start"></p>
                        </div>
                        <div class="detail-item">
                            <h4>End:</h4>
                            <p id="detail-end"></p>
                        </div>
                        <div class="detail-item">
                            <h4>Status:</h4>
                            <p id="detail-status"></p>
                        </div>
                        <div class="detail-item">
                            <h4>Venue:</h4>
                            <p id="detail-venue"></p>
                        </div>
                    </div>
                    <div class="detail-items-2 expanded-content">
                        <div class="detail-item">
                            <h4>Registered Users:</h4>
                            <p id="detail-user_count"></p>
                        </div>
                        <div class="detail-item">
                            <h4>Funding Sources:</h4>
                            <p id="detail-funding_sources"></p>
                        </div>
                        <div class="detail-item">
                            <h4>Speakers:</h4>
                            <p id="detail-speakers"></p>
                        </div>
                        <div class="detail-item">
                            <h4>Eligible Participants:</h4>
                            <p id="detail-eligible_participants"></p>
                        </div>
                        <div class="detail-item">
                            <h4>Meal Plan:</h4>
                            <p id="detail-meal_plan"></p>
                        </div>
                    </div>
                </div>
                    <?php if ($viewArchived): ?>
                    <div class="detail-item">
                        <button onclick="unarchiveEvent()" id="unarchive-btn" style="display:none;">Unarchive Event</button>
                    </div>
                    <?php else: ?>
                    <div class="detail-item">
                        <button onclick="archiveEvent()" id="archive-btn" style="display:none;">Archive Event</button>
                    </div>
                    <?php endif; ?>
>>>>>>> 6588783dfbc6bda536b1a5be83291290a238f979
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showDetails(eventData) {
<<<<<<< HEAD
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
=======
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
        document.getElementById('detail-status').textContent = eventData.status;
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
        
        // Show/hide archive/unarchive buttons as appropriate
        const archiveBtn = document.getElementById('archive-btn');
            archiveBtn.style.display = 'block';
        const unarchiveBtn = document.getElementById('unarchive-btn');
        
        if (archiveBtn) {
            archiveBtn.setAttribute('data-id', eventData.id);
        }
        
        if (unarchiveBtn) {
            unarchiveBtn.style.display = 'block';
            unarchiveBtn.setAttribute('data-id', eventData.id);
        }
    }
}

function archiveEvent() {
    const eventId = document.getElementById('archive-btn').getAttribute('data-id');
    if (confirm('Are you sure you want to archive this event?')) {
        window.location.href = 'archive-event.php?id=' + eventId + '&action=archive';
    }
}

function unarchiveEvent() {
    const eventId = document.getElementById('unarchive-btn').getAttribute('data-id');
    if (confirm('Are you sure you want to unarchive this event?')) {
        window.location.href = 'archive-event.php?id=' + eventId + '&action=unarchive';
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

document.addEventListener('DOMContentLoaded', () => {
    const eventDivs = document.querySelectorAll('.event');
    eventDivs.forEach(div => {
        div.addEventListener('click', selectEvent);
    });
});
>>>>>>> 6588783dfbc6bda536b1a5be83291290a238f979
</script>
</body>
</html>
<?php
$conn->close();
?>