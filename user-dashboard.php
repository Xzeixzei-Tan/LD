<?php
require_once 'config.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: signup.php");
    exit();
}

if (isset($_GET['show_certificate']) && !empty($_GET['show_certificate'])) {
    $event_id = $_GET['show_certificate'];
    
    // Fetch the certificate message if needed
    $stmt = $conn->prepare("SELECT message FROM notifications WHERE event_id = ? AND notification_subtype = 'certificate' LIMIT 1");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $certificate_message = '';
    
    if ($row = $result->fetch_assoc()) {
        $certificate_message = $row['message'];
    }
    
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            showModal("' . $event_id . '", "' . addslashes(htmlspecialchars($certificate_message)) . '");
        });
    </script>';
}

$user_id = $_SESSION['user_id'];

// Display session messages if any exist
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-info">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']); // Clear the message after displaying
}

// Set the default sort order to ASC (Soonest events first)
$sortOrder = isset($_GET['sort']) && ($_GET['sort'] == 'DESC') ? 'DESC' : 'ASC';

// Fetch upcoming and ongoing events from the database
$sql = "SELECT id, title, specification, start_date, end_date,
        CASE 
            WHEN NOW() BETWEEN start_date AND end_date THEN 'Ongoing'
            ELSE 'Upcoming'
        END AS status
        FROM events 
        WHERE end_date >= NOW()
        ORDER BY start_date $sortOrder";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
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

if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];

    $show_modal = false;
    if (isset($_GET['modal']) && $_GET['modal'] == 'true') {
        $show_modal = true;
    }
    
    // Fetch event details
    $event_sql = "SELECT title FROM events WHERE id = ?";
    $stmt = $conn->prepare($event_sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $event_result = $stmt->get_result();
    
    if ($event_result->num_rows > 0) {
        $event_row = $event_result->fetch_assoc();
        $event_title = $event_row['title'];
        
        // IMPORTANT: Fetch the actual certificate path from the database
        $cert_sql = "SELECT certificate_path FROM certificates WHERE event_id = ? AND user_id = ?";
        $cert_stmt = $conn->prepare($cert_sql);
        $cert_stmt->bind_param("ii", $event_id, $user_id);
        $cert_stmt->execute();
        $cert_result = $cert_stmt->get_result();
        
        if ($cert_result->num_rows > 0) {
            $cert_row = $cert_result->fetch_assoc();
            $certificate_path = $cert_row['certificate_path'];
            
            // Extract filename from the path for display purposes
            $path_parts = pathinfo($certificate_path);
            $certificate_filename = $path_parts['basename'];
        } else {
            // Format based on your folder naming convention (event_name rather than event_id)
            $certificate_filename = "Certificate_" . $event_title . "_" . $user_id . ".pdf";
            $certificate_path = "certificates/" . preg_replace('/[^a-zA-Z0-9_-]/', '_', $event_title). "/" . $certificate_filename;
        }
    }
}

// Fetch notifications for user
$notif_query = "SELECT n.id, n.message, n.created_at, n.is_read, n.notification_subtype, n.event_id, DATE(n.created_at) as notification_date, n.evaluation_link
                FROM notifications n
                WHERE n.user_id = ? 
                AND n.notification_type = 'user'
                AND (
                    (n.notification_subtype NOT IN ('certificate'))
                    OR 
                    (n.notification_subtype = 'certificate' AND 
                     n.id = (SELECT MAX(id) FROM notifications 
                             WHERE user_id = ? 
                             AND notification_type = 'user' 
                             AND notification_subtype = 'certificate' 
                             AND event_id = n.event_id))
                    OR
                    (n.notification_subtype = 'evaluation')
                )
                ORDER BY n.created_at DESC";
