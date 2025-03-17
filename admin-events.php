<?php
require_once 'config.php';

// Get the current date and time
$currentDateTime = date('Y-m-d H:i:s');
error_log("Current Date and Time: " . $currentDateTime);

// Auto-update archived status (as a fallback to the MySQL event)
$updateArchivedSQL = "UPDATE events SET archived = 1 WHERE end_date < NOW() AND archived = 0";
$conn->query($updateArchivedSQL);

// Check if we're viewing archived events
$viewArchived = isset($_GET['view']) && $_GET['view'] === 'archived';

// Base SQL query to fetch event details
// In your SQL query, make sure the event_days_data portion is included:
$baseSQL = "SELECT 
            e.id, e.title, e.specification, e.delivery, 
            e.start_date, e.end_date, e.venue, e.archived,
            (SELECT COUNT(*) FROM registered_users ru WHERE ru.event_id = e.id) AS user_count,
            GROUP_CONCAT(DISTINCT CONCAT(fs.source, ' -  ₱', FORMAT(fs.amount, 2), '') SEPARATOR ', ') AS funding_sources,
            GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ') AS speakers,  
            GROUP_CONCAT(DISTINCT 
                CASE 
                    WHEN ep.target = 'school' THEN 'School'
                    WHEN ep.target = 'division' THEN 'Division'
                    WHEN ep.target = 'all' THEN 'All Personnel'
                END 
                SEPARATOR ', ') AS participant_types,
            GROUP_CONCAT(DISTINCT 
                CONCAT(ep.id, ':', ep.target, ':')
                SEPARATOR '||') AS eligible_participants_data,
            GROUP_CONCAT(DISTINCT CONCAT(mp.day_date, ':', mp.meal_types) SEPARATOR '||') AS meal_plan_data,
            GROUP_CONCAT(DISTINCT mp.meal_types SEPARATOR ', ') AS meal_types,
            GROUP_CONCAT(DISTINCT 
                CONCAT(ed.day_number, ':', DATE_FORMAT(ed.day_date, '%Y-%m-%d'), ':', 
                       TIME_FORMAT(ed.start_time, '%H:%i'), ':', 
                       TIME_FORMAT(ed.end_time, '%H:%i'))
                SEPARATOR '||') AS event_days_data,
            CASE 
                WHEN NOW() BETWEEN e.start_date AND e.end_date THEN 'Ongoing'
                WHEN e.archived = 1 THEN 'Archived'
                ELSE 'Upcoming'
            END AS status
        FROM events e
        LEFT JOIN funding_sources fs ON e.id = fs.event_id
        LEFT JOIN speakers s ON e.id = s.event_id
        LEFT JOIN eligible_participants ep ON e.id = ep.event_id
        LEFT JOIN meal_plan mp ON e.id = mp.event_id
        LEFT JOIN event_days ed ON e.id = ed.event_id";

// Add the WHERE clause based on whether we're viewing archived events
if ($viewArchived) {
    $sql = $baseSQL . " WHERE e.archived = 1 GROUP BY e.id ORDER BY e.end_date DESC";
    $pageTitle = "Archived Events";
} else {
    $sql = $baseSQL . " WHERE e.archived = 0 AND e.end_date >= NOW() GROUP BY e.id ORDER BY e.start_date ASC";
    $pageTitle = "Upcoming and Ongoing Events";
}

$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
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

// Function to get specific participants for an eligible participant ID
function getSpecificParticipants($conn, $eligibleId, $target) {
    $participants = [];
    
    if ($target === 'School') {
        // First get the basic participant info
        $sql = "SELECT sp.id, sl.name as school_level_name, c.name as type_name, sp.specialization as specialization_ids 
                FROM school_participants sp
                LEFT JOIN school_level sl ON sp.school_level = sl.id
                LEFT JOIN classification c ON sp.type = c.id
                WHERE sp.eligible_participant_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $eligibleId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            // Check if specialization_ids contains multiple values
            $specialization_names = [];
            
            if (!empty($row['specialization_ids'])) {
                // Split the comma-separated specialization IDs
                $spec_ids = explode(',', $row['specialization_ids']);
                
                // For each ID, get the name from the specialization table
                foreach ($spec_ids as $spec_id) {
                    $spec_id = trim($spec_id); // Remove any whitespace
                    if (!empty($spec_id)) {
                        $spec_sql = "SELECT name FROM specialization WHERE id = ?";
                        $spec_stmt = $conn->prepare($spec_sql);
                        $spec_stmt->bind_param("i", $spec_id);
                        $spec_stmt->execute();
                        $spec_result = $spec_stmt->get_result();
                        
                        if ($spec_row = $spec_result->fetch_assoc()) {
                            $specialization_names[] = $spec_row['name'];
                        }
                        
                        $spec_stmt->close();
                    }
                }
            }
            
            $participants[] = [
                'level' => $row['school_level_name'],
                'type' => $row['type_name'],
                'specialization' => !empty($specialization_names) ? implode(', ', $specialization_names) : 'N/A'
            ];
        }
    } elseif ($target === 'Division') {
        $sql = "SELECT department_name FROM division_participants WHERE eligible_participant_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $eligibleId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $participants[] = [
                '' => $row['department_name']
            ];
        }
    }
    
    return $participants;
}

