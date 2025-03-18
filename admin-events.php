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

        .highlighted-event{
            transition: background-color 0.5s;
            background-color: #374ab6;
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
                    <div class="detail-item expanded-content">
                        <button onclick="unarchiveEvent()" id="unarchive-btn" style="display:none;">Unarchive Event</button>
                    </div>
                    <?php else: ?>
                    <div class="detail-item expanded-content">
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
                            <div class="select-container">
                                <select id="detail-eligible_participants" style="width: 100%; padding: 10px; background-color: #f8f9fa; cursor: pointer; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px; margin-bottom: 10px;">
                                    <option value="">-- Select Participant Type --</option>
                                </select>
                            </div>
                            <div id="participant-details-container" class="participant-details-wrapper" style="display: none;"></div>
                        </div>
                        <div class="detail-item">
                            <h4>Meal Plan:</h4>
                            <p id="detail-meal_plan"></p>
                        </div>
                    </div>
                </div>

                <!-- Add this new section for registered users table -->
                 
                <div class="detail-item expanded-content" style="width: 100%;">
                    <div class="registered-users">
                        <h4 style="margin: 0;">Registered Users: <span id="detail-user_count" style="font-weight: normal;"></span></h4>
                    </div>
                    <button id="toggle-users-table-btn" onclick="toggleRegisteredUsersTable()" class="view-user-btn">
                        <i class="fas fa-eye"></i> View Registered Users
                    </button>
                    
                    <div id="registered-users-table-container">
                        <div class="detail-item download">
                            <button class="download-btn" onclick="downloadParticipantsList()" id="download-btn">
                                <i class="fas fa-download"></i> List of Registered Participants
                            </button>
                            <button class="download-btn" onclick="distributeCertificates()" id="distribute-btn">
                                <i class="fas fa-certificate"></i> Distribute Certificates
                            </button>
                        </div>
                        <table id="registered-users-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Designation</th>
                                    <th>Registration Date</th>
                                </tr>
                            </thead>
                            <tbody id="registered-users-table-body">
                                <!-- Data will be populated via JavaScript -->
                                <tr>
                                    <td colspan="5">Click "View Registered Users" to load data</td>
                                </tr>
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

    // Show distribute certificate button and set event ID
    const distributeBtn = document.getElementById('distribute-btn');
    distributeBtn.style.display = 'block';
    distributeBtn.setAttribute('data-id', eventData.id);

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
        document.getElementById('detail-event-days').innerHTML = eventData.formatted_event_days || "No specific days information available";
        document.getElementById('detail-status').textContent = eventData.status;
        document.getElementById('detail-venue').textContent = eventData.venue || "Not specified";
        document.getElementById('detail-user_count').textContent = eventData.user_count;
        document.getElementById('detail-funding_sources').textContent = eventData.funding_sources || "Not specified";
        document.getElementById('detail-speakers').textContent = eventData.speakers || "Not specified";
        
        // Fetch registered users for this event
        fetchRegisteredUsers(eventData.id);
        
        // In the showDetails function, replace the participant select code with this:

// Process the eligible participants data for the dropdown
const participantSelect = document.getElementById('detail-eligible_participants');
const detailsContainer = document.getElementById('participant-details-container');

// Clear previous options
participantSelect.innerHTML = '<option value="">-- Select Participant Type --</option>';
participantSelect.removeAttribute('disabled');

try {
    if (eventData.processed_eligible_data) {
        const eligibleData = JSON.parse(eventData.processed_eligible_data);
        const participantTypes = [];
        
        // Create options for the dropdown
        eligibleData.forEach((participant, index) => {
            let optionText = '';
            let iconClass = '';
            
            // Format the option text based on target type
            if (participant.target === 'School') {
                optionText = '🏫 School Personnel';
                iconClass = 'fa-school';
            } else if (participant.target === 'Division') {
                optionText = '🏢 Division Office Personnel';
                iconClass = 'fa-building';
            } else if (participant.target === 'all') {
                optionText = '👥 All Personnel';
                iconClass = 'fa-users';
            }
            
            if (optionText) {
                const option = document.createElement('option');
                option.value = index;
                option.textContent = optionText;
                option.setAttribute('data-icon', iconClass);
                participantSelect.appendChild(option);
                
                // Store participant data for later use
                participantTypes.push({
                    type: participant.target,
                    data: participant.specificParticipants || []
                });
            }
        });
        
        // Add event listener for dropdown change
        participantSelect.addEventListener('change', function() {
            const selectedIndex = this.value;
            
            if (selectedIndex === '') {
                detailsContainer.style.display = 'none';
                return;
            }
            
            const selectedParticipant = participantTypes[selectedIndex];
            let detailsHTML = '';
            
            if (selectedParticipant.type === 'School') {
                detailsHTML += '<div class="participant-details-container">';
                detailsHTML += '<div class="participant-type-indicator"><i class="fas fa-school mr-2"></i> School Participants</div>';
                
                if (selectedParticipant.data.length > 0) {
                    selectedParticipant.data.forEach((school, idx) => {
                        detailsHTML += `<div class="participant-item">`;
                        if (typeof school === 'object') {
                            if (school.level) {
                                detailsHTML += `<span class="participant-tag"><i class="fas fa-layer-group"></i> ${school.level}</span>`;
                            }
                            if (school.type) {
                                detailsHTML += `<span class="participant-tag"><i class="fas fa-tag"></i> ${school.type}</span>`;
                            }
                            detailsHTML += `<div class="specialization"><strong>Specialization:</strong> ${school.specialization || 'N/A'}</div>`;
                        } else {
                            detailsHTML += `${school}`;
                        }
                        detailsHTML += '</div>';
                    });
                } else {
                    detailsHTML += '<div class="participant-item" style="text-align: center;"><i class="fas fa-info-circle mr-2"></i> All School Personnel are eligible</div>';
                }
                detailsHTML += '</div>';
            } else if (selectedParticipant.type === 'Division') {
                detailsHTML += '<div class="participant-details-container">';
                detailsHTML += '<div class="participant-type-indicator"><i class="fas fa-building mr-2"></i> Division Office Participants</div>';
                
                if (selectedParticipant.data.length > 0) {
                    detailsHTML += '<ul class="participant-list">';
                    selectedParticipant.data.forEach((division, idx) => {
                        detailsHTML += `<li class="participant-list-item">`;
                        if (typeof division === 'object') {
                            // Get the department name (usually the only/first property)
                            const deptName = Object.values(division)[0] || 'N/A';
                            detailsHTML += `${deptName}`;
                        } else {
                            detailsHTML += `${division}`;
                        }
                        detailsHTML += '</li>';
                    });
                    detailsHTML += '</ul>';
                } else {
                    detailsHTML += '<div class="participant-item" style="text-align: center;"><i class="fas fa-info-circle mr-2"></i> All Division Units are eligible</div>';
                }
                detailsHTML += '</div>';
            } else if (selectedParticipant.type === 'all') {
                detailsHTML += '<div class="participant-details-container">';
                detailsHTML += '<div class="participant-type-indicator"><i class="fas fa-users mr-2"></i> All Personnel</div>';
                detailsHTML += '<div class="participant-item" style="text-align: center;">';
                detailsHTML += '<i class="fas fa-check-circle mr-2" style="color: #28a745;"></i>';
                detailsHTML += 'This event is open to all personnel from both Schools and Division units.';
                detailsHTML += '</div>';
                detailsHTML += '</div>';
            }
            
            detailsContainer.innerHTML = detailsHTML;
            detailsContainer.style.display = 'block';
        });
    }
} catch (error) {
    console.error("Error parsing eligible participants data:", error);
    participantSelect.innerHTML = '<option value="">Error loading participant data</option>';
    participantSelect.classList.add('error');
}

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
        if (archiveBtn) {
            archiveBtn.style.display = 'block';
            archiveBtn.setAttribute('data-id', eventData.id);
        }
        
        const unarchiveBtn = document.getElementById('unarchive-btn');
        if (unarchiveBtn) {
            unarchiveBtn.style.display = 'block';
            unarchiveBtn.setAttribute('data-id', eventData.id);
        }
        
        // Show download button and set event ID
        const downloadBtn = document.getElementById('download-btn');
        downloadBtn.style.display = 'block';
        downloadBtn.setAttribute('data-id', eventData.id);
    }
}