$stmt = $conn->prepare($notif_query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$notif_result = $stmt->get_result();

if (!$notif_result) {
    die("Notification query failed: " . $conn->error);
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
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="styles/user-dashboard.css" rel="stylesheet">
    <script src="scripts/session-handler.js"></script>
    <title>Dashboard</title>
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
            <a href="user-dashboard.php" class="active">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="user-events.php">
                <i class="fas fa-calendar-alt"></i>
                <span>Events</span>
            </a>
            <a href="user-notif.php">
                <i class="fas fa-bell mr-3"></i>
                <span>Notification</span>
            </a>
        </div>
        <div class="logout-section">
            <a href="login.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
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
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h1>
            <hr><br>

            <div class="content-area">
                <div class="events-section">
                    <h2>Events</h2>
                    <?php while ($event = $result->fetch_assoc()) : ?>    
                        <div class="event">
                            <a class="events-btn" href="user-events.php?event_id=<?php echo urlencode($event['id']); ?>">
                                <div class="event-content">
                                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                    <p><strong>Event Specification:</strong> <?php echo htmlspecialchars($event['specification']); ?></p>
                                    <div class="event-dates">
                                        <i class="fas fa-calendar-day"></i>
                                        <?php 
                                            $start_date = new DateTime($event['start_date']);
                                            $end_date = new DateTime($event['end_date']);
                                            echo $start_date->format('M d') . ' - ' . $end_date->format('M d, Y'); 
                                        ?>
                                    </div>
                                    <?php if ($event["status"] === "Ongoing") { ?>
                                        <span class="ongoing"><i class="fas fa-circle"></i> Ongoing</span>
                                    <?php } else { ?>
                                        <span class="upcoming"><i class="fas fa-hourglass-start"></i> Upcoming</span>
                                    <?php } ?>        
                                </div>
                            </a>
                        </div>
                    <?php endwhile; ?>
                    <?php if ($result->num_rows == 0): ?>
                        <p style="color: gray; font-family: Montserrat;">No available events yet.</p>
                    <?php endif; ?>
                </div>
                <div class="notifications-section">
                    <h2>Notifications</h2>
                    <?php while ($notif = $notif_result->fetch_assoc()): ?>
                        <div class="notification <?php echo $notif['is_read'] ? 'read' : 'important'; ?>">
                            <?php 
                            // Determine the redirect URL based on notification type
                            if (!empty($notif['event_id']) && $notif['notification_subtype'] == 'certificate') {
                                $notification_id = $notif['id'];
                                $current_page = $_SERVER['PHP_SELF'];
                                $redirect_url = $current_page . (strpos($current_page, '?') !== false ? '&' : '?') . 'show_certificate=' . urlencode($notif['event_id']);
                            ?>
                                <a id="events-btn" class="<?php echo $notif['is_read'] ? 'read' : 'unread'; ?>" 
                                href="mark_notification_read.php?notification_id=<?php echo $notification_id; ?>&redirect=<?php echo urlencode($redirect_url); ?>">
                                    <div class="notification-content">
                                        <p><?php echo htmlspecialchars($notif['message']); ?></p>
                                        <br><small><?php echo $notif['created_at']; ?></small>
                                    </div>
                                </a>
                            <?php
                            } elseif (!empty($notif['event_id']) && $notif['notification_subtype'] == 'evaluation') {
                                // Special handling for evaluation notifications
                                $notification_id = $notif['id'];
                                
                                // Extract the evaluation link from the message
                                // The message format is: "Please complete the evaluation for the event: {event_title}. Click the link to proceed: {evaluation_link}"
                                preg_match('/Click the link to proceed: (https?:\/\/\S+)/', $notif['message'], $matches);
                                $evaluation_link = !empty($matches[1]) ? $matches[1] : '';
                                
                                if (!empty($evaluation_link)) {
                                    $redirect_url = $evaluation_link;
                                } else {
                                    // Fallback if no link is found in the message
                                    $redirect_url = "user-events.php?event_id=" . urlencode($notif['event_id']);
                                }
                            ?>
                                <a id="events-btn" class="<?php echo $notif['is_read'] ? 'read' : 'unread'; ?>" 
                                href="mark_notification_read.php?notification_id=<?php echo $notification_id; ?>&redirect=<?php echo urlencode($redirect_url); ?>">
                                    <div class="notification-content">
                                        <p><?php echo htmlspecialchars($notif['message']); ?></p>
                                        <br><small><?php echo $notif['created_at']; ?></small>
                                    </div>
                                </a>
                            <?php
                            } else {
                                // For all other notification types
                                if (!empty($notif['event_id']) && $notif['notification_subtype'] == 'new_event') {
                                    $redirect_url = "user-events.php?event_id=" . urlencode($notif['event_id']);
                                } elseif (!empty($notif['event_id']) && $notif['notification_subtype'] == 'event_reminder') {
                                    $redirect_url = "user-events.php?event_id=" . urlencode($notif['event_id']);
                                } elseif (!empty($notif['event_id']) && $notif['notification_subtype'] == 'update_event') {
                                    $redirect_url = "user-events.php?event_id=" . urlencode($notif['event_id']);
                                } elseif (!empty($notif['event_id']) && $notif['notification_subtype'] == 'event_registration') {
                                    $redirect_url = "user-events.php?event_id=" . urlencode($notif['event_id']);
                                } else {
                                    $redirect_url = "user-notif.php?event_id=" . urlencode($notif['event_id']);
                                }
                                
                                // You need to make sure your query also fetches the notification ID
                                $notification_id = $notif['id']; 
                            ?>
                                <a id="events-btn" class="<?php echo $notif['is_read'] ? 'read' : 'unread'; ?>" 
                                href="mark_notification_read.php?notification_id=<?php echo $notification_id; ?>&redirect=<?php echo urlencode($redirect_url); ?>">
                                    <div class="notification-content">
                                        <p><?php echo htmlspecialchars($notif['message']); ?></p>
                                        <br><small><?php echo $notif['created_at']; ?></small>    
                                    </div>
                                </a>
                            <?php } ?>
                        </div>
                    <?php endwhile; ?>
                    <?php if ($notif_result->num_rows == 0): ?>
                        <p style="color: gray;">No available notifications yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div id="eventModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <div class="modal-body">
                <div class="detail-item">
                    <p id="modal-message">Your certificate is now available to download.</p>
                </div>

                <div class="pdf-preview">
                    <div class="pdf-icon"><br>
                        <img src="styles/photos/PDF.png">
                        <div class="pdf-filename">
                            <a href="<?php echo isset($certificate_path) ? htmlspecialchars($certificate_path) : ''; ?>" download>
                                Certificate of Participation
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const content = document.querySelector('.content');
        const toggleBtn = document.getElementById('toggleSidebar');

        // Check if sidebar state is saved in localStorage
        const isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        
        // Set initial state based on localStorage
        if (isSidebarCollapsed) {
            sidebar.classList.add('collapsed');
            content.classList.add('expanded');
        }

        // Toggle sidebar when button is clicked
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                content.classList.toggle('expanded');
                
                // Save state to localStorage
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });
        }

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
    });

    function showModal(eventId, message) {
        // Update the modal message
        document.getElementById('modal-message').textContent = message;
        
        // Ideally this would be passed from the notification display code
        var certificatePath = "certificates/" + eventId + "/Certificate_" + eventId + "_<?php echo $user_id; ?>.pdf";
        document.querySelector('.pdf-filename a').href = certificatePath;
        
        // Show the modal
        document.getElementById('eventModal').style.display = "flex";
    }

    function closeModal() {
        var modal = document.getElementById("eventModal");
        modal.style.display = "none";
        
        document.querySelectorAll('.event').forEach(div => {
            div.classList.remove('selected');
        });
    }

    // Close the modal if user clicks outside of it
    window.onclick = function(event) {
        var modal = document.getElementById("eventModal");
        if (event.target == modal) {
            closeModal();
        }
    }

    function markAsRead(notificationId) {
        // Redirect to mark as read script
        window.location.href = "mark_notification_read.php?notification_id=" + notificationId + "&redirect=" + encodeURIComponent("user-dashboard.php");
    }
    
    // Get the modal
    var modal = document.getElementById("eventModal");

    function showModal(eventId, message) {
        // Update the modal message
        document.getElementById('modal-message').textContent = message;
        
        // Redirect to the same page with event_id parameter to fetch certificate info
        var currentUrl = window.location.href.split('?')[0]; // Get current URL without parameters
        var newUrl = currentUrl + "?event_id=" + eventId + "&modal=true";
        window.location.href = newUrl;
    }

    <?php if (isset($_GET['modal']) && $_GET['modal'] == 'true'): ?>
    // Automatically open modal if the page loaded with modal=true parameter
    document.addEventListener('DOMContentLoaded', function() {
        modal.style.display = "flex";
    });
    <?php endif; ?>  
    </script>


</body>
</html>