// Function to get registered users for an event
function getRegisteredUsers($conn, $eventId) {
    $users = [];
    
    $sql = "SELECT 
                ru.id AS registration_id, 
                ru.registration_date,
                CONCAT(u.first_name, ' ', 
                    CASE WHEN u.middle_name IS NOT NULL AND u.middle_name != '' THEN CONCAT(u.middle_name, ' ') ELSE '' END,
                    u.last_name,
                    CASE WHEN u.suffix IS NOT NULL AND u.suffix != '' THEN CONCAT(' ', u.suffix) ELSE '' END
                ) AS name,
                u.email,
                u.contact_no AS phone,
                cp.name AS position,
                cp.classification_id
            FROM registered_users ru
            JOIN users u ON ru.user_id = u.id
            LEFT JOIN users_lnd ul ON ru.user_id = ul.user_id
            LEFT JOIN class_position cp ON ul.position_id = cp.id
            WHERE ru.event_id = ?
            ORDER BY ru.registration_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Create designation from position and classification if available
        $designation = '';
        if (!empty($row['position'])) {
            $designation = $row['position'];
            if (!empty($row['classification'])) {
                $designation .= ' (' . $row['classification'] . ')';
            }
        }
        
        $users[] = [
            'id' => $row['registration_id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'designation' => $designation,
            'registration_date' => $row['registration_date']
        ];
    }
    
    return $users;
}

// Prepare eligibleParticipantsData for each event
$eventsData = [];
while ($row = $result->fetch_assoc()) {
    // Process eligible participants data
    $eligibleData = [];
    if (!empty($row['eligible_participants_data'])) {
        $participantGroups = explode('||', $row['eligible_participants_data']);
        
        foreach ($participantGroups as $group) {
            if (empty($group)) continue;
            
            $parts = explode(':', $group);
            if (count($parts) >= 2) {
                $id = $parts[0];
                $target = $parts[1];
                
                $specificParticipants = [];
                if ($target === 'School' || $target === 'Division') {
                    $specificParticipants = getSpecificParticipants($conn, $id, $target);
                }
                
                $eligibleData[] = [
                    'id' => $id,
                    'target' => $target,
                    'specificParticipants' => $specificParticipants
                ];
            }
        }
    }
    
    $row['processed_eligible_data'] = json_encode($eligibleData);
    $eventsData[] = $row;
}

// Format the event days data for each event
foreach ($eventsData as &$event) {
    if (isset($event['event_days_data'])) {
        $event['formatted_event_days'] = formatEventDaysData($event['event_days_data']);
    } else {
        $event['formatted_event_days'] = "No specific days information available";
    }
}
unset($event); // Break the reference to the last element

