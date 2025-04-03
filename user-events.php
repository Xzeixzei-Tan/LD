<?php
require_once 'config.php';

// Start the session
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Display session messages if any exist
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-info">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']); // Clear the message after displaying
}

// Get the user ID from session
$user_id = $_SESSION['user_id'];

// Check if an event ID is specified in the URL
$selected_event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : null;

// Determine which tab is active (default to "Unregistered" if not specified)
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'unregistered';

// Fetch all events from the database
// FIXED: Modified the CASE statement to correctly handle events occurring on the current date
$sql = "SELECT e.id, e.title, e.start_date, e.end_date, e.venue, e.specification, e.delivery, e.proponent,
               (SELECT COUNT(*) FROM registered_users ru WHERE ru.event_id = e.id AND ru.user_id = ?) AS is_registered,
               CASE 
                   WHEN DATE(e.start_date) <= CURDATE() AND DATE(e.end_date) >= CURDATE() THEN 'Ongoing'
                   WHEN DATE(e.start_date) > CURDATE() THEN 'Upcoming'
                   ELSE 'Past'
               END AS status
        FROM events e  
        ORDER BY e.start_date DESC";
$stmt = $conn->prepare($sql);

// Check if the prepare statement was successful
if ($stmt === false) {
    echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    exit();
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    // Log the error and display a user-friendly message
    error_log("Query failed in user-events.php: " . $conn->error);
    die("There was a problem loading the events. Please try again later.");
}

$user_sql = "SELECT first_name, last_name FROM users WHERE id = ?";
                
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows > 0) {
    $row = $user_result->fetch_assoc();
    $first_name = $row['first_name'];
    $last_name = $row['last_name'];
    $_SESSION['first_name'] = $first_name; // Update the session
    $_SESSION['last_name'] = $last_name; 
} else {
    $first_name = "Unknown";
    $last_name = ""; // Set a default value
    $_SESSION['first_name'] = $first_name;
    $_SESSION['last_name'] = $last_name;
}

// Separate events into registered and unregistered
$registered_events = [];
$unregistered_events = [];

while ($row = $result->fetch_assoc()) {
    if ($row['is_registered'] > 0) {
        $registered_events[] = $row;
    } else {
        $unregistered_events[] = $row;
    }
}

// If an event is selected, fetch its details and speakers
$selected_event = null;
$speakers = [];
$is_registered = false;

if ($selected_event_id) {
    // Fetch event details
    // FIXED: Modified the CASE statement in the detail query as well
    $detail_sql = "SELECT e.*, 
                  (SELECT COUNT(*) FROM registered_users ru WHERE ru.event_id = e.id AND ru.user_id = ?) AS is_registered,
                  CASE 
                      WHEN DATE(e.start_date) <= CURDATE() AND DATE(e.end_date) >= CURDATE() THEN 'Ongoing'
                      WHEN DATE(e.start_date) > CURDATE() THEN 'Upcoming'
                      ELSE 'Past'
                  END AS status
                  FROM events e WHERE e.id = ?";
    $stmt = $conn->prepare($detail_sql);
    $stmt->bind_param("ii", $user_id, $selected_event_id);
    $stmt->execute();
    $detail_result = $stmt->get_result();
    
    if ($detail_result && $detail_result->num_rows > 0) {
        $selected_event = $detail_result->fetch_assoc();
        $is_registered = ($selected_event['is_registered'] > 0);
        
        // Set the active tab based on the selected event's registration status
        $active_tab = $is_registered ? 'registered' : 'unregistered';
    } else {
        // Event not found, set message
        $_SESSION['message'] = "The selected event was not found.";
        header("Location: user-events.php");
        exit();
    }

    $schedule_sql = "SELECT day_date, day_number, start_time, end_time 
                     FROM event_days 
                     WHERE event_id = ? 
                     ORDER BY day_number ASC";
    $stmt = $conn->prepare($schedule_sql);
    $stmt->bind_param("i", $selected_event_id);
    $stmt->execute();
    $schedule_result = $stmt->get_result();
    
    $event_schedule = [];
    if ($schedule_result) {
        while ($day = $schedule_result->fetch_assoc()) {
            $event_schedule[] = $day;
        }
    }
    $stmt->close();
    
    // Fetch speakers for the selected event
    $speakers_sql = "SELECT name FROM speakers WHERE event_id = ?";
    $stmt = $conn->prepare($speakers_sql);
    $stmt->bind_param("i", $selected_event_id);
    $stmt->execute();
    $speakers_result = $stmt->get_result();
    
    if ($speakers_result) {
        while ($speaker = $speakers_result->fetch_assoc()) {
            $speakers[] = $speaker['name'];
        }
    }
    $stmt->close();
}

