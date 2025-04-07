<?php
require_once 'config.php';

// Get the current date and time
$currentDateTime = date('Y-m-d H:i:s');
error_log("Current Date and Time: " . $currentDateTime);

// Check if we're viewing archived events
$viewArchived = isset($_GET['view']) && $_GET['view'] === 'archived';

// Base SQL query to fetch event details
// In your SQL query, make sure the event_days_data portion is included:
$baseSQL = "SELECT 
            e.id, e.title, e.specification, e.delivery, 
            e.start_date, e.end_date, e.venue, e.proponent, e.archived, e.estimated_participants,
            (SELECT COUNT(DISTINCT ru.user_id) FROM registered_users ru WHERE ru.event_id = e.id) AS user_count,
            GROUP_CONCAT(DISTINCT CONCAT(fs.source, ' -  ₱', FORMAT(fs.amount, 2), '') SEPARATOR ', ') AS funding_sources,
            GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ') AS speakers,  
            GROUP_CONCAT(DISTINCT 
                CASE 
                    WHEN ep.target = 'school' THEN 'School'
                    WHEN ep.target = 'division' THEN 'Division'
                    WHEN ep.target = 'Both' THEN 'All Personnel'
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
                WHEN CURDATE() >= e.start_date AND CURDATE() <= e.end_date THEN 'Ongoing'
                WHEN CURDATE() > e.end_date THEN 'Past'
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
    $sql = $baseSQL . " WHERE e.archived = 0 GROUP BY e.id ORDER BY e.start_date ASC";
    $pageTitle = "Events";
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
        // Updated regex pattern to match the actual format (4 parts)
        if (preg_match('/^(\d+):(\d{4}-\d{2}-\d{2}):(\d{2}:\d{2}):(\d{2}:\d{2})$/', $day, $matches)) {
            $dayNumber = $matches[1];
            $dayDate = $matches[2];
            $startTime = $matches[3];
            $endTime = $matches[4];
            
            // Format the date and times
            $formattedDate = date('F j, Y', strtotime($dayDate));
            
            // Format the times
            $startTimeFormatted = date('g:i A', strtotime("2000-01-01 $startTime"));
            $endTimeFormatted = date('g:i A', strtotime("2000-01-01 $endTime"));
            
            $formattedDays[] = "Day $dayNumber ($formattedDate): $startTimeFormatted - $endTimeFormatted";
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
        c.name AS classification
    FROM registered_users ru
    JOIN users u ON ru.user_id = u.id
    LEFT JOIN users_lnd ul ON ru.user_id = ul.user_id
    LEFT JOIN class_position cp ON ul.position_id = cp.id
    LEFT JOIN classification c ON cp.classification_id = c.id
    WHERE ru.event_id = ?
    GROUP BY u.id, u.email
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
    .unregister-btn {
        background-color: #ff3b30;
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        font-family: 'Montserrat', sans-serif;
        box-shadow: 0 2px 4px rgba(255, 59, 48, 0.2);
        transition: opacity 0.3s ease, transform 0.3s ease;
        display: flex; /* Always keep as flex */
        align-items: center;
        margin-bottom: 15px;
        
        /* Hidden state */
        opacity: 0;
        transform: translateY(-10px);
        pointer-events: none; /* Prevents interaction when invisible */
    }

    .unregister-btn.visible {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto; /* Allows interaction when visible */
    }

    .unregister-btn i {
      margin-right: 8px;
    }

    input[type="checkbox"] {
        appearance: none;
        -webkit-appearance: none;
        width: 18px;
        height: 18px;
        border: 2px solid #cbd5e0;
        border-radius: 4px;
        outline: none;
        cursor: pointer;
        position: relative;
        vertical-align: middle;
        transition: all 0.2s ease;
    }
    input[type="checkbox"]:checked {
        background-color: #2b3a8f;
        border-color: #2b3a8f;
    }
    input[type="checkbox"]:checked::after {
        content: '✓';
        position: absolute;
        color: white;
        font-size: 12px;
        font-weight: bold;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="logo">
        <button id="toggleSidebar" class="toggle-btn">
            <i class="fas fa-bars"></i>
        </button>
    </div>
        
    <div class="menu">
        <a href="admin-dashboard.php">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="admin-events.php"  class="active">
            <i class="fas fa-calendar-alt"></i>
            <span>Events</span>
        </a>
        <a href="admin-users.php">
            <i class="fas fa-users"></i>
            <span>Users</span>
        </a>
    </div>
</div>

    <div class="content" id="mainContent">
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

            <div class="search-container">
                <span class="search-icon"><i class="fa fa-search" aria-hidden="true"></i></span>
                <input type="text" class="search-input" placeholder="Search for events...">
            </div>

            <div class="archive-toggle">
                <a href="admin-events.php" class="<?php echo !$viewArchived ? 'active' : ''; ?>">Events</a>
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
                        <div class="button">
                        <div class="expanded-content">
                            <button onclick="unarchiveEvent()" id="unarchive-btn" style="display:none;">Unarchive Event</button>
                        </div>
                        <?php else: ?>
                        <div class="expanded-content">
                            <button onclick="archiveEvent()" id="archive-btn" style="display:none;">Archive Event</button>
                        </div>
                        <div class="expanded-content">
                            <button onclick="updateEvent()" id="update-btn">Update Event</button>
                        </div>
                        </div>
                        <?php endif; ?>

            <h3 id="detail-title"></h3>
                <div class="detail-items">
                    <div class="detail-items-1">
                        <div class="detail-item expanded-content">
                            <h4>Event Specification:</h4>
                            <p id="detail-event_specification"></p>
                        </div>
                        <div class="detail-item">
                            <h4>Delivery:</h4>
                            <p id="detail-delivery"></p>
                        </div>
                        <div class="detail-item expanded-content">
                            <h4>Start:</h4>
                            <p id="detail-start"></p>
                        </div>
                        <div class="detail-item expanded-content">
                            <h4>End:</h4>
                            <p id="detail-end"></p>
                        </div>
                        <div class="detail-item">
                            <h4>Event Schedule:</h4>
                            <p id="detail-event-days"></p>
                        </div>
                        <div class="detail-item expanded-content">
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
                            <h4>No. of Estimated Participants</h4>
                            <p id="detail-est-participants">
                        </div>
                        <div class="detail-item">
                            <h4>Proponents</h4>
                            <p id="detail-proponent">
                        </div>
                        <div class="detail-item">
                            <h4>Speakers:</h4>
                            <p id="detail-speakers"></p>
                        </div>
                        <div class="detail-item">
                            <h4>Eligible Participants:</h4>
                            <div id="participant-details-container" class="participant-details-wrapper"></div>
                        </div>
                        <div class="detail-item">
                            <h4>Meal Plan:</h4>
                            <p id="detail-meal_plan"></p>
                        </div>
                    </div>
                </div>

                <div class="detail-item expanded-content" style="width: 100%;">
                    <div class="registered-users">
                        <h4 style="margin: 0;">Registered Users: <span id="detail-user_count"></span></h4>
                    </div>
                    <button id="toggle-users-table-btn" onclick="toggleRegisteredUsersTable()" class="view-user-btn">
                        <i class="fas fa-eye"></i> View List of Registered Users
                    </button>
                    
                    <div id="registered-users-table-container">
                        <div class="detail-item download">
                            <button class="download-btn" onclick="downloadParticipantsList()" id="download-btn">
                                <i class="fas fa-download"></i> List of Registered Participants
                            </button>
                            <button class="download-btn" onclick="downloadMealAttendance()" id="meal-btn">
                                <i class="fas fa-download"></i> Meal Plan Attendance
                            </button>
                            <button class="download-btn" id="link-btn">
                                <i class="fa fa-link" aria-hidden="true"></i> Distribute Evaluation Link
                            </button>
                            <button class="download-btn" onclick="distributeCertificates()" id="distribute-btn">
                                <i class="fas fa-certificate"></i> Distribute Certificates
                            </button>
                        </div>
                        <div class="registered-users-table">
                            <div class="table-controls mb-3">
                                <button id="unregister-selected" class="unregister-btn"><i class="fa fa-trash" aria-hidden="true"></i>Unregister Selected Users</button>
                            </div>
                        <table id="registered-users-table">
                            <thead>
                                <tr>
                                    <th class="checkbox-column"><input type="checkbox" class="user-checkbox" id="select-all"></th>
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
</div>

<!-- Evaluation Link Modal -->
<div id="evaluation-modal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2>Distribute Evaluation Link</h2>
    <p>Send evaluation link to all registered participants for this event.</p>
    <form id="evaluation-form">
        <div class="form-group">
            <label for="eval-link">Evaluation Link:</label>
            <input type="text" id="eval-link" name="eval-link" placeholder="Enter evaluation form URL" required>
        </div>
         
        <div class="form-group">
            <label>Participants who will receive the link:</label>
            <div class="participant-count">
            <span id="total-participants" style="display: none;">
            </div>
            <div class="participants-table-container">
            <table id="participants-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                </tr>
                </thead>
                <tbody id="participants-table-body">
                <tr>
                    <td colspan="3" class="text-center">Loading participants...</td>
                </tr>
                </tbody>
            </table>
            </div>
        </div>
      
        <div class="form-actions">
            <button type="button" id="cancel-eval">Cancel</button>
            <button type="submit" id="send-eval">Send Evaluation Link</button>
        </div>
        </form>
    </div>
    </div>
</div>

<script>
    let currentEvent = null;

    // Function to sort events purely by date in descending order (newest first)
function sortEventsByDate() {
    const eventsContainer = document.querySelector('.events-section');
    if (!eventsContainer) {
        console.error('Events section not found!');
        return;
    }
    
    // Get all event buttons
    const eventButtons = Array.from(eventsContainer.querySelectorAll('.events-btn'));
    
    // Sort the events: ongoing first, then sort by date in descending order
    eventButtons.sort((a, b) => {
        try {
            // Extract event data from onclick attribute
            const aOnClick = a.getAttribute('onclick') || '';
            const bOnClick = b.getAttribute('onclick') || '';
            
            const aMatch = aOnClick.match(/showDetails\((.*)\)/);
            const bMatch = bOnClick.match(/showDetails\((.*)\)/);
            
            if (!aMatch || !bMatch) {
                console.error('Failed to extract event data from onclick attributes');
                return 0;
            }
            
            // Parse the event data
            const aData = JSON.parse(aMatch[1]);
            const bData = JSON.parse(bMatch[1]);
            
            // Check if either is "ongoing"
            const aIsOngoing = aData.status.toLowerCase() === "ongoing";
            const bIsOngoing = bData.status.toLowerCase() === "ongoing";
            
            // If one is ongoing and the other isn't, prioritize the ongoing one
            if (aIsOngoing && !bIsOngoing) return -1;
            if (!aIsOngoing && bIsOngoing) return 1;
            
            // If both have the same status (both ongoing or both not), sort by date
            const aDate = new Date(aData.start_date);
            const bDate = new Date(bData.start_date);
            
            // Date-based descending sort (newer dates first)
            return bDate - aDate;
        } catch (error) {
            console.error('Error sorting events:', error);
            return 0;
        }
    });
    
    // Clear existing events and reappend in sorted order
    const fragment = document.createDocumentFragment();
    eventButtons.forEach(button => {
        fragment.appendChild(button.cloneNode(true));
    });
    
    // Remove all existing events
    eventButtons.forEach(button => button.remove());
    
    // Add sorted events
    eventsContainer.appendChild(fragment);
    
    console.log('Events sorted: ongoing first, then by date in descending order');
}

// Make sure to call this function on page load
document.addEventListener('DOMContentLoaded', function() {
    sortEventsByDate();
    
    // Re-sort when search is performed to maintain the order
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            // Let the filter function finish first
            setTimeout(sortEventsByDate, 10);
        });
    }
});

    // Function to toggle sidebar
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('mainContent');
        const toggleBtn = document.getElementById('toggleSidebar');

        // Check if sidebar state is saved in localStorage
        const isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        
        // Set initial state based on localStorage
        if (isSidebarCollapsed) {
            sidebar.classList.add('collapsed');
            content.classList.add('expanded');
        }

        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('expanded');
            
            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    });

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
        const sortButton = document.getElementById('sortButton');
        if (sortButton) {
            sortButton.textContent = 'Sort Events: ' + (currentSortOrder === 'ASC' ? 'Asc' : 'Des');
        }
    });

    function updateButtonState() {
    const linkBtn = document.getElementById('link-btn');
    const distributeBtn = document.getElementById('distribute-btn');
    
    function setupTooltip(button, message) {
        button.classList.add('tooltip-disabled');
        button.setAttribute('data-tooltip', message);
        button.disabled = true;
    }

    function removeTooltip(button) {
        button.classList.remove('tooltip-disabled');
        button.removeAttribute('data-tooltip');
        button.disabled = false;
    }

    // Check if an event is selected and its status
    if (!currentEvent) {
        // No event selected
        setupTooltip(linkBtn, 'Enabled after the event is completed');
        setupTooltip(distributeBtn, 'Enabled after the event is completed');
    } else {
        // An event is selected
        const eventData = <?php echo json_encode($eventsData); ?>.find(event => event.id == currentEvent);
        
        if (eventData && eventData.status !== 'Past') {
            // Event is not a past event
            setupTooltip(linkBtn, 'Enabled after the event is completed');
            setupTooltip(distributeBtn, 'Enabled after the event is completed');
        } else {
            // Past event
            removeTooltip(linkBtn);
            removeTooltip(distributeBtn);
        }
    }
}

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
        document.getElementById('detail-est-participants').textContent = eventData.estimated_participants || "Not specified";
        document.getElementById('detail-proponent').textContent = eventData.proponent || "Not specified";
        document.getElementById('detail-speakers').textContent = eventData.speakers || "Not specified";
        
        // Fetch registered users for this event
        fetchRegisteredUsers(eventData.id);

        updateButtonState();
        
        // In the showDetails function, replace the participant select code with this:

    // Process the eligible participants data to show all types at once
    const detailsContainer = document.getElementById('participant-details-container');
    detailsContainer.innerHTML = ''; // Clear previous content
    
    // Disable or enable distribution buttons based on event status
    const linkBtn = document.getElementById('link-btn');
    const distributeBtn = document.getElementById('distribute-btn');

    // Modify the existing code that handles button visibility
    if (eventData.status !== 'Past') {
        // Disable buttons for events not yet completed
        linkBtn.disabled = true;
        linkBtn.classList.add('tooltip-disabled');
        linkBtn.setAttribute('data-tooltip', 'Enabled after the event is completed');

        distributeBtn.disabled = true;
        distributeBtn.classList.add('tooltip-disabled');
        distributeBtn.setAttribute('data-tooltip', 'Enabled after the event is completed');
    } else {
        // Ensure buttons are enabled for completed events
        linkBtn.disabled = false;
        linkBtn.classList.remove('tooltip-disabled');
        linkBtn.removeAttribute('data-tooltip');

        distributeBtn.disabled = false;
        distributeBtn.classList.remove('tooltip-disabled');
        distributeBtn.removeAttribute('data-tooltip');
    }

    document.getElementById('detail-delivery').textContent = eventData.delivery;
    
        // Add this code to check if the event is online and disable meal plan button accordingly
        const mealBtn = document.getElementById('meal-btn');
    if (eventData.delivery && eventData.delivery.toLowerCase() === 'online') {
        mealBtn.disabled = true;
        
        // Remove any existing classes that might conflict with tooltip display
        mealBtn.classList.remove('disabled-btn'); // Remove if you're using a different class
        mealBtn.classList.add('tooltip-disabled'); // Use the consistent tooltip class
        
        // Set a clear tooltip message
        mealBtn.setAttribute('data-tooltip', 'Meal plan not available for online events');
    } else {
        mealBtn.disabled = false;
        
        // Remove tooltip classes and attributes when not needed
        mealBtn.classList.remove('tooltip-disabled');
        mealBtn.removeAttribute('data-tooltip');
    }

    try {
        if (eventData.processed_eligible_data) {
            const eligibleData = JSON.parse(eventData.processed_eligible_data);
            let detailsHTML = '';
            
            if (eligibleData.length === 0) {
                detailsHTML = '<div class="participant-item" style="text-align: center;"><i class="fas fa-info-circle mr-2"></i> No participant information available</div>';
            } else {
                // Display all participant types at once
                eligibleData.forEach((participant) => {
                    if (participant.target === 'School') {
                        detailsHTML += '<div class="participant-details-container">';
                        detailsHTML += '<div class="participant-type-indicator"><i class="fas fa-school mr-2"></i> School Personnel</div>';
                        
                        if (participant.specificParticipants && participant.specificParticipants.length > 0) {
                            participant.specificParticipants.forEach((school) => {
                                detailsHTML += `<div class="participant-item">`;
                                if (typeof school === 'object') {
                                    if (school.level) {
                                        detailsHTML += `<span class="participant-tag"><i class="fas fa-layer-group"></i> ${school.level}</span>`;
                                    }
                                    if (school.type) {
                                        detailsHTML += `<span class="participant-tag"><i class="fas fa-tag"></i> ${school.type}</span>`;
                                    }
                                    
                                    // New code: Display specialization in bullet form
                                    if (school.specialization) {
                                        detailsHTML += `<div class="specialization"><strong>Specialization:</strong>`;
                                        detailsHTML += `<ul class="specialization-list">`;
                                        
                                        // Handle different input types for specialization
                                        let specs = [];
                                        if (Array.isArray(school.specialization)) {
                                            specs = school.specialization;
                                        } else if (typeof school.specialization === 'string') {
                                            // Split by comma and trim each specialization
                                            specs = school.specialization.split(',').map(spec => spec.trim());
                                        }
                                        
                                        // Create a bullet for each specialization
                                        specs.forEach((spec) => {
                                            detailsHTML += `<li>${spec}</li>`;
                                        });
                                        
                                        detailsHTML += `</ul></div>`;
                                    } else {
                                        detailsHTML += `<div class="specialization"><strong>Specialization:</strong> N/A</div>`;
                                    }
                                } else {
                                    detailsHTML += `${school}`;
                                }
                                detailsHTML += '</div>';
                            });
                        } else {
                            detailsHTML += '<div class="participant-item" style="text-align: center;"><i class="fas fa-info-circle mr-2"></i> All School Personnel are eligible</div>';
                        }
                        detailsHTML += '</div>';
                    } else if (participant.target === 'Division') {
                        // Division section remains unchanged
                        detailsHTML += '<div class="participant-details-container">';
                        detailsHTML += '<div class="participant-type-indicator"><i class="fas fa-building mr-2"></i> Division Office Personnel</div>';
                        
                        if (participant.specificParticipants && participant.specificParticipants.length > 0) {
                            detailsHTML += '<ul class="participant-list">';
                            participant.specificParticipants.forEach((division) => {
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
                    } else if (participant.target === 'Both') {
                        detailsHTML += '<div class="participant-details-container">';
                        detailsHTML += '<div class="participant-type-indicator"><i class="fas fa-users mr-2"></i> All Personnel</div>';
                        
                        // First, check if we have specific data
                        if (participant.specificParticipants && typeof participant.specificParticipants === 'object') {
                            // Display school participants
                            if (participant.specificParticipants.school && participant.specificParticipants.school.length > 0) {
                                detailsHTML += '<div class="participant-group">';
                                detailsHTML += '<div class="participant-type-subheader"><i class="fas fa-school mr-2"></i> School Personnel</div>';
                                
                                participant.specificParticipants.school.forEach((school) => {
                                    detailsHTML += `<div class="participant-item">`;
                                    if (typeof school === 'object') {
                                        if (school.level) {
                                            detailsHTML += `<span class="participant-tag"><i class="fas fa-layer-group"></i> ${school.level}</span>`;
                                        }
                                        if (school.type) {
                                            detailsHTML += `<span class="participant-tag"><i class="fas fa-tag"></i> ${school.type}</span>`;
                                        }
                                        
                                        // New code: Display specialization in bullet form
                                        if (school.specialization) {
                                            detailsHTML += `<div class="specialization"><strong>Specialization:</strong>`;
                                            detailsHTML += `<ul class="specialization-list">`;
                                            
                                            // If specialization is an array, create a bullet for each
                                            if (Array.isArray(school.specialization)) {
                                                school.specialization.forEach((spec) => {
                                                    detailsHTML += `<li>${spec}</li>`;
                                                });
                                            } else {
                                                // If it's a single string, create one bullet
                                                detailsHTML += `<li>${school.specialization}</li>`;
                                            }
                                            
                                            detailsHTML += `</ul></div>`;
                                        } else {
                                            detailsHTML += `<div class="specialization"><strong>Specialization:</strong> N/A</div>`;
                                        }
                                    } else {
                                        detailsHTML += `${school}`;
                                    }
                                    detailsHTML += '</div>';
                                });
                                detailsHTML += '</div>';
                            } else {
                                detailsHTML += '<div class="participant-group">';
                                detailsHTML += '<div class="participant-type-subheader"><i class="fas fa-school mr-2"></i> School Personnel</div>';
                                detailsHTML += '<div class="participant-item" style="text-align: center;"><i class="fas fa-info-circle mr-2"></i> All School Personnel are eligible</div>';
                                detailsHTML += '</div>';
                            }
                            
                            // Division section remains unchanged
                            if (participant.specificParticipants.division && participant.specificParticipants.division.length > 0) {
                                detailsHTML += '<div class="participant-group">';
                                detailsHTML += '<div class="participant-type-subheader"><i class="fas fa-building mr-2"></i> Division Office Personnel</div>';
                                detailsHTML += '<ul class="participant-list">';
                                
                                participant.specificParticipants.division.forEach((division) => {
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
                                detailsHTML += '</div>';
                            } else {
                                detailsHTML += '<div class="participant-group">';
                                detailsHTML += '<div class="participant-type-subheader"><i class="fas fa-building mr-2"></i> Division Office Personnel</div>';
                                detailsHTML += '<div class="participant-item" style="text-align: center;"><i class="fas fa-info-circle mr-2"></i> All Division Units are eligible</div>';
                                detailsHTML += '</div>';
                            }
                        } else {
                            // Fallback for when specific participant data isn't available
                            detailsHTML += '<div class="participant-item" style="text-align: center;">';
                            detailsHTML += '<i class="fas fa-check-circle mr-2" style="color: #28a745;"></i>';
                            detailsHTML += 'This event is open to all personnel from both Schools and Division units.';
                            detailsHTML += '</div>';
                        }
                        
                        detailsHTML += '</div>';
                    }
                });
            }
            
            detailsContainer.innerHTML = detailsHTML;
            detailsContainer.style.display = 'block';
        } else {
            detailsContainer.innerHTML = '<div class="participant-item" style="text-align: center;"><i class="fas fa-info-circle mr-2"></i> No participant information available</div>';
        }
    } catch (error) {
        console.error("Error parsing eligible participants data:", error);
        detailsContainer.innerHTML = '<div class="participant-item error" style="text-align: center;"><i class="fas fa-exclamation-circle mr-2"></i> Error loading participant data</div>';
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
        

        // Show/hide update button based on event status
        const updateBtn = document.getElementById('update-btn');
        if (updateBtn) {

            const status = eventData.status.toLowerCase();

            // Only show update button if the event is not "ongoing"
            if (status !== 'ongoing' && status !== 'past') {
                updateBtn.style.display = 'block';
                updateBtn.setAttribute('data-id', eventData.id);
            } else {
                updateBtn.style.display = 'none';
            }
        }

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

        // Get the modal and button elements
        const evalLinkBtn = document.getElementById("link-btn");
        const evalModal = document.getElementById("evaluation-modal");
        const closeBtn = evalModal.querySelector(".close");
        const cancelBtn = document.getElementById("cancel-eval");
        const evalForm = document.getElementById("evaluation-form");

        // When the user clicks the link button, open the modal
        evalLinkBtn.onclick = function() {
        evalModal.style.display = "block";
        const eventId = currentEvent;

        // Set event ID as a data attribute on the form
        evalForm.setAttribute('data-event-id', eventId);

        // Load participants for this event
        loadParticipantsForModal(eventId);
        }

        // Function to load participants for the modal
        function loadParticipantsForModal(eventId) {
        const tableBody = document.getElementById('participants-table-body');
        const totalParticipantsElement = document.getElementById('total-participants');

        // Show loading indicator
        tableBody.innerHTML = '<tr><td colspan="3" class="text-center">Loading participants...</td></tr>';

        // Fetch registered users using AJAX
        fetch('get_registered_users.php?event_id=' + eventId)
            .then(response => response.json())
            .then(data => {
            // Clear loading indicator
            tableBody.innerHTML = '';
            
            // Update total participants count
            totalParticipantsElement.textContent = data.length;
            
            if (data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="3" class="text-center">No registered users found</td></tr>';
                return;
            }
            
            // Populate table with user data
            data.forEach((user, index) => {
                const row = document.createElement('tr');
                
                row.innerHTML = `
                <td>${index + 1}</td>
                <td>${user.name}</td>
                <td>${user.email}</td>
                `;
                
                tableBody.appendChild(row);
            });
            })
            .catch(error => {
            console.error('Error fetching registered users:', error);
            tableBody.innerHTML = '<tr><td colspan="3" class="text-center">Error loading registered users</td></tr>';
            totalParticipantsElement.textContent = '0';
            });
        }

        // When the user clicks the close button or cancel button, close the modal
        closeBtn.onclick = function() {
        evalModal.style.display = "none";
        }

        cancelBtn.onclick = function() {
        evalModal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
        if (event.target == evalModal) {
            evalModal.style.display = "none";
        }
        }

        // Function to validate URL
        function isValidUrl(url) {
            try {
                new URL(url);
                return true;
            } catch {
                return false;
            }
        }


        // Handle form submission
        evalForm.onsubmit = function(e) {
        e.preventDefault();
        const eventId = this.getAttribute('data-event-id');
        const evalLink = document.getElementById('eval-link').value;

         // Validate evaluation link
        if (!isValidUrl(evalLink)) {
            alert('Please enter a valid URL for the evaluation link.');
            return;
        }

        // Confirm before sending
        if (!confirm('Are you sure you want to send the evaluation link to all participants?')) {
            return;
        }  

        // Show loading state
        document.getElementById('send-eval').textContent = "Sending...";
        document.getElementById('send-eval').disabled = true;

        fetch(`send_eval_link.php?event_id=${eventId}&eval_link=${encodeURIComponent(evalLink)}`, {
                method: 'GET'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text(); // or response.json() depending on your PHP script
            })
            .then(data => {
                // Parse the response or show a generic success message
                alert('Evaluation link sent successfully to all participants!');
                evalModal.style.display = "none";
               
                // Reset form
                evalForm.reset();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to send evaluation link. Please try again.');
            })
            .finally(() => {
                // Restore button state
                sendButton.textContent = "Send Evaluation Link";
                sendButton.disabled = false;
            });
        }

    const searchInput = document.querySelector('.search-input');
    const eventButtons = document.querySelectorAll('.events-btn');
    const eventsSection = document.querySelector('.events-section');

    // Function to filter events
    function filterEvents(searchTerm) {
        eventButtons.forEach(function(eventButton) {
            const eventTitle = eventButton.querySelector('h3').textContent.toLowerCase();
            const eventSpecification = eventButton.querySelector('p').textContent.toLowerCase();
            const eventDate = eventButton.querySelector('.event-dates p').textContent.toLowerCase();
            
            // Check if the search term matches the title, specification, or date
            if (
                searchTerm === '' || 
                eventTitle.includes(searchTerm) || 
                eventSpecification.includes(searchTerm) || 
                eventDate.includes(searchTerm)
            ) {
                eventButton.style.display = 'block';
            } else {
                eventButton.style.display = 'none';
            }
        });

        // If no events match the search, show a "No results" message
        const visibleEvents = Array.from(eventButtons).filter(btn => btn.style.display !== 'none');
        
        // Remove any existing "no results" message
        const existingNoResultsMessage = document.querySelector('.no-results-message');
        if (existingNoResultsMessage) {
            existingNoResultsMessage.remove();
        }

        if (visibleEvents.length === 0 && searchTerm !== '') {
            const noResultsMessage = document.createElement('div');
            noResultsMessage.classList.add('no-results-message');
            noResultsMessage.innerHTML = `
                <p><i class="fas fa-search"></i> No events found matching "<strong>${searchTerm}</strong>"</p>
            `;
            
            // Insert the message inside the events section
            eventsSection.appendChild(noResultsMessage);
        }
    }

    // Event listener for input changes
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        filterEvents(searchTerm);
    });

    // Handle clear button (x) in search input
    searchInput.addEventListener('search', function() {
        // This event is triggered when the search input is cleared
        const searchTerm = this.value.toLowerCase().trim();
        filterEvents(searchTerm);
    });

        // When clicking the X (clear) button
        const searchContainer = document.querySelector('.search-container');
    if (searchContainer) {
        searchContainer.addEventListener('click', function(e) {
            // Check if the click was on the after pseudo-element (approximated by position)
            const rect = searchContainer.getBoundingClientRect();
            
            // If click is in the right 30px of the container (where the X appears)
            if (searchInput && e.clientX > rect.right - 30 && searchInput.value !== '') {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
                searchInput.focus();
            }
        });
    }

    function distributeCertificates() {
        const eventId = document.getElementById('distribute-btn').getAttribute('data-id');
        if (confirm('Are you sure you want to distribute certificates to all participants of this event?')) {
            window.location.href = 'distribute_certificates.php?event_id=' + eventId;
        }
    }

 function fetchRegisteredUsers(eventId, forceFresh = false) {
    // Show loading indicator
    document.getElementById('registered-users-table-body').innerHTML = '<tr><td colspan="6" style="text-align: center;">Loading...</td></tr>';
    
    // Add cache-busting parameter to prevent caching issues
    const cacheBuster = forceFresh ? `&_t=${new Date().getTime()}` : '';
    
    // Fetch registered users using AJAX
    fetch(`get_registered_users.php?event_id=${eventId}${cacheBuster}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data); // Debug log to see what's coming back
            const tableBody = document.getElementById('registered-users-table-body');
            
            // Clear loading indicator
            tableBody.innerHTML = '';
            
            // Check if data is valid array and has elements
            if (!Array.isArray(data) || data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No registered users found</td></tr>';
                return;
            }
            
            // Populate table with user data
            data.forEach(user => {
                const row = document.createElement('tr');
                
                // Format the registration date
                const regDate = new Date(user.registration_date);
                const formattedDate = regDate.toLocaleString();
                
                row.innerHTML = `
                    <td><input type="checkbox" class="user-checkbox" data-registration-id="${user.id}"></td>
                    <td>${user.name}</td>
                    <td>${user.email}</td>
                    <td>${user.phone || 'N/A'}</td>
                    <td>${user.designation || 'N/A'}</td>
                    <td>${formattedDate}</td>
                `;
                
                tableBody.appendChild(row);
            });
            // Reset select all checkbox
            document.getElementById('select-all').checked = false;
            
            // Hide unregister button after refresh
            document.getElementById('unregister-selected').style.display = 'none';
        })
        .catch(error => {
            console.error('Error fetching registered users:', error);
            document.getElementById('registered-users-table-body').innerHTML = 
                '<tr><td colspan="6" style="text-align: center;">Error loading registered users: ' + error.message + '</td></tr>';
        });
}

// Set up select all checkbox
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Show/hide unregister button based on checkbox selection
document.addEventListener('DOMContentLoaded', function() {
    // Initial setup for select-all checkbox
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            toggleUnregisterButton();
        });
    }
    
    // Add event delegation for individual checkboxes
    document.getElementById('registered-users-table-body').addEventListener('change', function(e) {
        if (e.target.classList.contains('user-checkbox')) {
            toggleUnregisterButton();
            
            // Update select-all checkbox based on individual selections
            const checkboxes = document.querySelectorAll('.user-checkbox');
            const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = checkboxes.length === checkedBoxes.length;
            }
        }
    });
    
    // Function to toggle the unregister button visibility
    function toggleUnregisterButton() {
        const hasChecked = document.querySelector('.user-checkbox:checked');
        const unregisterBtn = document.getElementById('unregister-selected');
        
        if (unregisterBtn) {
            // First, ensure the button doesn't have display:none from elsewhere
            unregisterBtn.style.display = 'flex';
            
            if (hasChecked) {
                unregisterBtn.classList.add('visible');
            } else {
                unregisterBtn.classList.remove('visible');
            }
        }
    }
});

// Update the unregister button click handler to check for empty user list
document.getElementById('unregister-selected').addEventListener('click', function() {
    // First check if there are any users in the table
    const tableBody = document.getElementById('registered-users-table-body');
    
    // Check if the table has the "no users" message
    const tableRows = tableBody.querySelectorAll('tr');
    let hasNoUsersMessage = false;
    
    if (tableRows.length === 1) {
        const firstRowCell = tableRows[0].querySelector('td[colspan="6"]');
        if (firstRowCell && firstRowCell.textContent.includes('No registered users found')) {
            hasNoUsersMessage = true;
        }
    }
    
    if (hasNoUsersMessage) {
        alert('Unregister is unavailable, no registered users');
        return;
    }
    
    const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
    
    if (selectedCheckboxes.length === 0) {
        alert('Please select at least one user to unregister');
        return;
    }
    
    if (!confirm(`Are you sure you want to unregister ${selectedCheckboxes.length} user(s)?`)) {
        return;
    }
    
    const registrationIds = Array.from(selectedCheckboxes).map(checkbox => 
        checkbox.getAttribute('data-registration-id')
    );
    
    unregisterUsers(registrationIds);
});

// Function to unregister selected users
// Function to unregister selected users
function unregisterUsers(registrationIds) {
    // Show loading indicator
    document.getElementById('registered-users-table-body').innerHTML = '<tr><td colspan="6" style="text-align: center;">Processing unregistration...</td></tr>';
    
    fetch('unregister_users.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ registrationIds: registrationIds })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Server returned status: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(`Successfully unregistered ${registrationIds.length} user(s)`);
            
            // Show a temporary message
            document.getElementById('registered-users-table-body').innerHTML = 
                '<tr><td colspan="6" style="text-align: center;">Refreshing user list...</td></tr>';
                
            // Simply reload the page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            alert('Error: ' + (data.message || 'Unknown error occurred'));
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error unregistering users:', error);
        alert('An error occurred while trying to unregister users: ' + error.message);
        window.location.reload();
    });
}

// Recursive function to attempt multiple refreshes if needed
function refreshRegisteredUsers(eventId, attempt, maxAttempts = 3) {
    console.log(`Refresh attempt ${attempt} of ${maxAttempts}`);
    
    // Add cache-busting parameter with attempt number
    const cacheBuster = `&_nocache=${new Date().getTime()}_${attempt}`;
    
    fetch(`get_registered_users.php?event_id=${eventId}${cacheBuster}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Server returned status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log(`Attempt ${attempt} received data:`, data);
            
            // Check if we got valid data
            if (Array.isArray(data)) {
                // Update the table with this data
                updateTable(data);
                console.log(`Refresh successful on attempt ${attempt}`);
            } else {
                throw new Error('Invalid data format received');
            }
        })
        .catch(error => {
            console.error(`Error on refresh attempt ${attempt}:`, error);
            
            // Try again if we haven't reached max attempts
            if (attempt < maxAttempts) {
                // Exponential backoff - wait longer between each attempt
                const delay = Math.pow(2, attempt) * 500;
                console.log(`Will retry in ${delay}ms`);
                
                setTimeout(() => {
                    refreshRegisteredUsers(eventId, attempt + 1, maxAttempts);
                }, delay);
            } else {
                // If all attempts failed, show error and do a regular fetch
                console.error('All refresh attempts failed, falling back to normal fetch');
                fetchRegisteredUsers(eventId);
            }
        });
}

// Helper function to update the table with data
function updateTable(data) {
  const tableBody = document.getElementById('registered-users-table-body');
  const selectAllCheckbox = document.getElementById('select-all');
  const unregisterButton = document.getElementById('unregister-selected');
  const checkboxHeaderCell = document.querySelector('th.checkbox-column');
     
  // Clear any existing content
  tableBody.innerHTML = '';
  
  // Check if there are any users
  if (!Array.isArray(data) || data.length === 0) {
    tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No registered users found</td></tr>';
    
    // Direct approach - set inline style with !important
    if (checkboxHeaderCell) {
      checkboxHeaderCell.setAttribute('style', 'display: none !important');
    }
    
    // Hide unregister button
    if (unregisterButton) {
      unregisterButton.style.display = 'none';
    }
    return;
  }
  
  // If we have users, make everything visible again
  if (checkboxHeaderCell) {
    checkboxHeaderCell.removeAttribute('style');
  }
  
  // Rest of your code remains the same...
  data.forEach(user => {
    const row = document.createElement('tr');
    // Format the registration date
    const regDate = new Date(user.registration_date);
    const formattedDate = regDate.toLocaleString();
    row.innerHTML = `
      <td><input type="checkbox" class="user-checkbox" data-registration-id="${user.id}"></td>
      <td>${user.name}</td>
      <td>${user.email}</td>
      <td>${user.phone || 'N/A'}</td>
      <td>${user.designation || 'N/A'}</td>
      <td>${formattedDate}</td>
    `;
    tableBody.appendChild(row);
  });
  
  // Reset select all checkbox
  if (selectAllCheckbox) {
    selectAllCheckbox.checked = false;
  }
  
  // Hide unregister button
  if (unregisterButton) {
    unregisterButton.style.display = 'none';
  }
}
    function toggleRegisteredUsersTable() {
        const tableContainer = document.getElementById('registered-users-table-container');
        const toggleButton = document.getElementById('toggle-users-table-btn');
        
        if (tableContainer.style.display === 'none' || tableContainer.style.display === '') {
            tableContainer.style.display = 'block';
            toggleButton.innerHTML = '<i class="fas fa-eye-slash"></i> Hide List of Registered Users';
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

    function downloadMealAttendance() {
        const eventId = document.getElementById('download-btn').getAttribute('data-id');
        if (eventId) {
            window.location.href = 'download_meal_attendance.php?event_id=' + eventId;
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

        function updateEvent() {
            const eventId = document.getElementById('update-btn').getAttribute('data-id');
            if (eventId) {
                window.location.href = 'admin-update_event.php?id=' + eventId;
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

                    // Inside showDetails() function, where other buttons are being set up:
                // Inside showDetails() function, where other buttons are being set up:
                    const updateBtn = document.getElementById('update-btn');
                    if (updateBtn) {
                        updateBtn.style.display = 'block';
                        updateBtn.setAttribute('data-id', eventData.id);
                    }
                    
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