// After fetching event data, add this code
$eventsWithUsers = [];
foreach ($eventsData as $event) {
    // Get registered users for this event
    $users = getRegisteredUsers($conn, $event['id']);
    $eventsWithUsers[$event['id']] = $users;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="styles/admin-events.css" rel="stylesheet">
    <script src="scripts/admin-events.js" defer></script>
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
            float: right;
            
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            margin-left: 10px;
        }
        
        .download-btn {
            background-color: #2b3a8f;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 15px;
            display: flex;
            align-items: center;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .download-btn:hover {
            background-color: #374ab6;
        }
        
        .download-btn i {
            margin-right: 8px;
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
        </div>
    </div>

    <div class="content">
        <div class="content-header">
            <img src="styles/photos/DO-LOGO.png" width="70px" height="70px">
            <p>Learning and Development</p>
            <h1>EVENT MANAGEMENT SYSTEM</h1>
        </div><br><br><br>

        <div class="content-body">
            <h1><?php echo $pageTitle; ?></h1>
            <hr><br>

            <?php if (!$viewArchived): ?>
                <a class="create-btn" href="admin-create_events.php">Create an Event!</a>
            <?php endif; ?>

            <div class="archive-toggle">
                <a href="admin-events.php" class="<?php echo !$viewArchived ? 'active' : ''; ?>">Current Events</a>
                <a href="admin-events.php?view=archived" class="<?php echo $viewArchived ? 'active' : ''; ?>">Archived Events</a>
            </div>

            <div class="content-area">
                <div class="events-section">
                    <?php
                    if (count($eventsData) > 0) {
                        foreach ($eventsData as $row) {
                            echo '<a class="events-btn" href="javascript:void(0);" onclick="showDetails(' . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . ')">';
                            echo '<div class="event">';
                            echo '<div class="event-content">';
                            echo '<h3>' . htmlspecialchars($row["title"]) . '</h3>';
                            echo '<p>'. '<strong>Event Specification: '. '</strong>' . htmlspecialchars($row["specification"]) . '</p>';
                            echo '<div class="event-dates">'.'<p>' . '<strong><i class="fas fa-calendar-day"></i>Date: '. '</strong>' . date('M d, Y', strtotime($row["start_date"])) . '</p>'. '</div>';
                            echo '<span class="status-badge status-' . strtolower($row["status"]) . '">';
                            if(strtolower($row["status"]) == "upcoming") {
                                echo '<i class="fas fa-hourglass-start"></i> ';
                            } else if(strtolower($row["status"]) == "ongoing") {
                                echo '<i class="fas fa-circle"></i>';
                            } else {
                                echo '<i class="fas fa-check-circle"></i> ';
                            }
                            echo htmlspecialchars($row["status"]) . '</span>';
                            echo '</div>';
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
                    <div class="details-section-header">
                    <button class="expand-btn" onclick="toggleExpand()"><i class="fas fa-expand"></i></button>
                    <h2>Details</h2>
                    <?php if ($viewArchived): ?>
                    <div class="detail-item">
                        <button onclick="unarchiveEvent()" id="unarchive-btn" style="display:none;">Unarchive Event</button>
                    </div>
                    <?php else: ?>
                    <div class="detail-item">
                        <button onclick="archiveEvent()" id="archive-btn" style="display:none;">Archive Event</button>
                    </div>
                    <?php endif; ?>
                    </div>
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
                            <h4>Event Schedule:</h4>
                            <p id="detail-event-days"></p>
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
                        <div class="detail-item">
                            <button class="download-btn" onclick="downloadParticipantsList()" id="download-btn" style="display:none;">
                                <i class="fas fa-download"></i> List of Registered Participants
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Add this new section for registered users table -->
                 
                <div class="detail-item expanded-content" style="width: 100%;">
                    <h4>Registered Users:</h4>
                    <p id="detail-user_count"></p>

                    <div id="registered-users-table-container" style="max-height: 300px; overflow-y: auto;">
                        <table id="registered-users-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Name</th>
                                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Email</th>
                                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Phone</th>
                                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Designation</th>
                                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Registration Date</th>
                                </tr>
                            </thead>
                            <tbody id="registered-users-table-body">
                                <!-- Data will be populated via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
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
        const registeredUsersData = <?php echo json_encode($eventsWithUsers); ?>;

        if (currentEvent === eventData.id) {
            detailsSection.style.display = 'none';
            eventsSection.classList.remove('shrink');
            currentEvent = null;
        } else {
            document.getElementById('detail-title').textContent = eventData.title;
            document.getElementById('detail-event_specification').textContent = eventData.specification;
            document.getElementById('detail-delivery').textContent = eventData.delivery;
            document.getElementById('detail-start').textContent = eventData.start_date;
            document.getElementById('detail-end').textContent = eventData.end_date;
            // In your showDetails function:
            document.getElementById('detail-event-days').innerHTML = eventData.formatted_event_days || "No specific days information available";
            document.getElementById('detail-status').textContent = eventData.status;
            document.getElementById('detail-venue').textContent = eventData.venue || "Not specified";
            document.getElementById('detail-user_count').textContent = eventData.user_count;
            document.getElementById('detail-funding_sources').textContent = eventData.funding_sources || "Not specified";
            document.getElementById('detail-speakers').textContent = eventData.speakers || "Not specified";
            
            
            // Fetch registered users for this event
            fetchRegisteredUsers(eventData.id);
            // Process the eligible participants data
            // Update the part in the showDetails function that displays eligible participants

            let participantDetails = '';

            try {
                if (eventData.processed_eligible_data) {
                    const eligibleData = JSON.parse(eventData.processed_eligible_data);

                    eligibleData.forEach(participant => {
                        if (participant.target === 'School') {
                            participantDetails += `<strong>School:</strong><br>`;
                            if (participant.specificParticipants && participant.specificParticipants.length > 0) {
                                participant.specificParticipants.forEach(school => {
                                    if (typeof school === 'object') {
                                        // Modified to display the actual names fetched from the database
                                        participantDetails += ` Level: ${school.level || 'N/A'} <br> Type: ${school.type || 'N/A'} <br> Specialization: ${school.specialization || 'N/A'}<br>`;
                                    } else {
                                        // Fallback for legacy data structure
                                        participantDetails += `- ${school}<br>`;
                                    }
                                });
                                participantDetails += `<br>`;
                            } else {
                                participantDetails += '<em>All Schools</em><br><br>';
                            }
                        } 
                        else if (participant.target === 'Division') {
                            participantDetails += `<strong>Deparment/Unit:</strong><br>`;
                            if (participant.specificParticipants && participant.specificParticipants.length > 0) {
                                participant.specificParticipants.forEach(division => {
                                    if (typeof division === 'object') {
                                        // Modified to display division object properties if they exist
                                        const divisionProps = Object.entries(division)
                                            .map(([key, value]) => `${key ? key : ''}${value || 'N/A'}`)
                                            .join(', ');
                                        participantDetails += ` ${divisionProps}<br>`;
                                    } else {
                                        // Fallback for string values
                                        participantDetails += `: ${division}<br>`;
                                    }
                                });
                                participantDetails += `<br>`;
                            } else {
                                participantDetails += '<em>All Departments/Units</em><br><br>';
                            }
                        } else if (participant.target === 'all') {
                            participantDetails += `<strong>All Personnel</strong><br><br>`;
                        }
                    });
                }
            } catch (error) {
                console.error("Error parsing eligible participants data:", error);
                participantDetails = "Error displaying participant data";
            }

            // Now update the registered users table
            const tableBody = document.getElementById('registered-users-table-body');
            tableBody.innerHTML = ''; // Clear previous content
            
            const users = registeredUsersData[eventData.id] || [];
            
            if (users.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No registered users found</td></tr>';
            } else {
                users.forEach(user => {
                    const row = document.createElement('tr');
                    
                    // Format the registration date
                    const regDate = new Date(user.registration_date);
                    const formattedDate = regDate.toLocaleString();
                    
                    row.innerHTML = `
                        <td style="border: 1px solid #ddd; padding: 8px;">${user.name}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">${user.email}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">${user.phone || 'N/A'}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">${user.designation || 'N/A'}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">${formattedDate}</td>
                    `;
                    
                    tableBody.appendChild(row);
                });
            }
            
            // Show download button and set event ID
            const downloadBtn = document.getElementById('download-btn');
            downloadBtn.style.display = 'block';
            downloadBtn.setAttribute('data-id', eventData.id);

            document.getElementById('detail-eligible_participants').innerHTML = participantDetails;

            // Display the meal plan information
            if (eventData.meal_plan_data) {
                const mealPlanItems = eventData.meal_plan_data.split('||');
                let mealPlanText = '';
                
                mealPlanItems.forEach(item => {
                    // Each item is in the format "date:meal_types"
                    if (item && item.includes(':')) {
                        mealPlanText += `${item.replace(':', ': ')}<br>`;
                    }
                });
                
                document.getElementById('detail-meal_plan').innerHTML = mealPlanText;
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

function fetchRegisteredUsers(eventId) {
    // Show loading indicator
    document.getElementById('registered-users-table-body').innerHTML = '<tr><td colspan="5" style="text-align: center;">Loading...</td></tr>';
    
    // Fetch registered users using AJAX
    fetch('get_registered_users.php?event_id=' + eventId)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('registered-users-table-body');
            
            // Clear loading indicator
            tableBody.innerHTML = '';
            
            if (data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No registered users found</td></tr>';
                return;
            }
            
            // Populate table with user data
            data.forEach(user => {
                const row = document.createElement('tr');
                
                // Format the registration date
                const regDate = new Date(user.registration_date);
                const formattedDate = regDate.toLocaleString();
                
                row.innerHTML = `
                    <td style="border: 1px solid #ddd; padding: 8px;">${user.name}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">${user.email}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">${user.phone || 'N/A'}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">${user.designation || 'N/A'}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">${formattedDate}</td>
                `;
                
                tableBody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error fetching registered users:', error);
            document.getElementById('registered-users-table-body').innerHTML = 
                '<tr><td colspan="5" style="text-align: center;">Error loading registered users</td></tr>';
        });
}

function downloadParticipantsList() {
    const eventId = document.getElementById('download-btn').getAttribute('data-id');
    if (eventId) {
        window.location.href = 'download_participants.php?event_id=' + eventId;
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
    let expandIcon = document.querySelector('.expand-btn i');
    let expandedContent = document.querySelectorAll('.expanded-content');

    if (detailsSection.classList.contains('expand')) {
        // Collapse
        detailsSection.classList.remove('expand');
        eventsSection.classList.remove('hidden');
        expandIcon.classList.replace('fa-compress', 'fa-expand');
        expandedContent.forEach(content => content.style.display = 'none');
    } else {
        // Expand
        detailsSection.classList.add('expand');
        eventsSection.classList.add('hidden');
        expandIcon.classList.replace('fa-expand', 'fa-compress');
        expandedContent.forEach(content => content.style.display = 'block');
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