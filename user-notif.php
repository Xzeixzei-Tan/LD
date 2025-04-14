<?php
require_once 'session_manager.php';
validateUserSession();


// User is already being redirected if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signup.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Display session messages if any exist
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-info">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']); // Clear the message after displaying
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
            // Default certificate path if record doesn't exist
            // Format based on your folder naming convention (event_name rather than event_id)
            $certificate_filename = "Certificate_" . $event_title . "_" . $user_id . ".pdf";
            $certificate_path = "certificates/" . preg_replace('/[^a-zA-Z0-9_-]/', '_', $event_title). "/" . $certificate_filename;
        }
    }
}

// Fetch notifications for user and group them by date
$notif_query = "SELECT n.id, n.message, n.created_at, n.is_read, n.notification_subtype, n.event_id, DATE(n.created_at) as notification_date, n.evaluation_link
                FROM notifications n
                WHERE n.user_id = ? 
                AND n.notification_type = 'user'
                AND (
                    (n.notification_subtype NOT IN ('update_event', 'certificate'))
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
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="styles/user-notif.css" rel="stylesheet">
    <script src="scripts/session-handler.js"></script>
    <title>Notifications - Event Management System</title>
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
            <a href="user-dashboard.php">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="user-events.php">
                <i class="fas fa-calendar-alt"></i>
                <span>Events</span>
            </a>
            <a href="user-notif.php" class="active">
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
            <h1>Notifications</h1>
            <hr><br>

            <div class="notification-card">
                <div class="notification-content">
                    <?php
                    if ($notif_result->num_rows > 0) {
                        $current_date = '';
                        $today = date('Y-m-d');
                        $yesterday = date('Y-m-d', strtotime('-1 day'));
                        
                        while ($row = $notif_result->fetch_assoc()) { 
                            $notification_id = $row['id'];
                            $is_read = $row['is_read'] ? 'read' : 'unread';
                            $is_important = $row['is_read'] ? 'read' : 'important';
                            $notification_date = $row['notification_date'];
                            
                            // Display date header if this is a new date
                            if ($notification_date != $current_date) {
                                // Close previous section if it's not the first one
                                if ($current_date != '') {
                                    echo '</div>'; // Close previous date section
                                }
                                
                                // Format the date header
                                if ($notification_date == $today) {
                                    $date_header = "Today";
                                } else if ($notification_date == $yesterday) {
                                    $date_header = "Yesterday";
                                } else {
                                    $date_header = date('F j, Y', strtotime($notification_date));
                                }
                                
                                echo '<div class="date-section">';
                                echo '<h2 class="date-header">' . $date_header . '</h2>';
                                
                                $current_date = $notification_date;
                            }
                            ?>
                            <div class="notifs <?php echo $is_important; ?>">
                                <?php 
                                // Determine the redirect URL and action based on notification type
                                if (!empty($row['event_id']) && $row['notification_subtype'] == 'certificate') {
                                    // For certificate notifications, create a visible button that triggers the modal
                                    ?>
                                    <a style="cursor: pointer;" class="events-btn <?php echo $is_read; ?>" 
                                       onclick="showCertificateModal('<?php echo $row['event_id']; ?>', '<?php echo addslashes(htmlspecialchars($row['message'])); ?>', <?php echo $notification_id; ?>);">
                                        <h3><?php 
                                            $message = htmlspecialchars($row['message']);
                                            // Find the position of "event:" in the message
                                            $pos = strpos($message, "event:");
                                            if ($pos !== false) {
                                                // Find the position of "is now available" or similar ending text
                                                $end_pos = strpos($message, "is now available");
                                                if ($end_pos !== false) {
                                                    // Extract the event title
                                                    $event_title = substr($message, $pos + 7, $end_pos - ($pos + 7) - 1);
                                                    // Replace the original title with the bold version
                                                    $message = str_replace("event: $event_title", "event: <strong>$event_title</strong>", $message);
                                                }
                                            }
                                            echo $message;
                                        ?></h3>
                                        <small><?php echo $row['created_at']; ?></small>
                                    </a>
                                    <?php 
                                } elseif (!empty($row['event_id']) && $row['notification_subtype'] == 'evaluation') {
                                    // Special handling for evaluation notifications
                                    $notification_id = $row['id'];
                                    
                                    // Extract the evaluation link from the message
                                    // The message format is: "Please complete the evaluation for the event: {event_title}. Click the link to proceed: {evaluation_link}"
                                    preg_match('/Click the link to proceed: (https?:\/\/\S+)/', $row['message'], $matches);
                                    $evaluation_link = !empty($matches[1]) ? $matches[1] : '';
                                    
                                    if (!empty($evaluation_link)) {
                                        $redirect_url = $evaluation_link;
                                    } else {
                                        // Fallback if no link is found in the message
                                        $redirect_url = "user-events.php?event_id=" . urlencode($row['event_id']);
                                    }
                                    ?>
                                    <a class="events-btn <?php echo $is_read; ?>" 
                                       href="mark_notification_read.php?notification_id=<?php echo $notification_id; ?>&redirect=<?php echo urlencode($redirect_url); ?>">
                                        <div class="events-btn">
                                            <p><?php echo htmlspecialchars($row['message']); ?></p>
                                            <br><small><?php echo $row['created_at']; ?></small>
                                        </div>
                                    </a>  
                                <?php
                                } elseif (!empty($row['event_id']) && $row['notification_subtype'] == 'new_event') {
                                    $redirect_url = "user-events.php?event_id=" . urlencode($row['event_id']);
                                    ?>
                                    <a class="events-btn <?php echo $is_read; ?>" 
                                       href="mark_notification_read.php?notification_id=<?php echo $notification_id; ?>&redirect=<?php echo urlencode($redirect_url); ?>">
                                        <h3><?php echo htmlspecialchars($row['message']); ?></h3>
                                        <small><?php echo $row['created_at']; ?></small>
                                    </a>
                                <?php
                                } elseif (!empty($row['event_id']) && $row['notification_subtype'] == 'event_reminder') {
                                    $redirect_url = "user-events.php?event_id=" . urlencode($row['event_id']);
                                    ?>
                                    <a class="events-btn <?php echo $is_read; ?>" 
                                       href="mark_notification_read.php?notification_id=<?php echo $notification_id; ?>&redirect=<?php echo urlencode($redirect_url); ?>">
                                        <h3><?php echo htmlspecialchars($row['message']); ?></h3>
                                        <small><?php echo $row['created_at']; ?></small>
                                    </a>
                                <?php
                                } elseif (!empty($row['event_id']) && $row['notification_subtype'] == 'event_registration') {
                                    $redirect_url = "user-events.php?event_id=" . urlencode($row['event_id']);
                                    ?>
                                    <a class="events-btn <?php echo $is_read; ?>" 
                                       href="mark_notification_read.php?notification_id=<?php echo $notification_id; ?>&redirect=<?php echo urlencode($redirect_url); ?>">
                                        <h3><?php echo htmlspecialchars($row['message']); ?></h3>
                                        <small><?php echo $row['created_at']; ?></small>
                                    </a>
                                <?php
                                } else {
                                    $redirect_url = "user-notif.php?event_id=" . urlencode($row['event_id']);
                                    ?>
                                    <a class="events-btn <?php echo $is_read; ?>" 
                                       href="mark_notification_read.php?notification_id=<?php echo $notification_id; ?>&redirect=<?php echo urlencode($redirect_url); ?>">
                                        <h3><?php echo htmlspecialchars($row['message']); ?></h3>
                                        <small><?php echo $row['created_at']; ?></small>
                                    </a>
                                <?php 
                                }
                                ?>
                            </div>
                        <?php 
                        } // End of while loop
                        
                        // Close the last date section
                        if ($current_date != '') {
                            echo '</div>';
                        }
                    } else { 
                        ?>
                        <p>No notifications found.</p>
                    <?php 
                    } 
                    ?>
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
    // Document ready function
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
    });

    // Modal functions (defined outside the DOMContentLoaded event for global access)
    function showModal(eventId, message) {
        // Update the modal message
        document.getElementById('modal-message').textContent = message;
        
        // Redirect to the same page with event_id parameter to fetch certificate info
        var currentUrl = window.location.href.split('?')[0]; // Get current URL without parameters
        var newUrl = currentUrl + "?event_id=" + eventId + "&modal=true";
        window.location.href = newUrl;
    }

    function closeModal() {
        var modal = document.getElementById("eventModal");
        modal.style.display = "none";
        
        // Remove selected class from all events
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
        window.location.href = "mark_notification_read.php?notification_id=" + notificationId + "&redirect=" + encodeURIComponent("user-notif.php");
    }

    function showCertificateModal(eventId, message, notificationId) {
        // Update the modal message
        document.getElementById('modal-message').innerHTML = message;

        const modalMessage = document.getElementById('modal-message');
        
        // Parse the message to identify and bold the event title
        let formattedMessage = message;
        const eventPrefix = "event:";
        const availableSuffix = "is now available";
        
        const startPos = message.indexOf(eventPrefix);
        const endPos = message.indexOf(availableSuffix);
        
        if (startPos !== -1 && endPos !== -1) {
            // Extract event title
            const beforeTitle = message.substring(0, startPos + eventPrefix.length);
            const title = message.substring(startPos + eventPrefix.length, endPos);
            const afterTitle = message.substring(endPos);
            
            // Format with bold title
            formattedMessage = beforeTitle + " <strong>" + title.trim() + "</strong> " + afterTitle;
        }
        
        modalMessage.innerHTML = formattedMessage;
        
        // Mark notification as read via AJAX
        fetch('mark_notification_read.php?notification_id=' + notificationId + '&ajax=true')
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Update UI to show notification as read if needed
                    const notificationElement = document.querySelector(`[onclick*="${notificationId}"]`).closest('.notifs');
                    if(notificationElement) {
                        notificationElement.classList.remove('important');
                        notificationElement.classList.add('read');
                        
                        // Update the link class as well
                        const linkElement = notificationElement.querySelector('.events-btn');
                        if(linkElement) {
                            linkElement.classList.add('read');
                        }
                    }
                }
            })
            .catch(error => console.error('Error marking notification as read:', error));
        
        // Show the modal
        var modal = document.getElementById("eventModal");
        modal.style.display = "flex";
        
        // Update URL without reloading the page (for certificate info)
        const url = new URL(window.location.href);
        url.searchParams.set('event_id', eventId);
        url.searchParams.set('modal', 'true');
        window.history.pushState({}, '', url);
        
        // If needed, fetch certificate details via AJAX
        fetchCertificateDetails(eventId);
    }

    function fetchCertificateDetails(eventId) {
        // Optional: Only implement if you need to dynamically update certificate details
        fetch('get_certificate_details.php?event_id=' + eventId)
            .then(response => response.json())
            .then(data => {
                if(data.certificate_path) {
                    document.querySelector('.pdf-filename a').href = data.certificate_path;
                }
            })
            .catch(error => console.error('Error fetching certificate details:', error));
    }

    // Get the modal
    var modal = document.getElementById("eventModal");

    <?php if (isset($_GET['modal']) && $_GET['modal'] == 'true'): ?>
    // Automatically open modal if the page loaded with modal=true parameter
    document.addEventListener('DOMContentLoaded', function() {
        modal.style.display = "flex";
    });
    <?php endif; ?>  
    </script>
</body>
</html>