// Function to format text with first letter of words capitalized and conjunctions lowercase
function formatTitle($text) {
    $words = explode(' ', strtolower($text));
    $conjunctions = array('a', 'an', 'the', 'and', 'but', 'or', 'for', 'nor', 'so', 'yet', 'at', 'by', 'in', 'of', 'on', 'to', 'with');
    
    foreach ($words as $i => $word) {
        // Always capitalize first word or any word that's not a conjunction
        $words[$i] = ($i === 0 || !in_array($word, $conjunctions)) ? ucfirst($word) : $word;
    }
    
    return implode(' ', $words);
}

// Function to capitalize the first letter of each word in a string
function capitalizeFirstLetters($text) {
    return ucwords(strtolower($text));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="styles/user-events.css" rel="stylesheet"> 
    <title>USER-events</title>
</head>
<body>
    <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
    <div class="logo">
        <button id="toggleSidebar" class="toggle-btn">
            <i class="fas fa-bars"></i>
        </button>
    </div>

       <div class="menu">
        <a href="user-dashboard.php" >
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="user-events.php" class="active">
            <i class="fas fa-calendar-alt"></i>
            <span>Events</span>
        </a>
        <a href="user-notif.php">
            <i class="fas fa-bell mr-3"></i>
            <span>Notification</span>
        </a>
        </div>
 <!-- Modified user profile with logout menu -->
        <div class="user-profile" id="userProfileToggle">
            <div class="user-avatar"><img src="styles/photos/me.jpg"></div>
            <div class="username"><?php echo htmlspecialchars($_SESSION['first_name']); ?> <?php echo isset($_SESSION['last_name']) ? htmlspecialchars($_SESSION['last_name']) : ''; ?></div>
            <!-- Add logout menu -->
            <div class="logout-menu" id="logoutMenu">
                <a href="login.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
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

            <div class="search-container">
                <span class="search-icon"><i class="fa fa-search" aria-hidden="true"></i></span>
                <input type="text" class="search-input" placeholder="Search for events...">
            </div>

            <div class="content-area">
                <div class="events-section <?php echo $selected_event ? 'shrink' : ''; ?>">
                    <div class="tabs">
                        <button class="tab <?php echo $active_tab == 'registered' ? 'active' : ''; ?>" onclick="switchTab('registered')">
                            Registered <span class="badge"><?php echo count($registered_events); ?></span>
                        </button>
                        <button class="tab <?php echo $active_tab == 'unregistered' ? 'active' : ''; ?>" onclick="switchTab('unregistered')">
                            Unregistered <span class="badge"><?php echo count($unregistered_events); ?></span>
                        </button>
                    </div>

                    <!-- Registered Events Tab -->
                    <div id="registered-tab" class="tab-content <?php echo $active_tab == 'registered' ? 'active' : ''; ?>">
                        <h2>Registered Events</h2>
                        <?php
                        if (count($registered_events) > 0) {
                            foreach ($registered_events as $row) {
                                $isSelected = ($selected_event_id == $row['id']) ? 'selected' : '';
                                echo '<div class="event ' . $isSelected . '">';
                                echo '<a class="events-btn" href="user-events.php?event_id=' . urlencode($row['id']) . '&tab=registered">';
                                echo '<div class="event-content">';
                                echo '<h3>' . htmlspecialchars($row["title"]) . '</h3>';
                                echo '<p>'. '<strong>Event Specification: '. '</strong>' . htmlspecialchars(capitalizeFirstLetters($row["specification"])) . '</p>';
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
                                echo '</div></a>';
                                echo '</div>';
                            }
                        } else {
                            echo "<p>You haven't registered for any events yet.</p>";
                        }
                        ?>
                    </div>

                    <!-- Unregistered Events Tab -->
                    <div id="unregistered-tab" class="tab-content <?php echo $active_tab == 'unregistered' ? 'active' : ''; ?>">
                        <h2>Available Events</h2>
                        <?php
                        if (count($unregistered_events) > 0) {
                            foreach ($unregistered_events as $row) {
                                $isSelected = ($selected_event_id == $row['id']) ? 'selected' : '';
                                echo '<div class="event ' . $isSelected . '">';
                                echo '<a class="events-btn" href="user-events.php?event_id=' . urlencode($row['id']) . '&tab=unregistered">';
                                echo '<div class="event-content">';
                                echo '<h3>' . htmlspecialchars($row["title"]) . '</h3>';
                                echo '<p>' . '<strong>Event Specification: '. '</strong>' . htmlspecialchars(capitalizeFirstLetters($row["specification"])) . '</p>';
                                echo '<div class="event-dates">'.'<p>' . '<strong><i class="fas fa-calendar-day"></i>Date: '. '</strong>' . date('M d, Y', strtotime($row["start_date"])) . '</p>'. '</div>';
                                echo '<span class="status-badge status-' . strtolower($row["status"]) . '">';
                                if(strtolower($row["status"]) == "upcoming") {
                                    echo '<i class="fas fa-hourglass-start"></i> ';
                                } else if(strtolower($row["status"]) == "ongoing") {
                                    echo '<i class="fas fa-circle"></i> ';
                                } else {
                                    echo '<i class="fas fa-check-circle"></i> ';
                                }
                                echo htmlspecialchars($row["status"]) . '</span>';
                                echo '</div></a>';
                                echo '</div>';
                            }
                        } else {
                            echo "<p>No available events found.</p>";
                        }
                        ?>
                    </div>
                </div>  

                <div class="details-section" id="details-section" <?php echo $selected_event ? 'style="display: block;"' : ''; ?>>
                    <i class="fas fa-expand expand-btn" onclick="toggleExpand()"></i><br>
                    <h2>Details</h2>
                    <hr>
                    <?php if ($selected_event): ?>
                    <h3 id="detail-title"><?php echo htmlspecialchars(formatTitle($selected_event["title"])); ?></h3>
                    <div class="detail-items">
                        <div class="detail-items-1"> 
                            <div class="detail-item">
                                <h4>Delivery:</h4>
                                <p id="detail-mode"><?php echo htmlspecialchars(formatTitle($selected_event["delivery"])); ?></p>
                            </div>

                            <?php if (strtolower($selected_event["delivery"]) !== "online"): ?>
                            <div class="detail-item">
                                <h4>Venue:</h4>
                                <p id="detail-venue"><?php echo htmlspecialchars(formatTitle($selected_event["venue"] ?? "Not specified")); ?></p>
                            </div>
                            <?php endif; ?>

                            <div class="detail-item">
                                <h4>Event Specification:</h4>
                                <p id="detail-specification"><?php echo htmlspecialchars(formatTitle($selected_event["specification"])); ?></p>
                            </div>

                            <div class="detail-item">
                                <h4>Proponents:</h4>
                                <p id="detail-organizer"><?php echo htmlspecialchars(formatTitle($selected_event["proponent"] ?? "Not specified")); ?></p>
                            </div>

                            <div class="detail-item">
                                <h4>Speaker(s):</h4>
                                <p id="detail-speakers">
                                <?php 
                                if (!empty($speakers)) {
                                    $formatted_speakers = array();
                                    foreach ($speakers as $speaker) {
                                        $formatted_speakers[] = formatTitle($speaker);
                                    }
                                    echo htmlspecialchars(implode(", ", $formatted_speakers));
                                } else {
                                    echo "Not specified";
                                }
                                ?>
                                </p>
                            </div>
                        </div>
                        <div class="detail-items-2">
                            
                            <div class="detail-item expanded-content">
                                <h4>Start:</h4>
                                <p id="detail-start"><?php echo date('F j, Y', strtotime($selected_event["start_date"])); ?></p>
                            </div>

                            <div class="detail-item expanded-content">
                                <h4>End:</h4>
                                <p id="detail-end"><?php echo date('F j, Y', strtotime($selected_event["end_date"])); ?></p>
                            </div>

                            <div class="detail-item expanded-content">
                                <h4>Event Schedule:</h4>
                                <div id="detail-event-days">
                                    <?php 
                                    if (!empty($event_schedule)) {
                                        foreach ($event_schedule as $day) {
                                            $formatted_date = date('F j, Y', strtotime($day['day_date']));
                                            $start_time = date('g:i A', strtotime($day['start_time']));
                                            $end_time = date('g:i A', strtotime($day['end_time']));
                                            
                                            echo '<p><strong>Day ' . htmlspecialchars($day['day_number']) . '</strong>: ' . 
                                                htmlspecialchars($formatted_date) . ', ' . 
                                                htmlspecialchars($start_time) . ' - ' . 
                                                htmlspecialchars($end_time) . '</p>';
                                        }
                                    } else {
                                        echo "<p>No schedule information available</p>";
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="detail-item expanded-content">
                                <h4>Status:</h4>
                                <p id="detail-status"><?php echo ucfirst(strtolower(htmlspecialchars($selected_event["status"]))); ?></p>
                            </div>

                        </div>
                    </div>
                    <br>
                    <?php if (!$is_registered): ?>
                        <?php if ($selected_event["status"] === "Past" || $selected_event["status"] === "Ongoing"): ?>        
                            <a class="create-btn" style="visibility: hidden;">Register</a>
                        <?php else: ?>
                            <a class="create-btn" href="register.php?event_id=<?php echo urlencode($selected_event['id']); ?>">Register</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a class="create-btn" href="unregister.php?event_id=<?php echo urlencode($selected_event['id']); ?>" style="background-color:rgb(117, 130, 14); border-style: none; cursor: pointer;" onclick="return confirm('Are you sure you want to unregister from this event?');">Unregister</a>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="detail-item">
                        <p>Select an event to view details</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Function to switch tabs
function switchTab(tabName) {
    // Hide all tab contents
    var tabContents = document.getElementsByClassName('tab-content');
    for (var i = 0; i < tabContents.length; i++) {
        tabContents[i].classList.remove('active');
    }
    
    // Remove active class from all tabs
    var tabs = document.getElementsByClassName('tab');
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove('active');
    }
    
    // Show the selected tab content and mark tab as active
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Explicitly activate the clicked tab button
    document.querySelector('.tab:nth-child(' + (tabName === 'registered' ? '1' : '2') + ')').classList.add('active');
    
    // Update URL with the active tab parameter
    if (window.location.href.includes('event_id=')) {
        // If there's an event ID in the URL, preserve it
        var eventId = new URLSearchParams(window.location.search).get('event_id');
        window.history.replaceState(null, null, `?event_id=${eventId}&tab=${tabName}`);
    } else {
        window.history.replaceState(null, null, `?tab=${tabName}`);
    }
}

// Function to show alert for past or ongoing events
function showStatusAlert(status) {
    alert("You can't register to '" + status + "' events.");
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

// Document ready function
document.addEventListener('DOMContentLoaded', function() {
    // User profile toggle and logout menu
    const userProfileToggle = document.getElementById('userProfileToggle');
    const logoutMenu = document.getElementById('logoutMenu');
    const sidebar = document.getElementById('sidebar');
    const content = document.querySelector('.content');
    const toggleBtn = document.getElementById('toggleSidebar');
    const userAvatar = document.querySelector('.user-avatar');

    // Check if sidebar state is saved in localStorage
    const isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    
    // Set initial state based on localStorage
    if (isSidebarCollapsed) {
        sidebar.classList.add('collapsed');
        content.classList.add('expanded');
        userAvatar.style.cursor = 'default'; // Make avatar non-clickable
        userProfileToggle.style.pointerEvents = 'none'; // Disable click events
    } else {
        userAvatar.style.cursor = 'pointer'; // Make avatar clickable
        userProfileToggle.style.pointerEvents = 'auto'; // Enable click events
    }

    // Toggle sidebar when button is clicked
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('expanded');

            // Update avatar clickability based on sidebar state
            if (sidebar.classList.contains('collapsed')) {
                userAvatar.style.cursor = 'default';
                userProfileToggle.style.pointerEvents = 'none';
            } else {
                userAvatar.style.cursor = 'pointer';
                userProfileToggle.style.pointerEvents = 'auto';
            }
            
            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    }

    // Toggle logout menu when user profile is clicked
    if (userProfileToggle) {
        userProfileToggle.addEventListener('click', function(event) {
            // Only toggle logout menu if sidebar is not collapsed
            if (!sidebar.classList.contains('collapsed')) {
                event.stopPropagation();
                logoutMenu.classList.toggle('active');
            }
        });
    }

    // Close the logout menu when clicking outside
    document.addEventListener('click', function(event) {
        if (logoutMenu && userProfileToggle && !userProfileToggle.contains(event.target)) {
            logoutMenu.classList.remove('active');
        }
    });

    // Sort events functionality
    const sortButton = document.getElementById('sortButton');
    if (sortButton) {
        sortButton.addEventListener('click', function() {
            // Get the current sort order from the URL
            const currentSortOrder = new URLSearchParams(window.location.search).get('sort') || 'ASC';
            
            // Toggle sort order
            const newSortOrder = (currentSortOrder === 'ASC') ? 'DESC' : 'ASC';
            
            // Update the URL to reflect the new sort order
            window.location.href = window.location.pathname + '?sort=' + newSortOrder;
        });
    }

    // Update the sort order label and button text on page load
    const currentSortOrder = new URLSearchParams(window.location.search).get('sort') || 'ASC';
    // Using getElementById with a null check since the element might not exist
    const sortOrderLabel = document.getElementById('sortOrderLabel');
    if (sortOrderLabel) {
        sortOrderLabel.textContent = currentSortOrder === 'ASC' ? 'Ascending' : 'Descending';
    }
    
    if (sortButton) {
        sortButton.textContent = 'Sort Events: ' + (currentSortOrder === 'ASC' ? 'Asc' : 'Des');
    }
    
    // Check if there's a selected event
    const selectedEvent = document.querySelector('.event.selected');
    
    // If a selected event exists, scroll to it
    if (selectedEvent) {
        // Smooth scroll to the element
        selectedEvent.scrollIntoView({
            behavior: 'smooth',
            block: 'center' // Centers the element in the viewport
        });
        
        // Optional: Add a brief highlight effect
        setTimeout(function() {
            selectedEvent.style.transition = 'background-color 0.5s';
            const originalBackground = selectedEvent.style.backgroundColor;
            selectedEvent.style.backgroundColor = '#20cd6d'; // Flash with a different color
            
            setTimeout(function() {
                selectedEvent.style.backgroundColor = originalBackground;
            }, 700);
        }, 300);
    }
    
    // Get the search input element
    const searchInput = document.querySelector('.search-input');
    
    // Add event listener for input changes
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            // Get all event elements from both tabs
            const registeredEvents = document.querySelectorAll('#registered-tab .event');
            const unregisteredEvents = document.querySelectorAll('#unregistered-tab .event');
            
            // Search function for events
            function filterEvents(events) {
                let visibleCount = 0;
                
                events.forEach(event => {
                    // Get the event title and other searchable content
                    const title = event.querySelector('h3').textContent.toLowerCase();
                    const specification = event.querySelector('p').textContent.toLowerCase();
                    const date = event.querySelector('.event-dates') ? 
                        event.querySelector('.event-dates').textContent.toLowerCase() : 
                        event.querySelectorAll('p')[1].textContent.toLowerCase();
                    const status = event.querySelector('.status-badge').textContent.toLowerCase();
                    
                    // Combine all searchable content
                    const searchableContent = `${title} ${specification} ${date} ${status}`;
                    
                    // Check if the search term exists in any of the content
                    if (searchableContent.includes(searchTerm)) {
                        event.style.display = 'block';
                        visibleCount++;
                    } else {
                        event.style.display = 'none';
                    }
                });
                
                return visibleCount;
            }
            
            // Apply filter to both tabs
            const registeredCount = filterEvents(registeredEvents);
            const unregisteredCount = filterEvents(unregisteredEvents);
            
            // Update the badge counts
            updateBadgeCount('registered', registeredCount);
            updateBadgeCount('unregistered', unregisteredCount);
            
            // Show "No results found" message if needed
            displayNoResultsMessage('registered-tab', registeredCount);
            displayNoResultsMessage('unregistered-tab', unregisteredCount);
        });
        
        // Add clear button functionality
        searchInput.addEventListener('keyup', function(e) {
            // Check if Escape key was pressed or input is empty
            if (e.key === 'Escape' || this.value === '') {
                this.value = '';
                // Trigger the input event to reset the search
                this.dispatchEvent(new Event('input'));
            }
        });
    }
    
    // Function to update badge count
    function updateBadgeCount(tabName, count) {
        const badge = document.querySelector(`.tab:nth-child(${tabName === 'registered' ? '1' : '2'}) .badge`);
        if (badge) {
            badge.textContent = count;
        }
    }
    
    // Function to display "No results found" message
    function displayNoResultsMessage(tabId, count) {
        const tabContent = document.getElementById(tabId);
        
        // Remove existing no-results message if it exists
        const existingMessage = tabContent.querySelector('.no-results-message');
        if (existingMessage) {
            existingMessage.remove();
        }
        
        // Add no-results message if no events were found
        if (count === 0) {
            const noResultsMessage = document.createElement('p');
            noResultsMessage.className = 'no-results-message';
            noResultsMessage.textContent = 'No events found matching your search criteria.';
            noResultsMessage.style.textAlign = 'center';
            noResultsMessage.style.padding = '20px';
            noResultsMessage.style.color = '#666';
            noResultsMessage.style.fontFamily = 'Montserrat, sans-serif';
            
            // Insert after the heading
            const heading = tabContent.querySelector('h2');
            heading.insertAdjacentElement('afterend', noResultsMessage);
        }
    }
    
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
});

</script>

</body>
</html>

<?php