function distributeCertificates() {
    const eventId = document.getElementById('distribute-btn').getAttribute('data-id');
    if (confirm('Are you sure you want to distribute certificates to all participants of this event?')) {
        window.location.href = 'distribute_certificates.php?event_id=' + eventId;
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
                    <td>${user.name}</td>
                    <td>${user.email}</td>
                    <td>${user.phone || 'N/A'}</td>
                    <td>${user.designation || 'N/A'}</td>
                    <td>${formattedDate}</td>
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

function toggleRegisteredUsersTable() {
    const tableContainer = document.getElementById('registered-users-table-container');
    const toggleButton = document.getElementById('toggle-users-table-btn');
    
    if (tableContainer.style.display === 'none' || tableContainer.style.display === '') {
        tableContainer.style.display = 'block';
        toggleButton.innerHTML = '<i class="fas fa-eye-slash"></i> Hide Registered Users';
        // Fetch users data if not already loaded
        const eventId = currentEvent;
        if (eventId) {
            fetchRegisteredUsers(eventId);
        }
    } else {
        tableContainer.style.display = 'none';
        toggleButton.innerHTML = '<i class="fas fa-eye"></i> View Registered Users';
    }
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

document.addEventListener('DOMContentLoaded', function() {
    // Check if an event_id is in the URL
    const urlParams = new URLSearchParams(window.location.search);
    const eventId = urlParams.get('event_id');
    
    if (eventId) {
        // Find the event with the matching ID
        const events = <?php echo json_encode($eventsData); ?>;
        const event = events.find(e => e.id == eventId);
        
        if (event) {
            // Show the details for this event
            showDetails(event);
            
            // Find all event buttons
            const eventElements = document.querySelectorAll('.events-btn');
            
            // Loop through each event button to find the one with the matching ID
            eventElements.forEach(function(element) {
                // Get the onclick attribute content
                const onclickAttr = element.getAttribute('onclick');
                
                // If this element has the matching event ID in its onclick attribute
                if (onclickAttr && onclickAttr.indexOf(`"id":${eventId}`) !== -1) {
                    // Scroll to this element
                    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Optional: Add a highlight class
                    element.classList.add('highlighted-event');
                }
            });
        }
    }
});

</script>

</body>
</html>
<?php
$conn->close(); 
?>