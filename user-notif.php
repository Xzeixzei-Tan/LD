<?php
require_once 'config.php';
session_start();

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
            $certificate_path = "certificates/" . $event_title . "/" . $certificate_filename;
        }
    }
}

// Fetch notifications for user
$notif_query = "SELECT id, message, created_at, is_read, notification_subtype, event_id 
                FROM notifications 
                WHERE notification_type = 'user' 
                ORDER BY created_at DESC";
$notif_result = $conn->query($notif_query);

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
    <link rel="stylesheet" href="">
    <title>Notifications - Event Management System</title>
    <style type="text/css">
    /* CSS remains unchanged */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }

    body, html {
        height: 100%;
    }

    .sidebar {
        width: 230px;
        height: 100vh;
        background-color: #12753E;
        color: white;
        display: flex;
        flex-direction: column;
        transition: width 0.3s ease;
        position: fixed;
    }

    .sidebar-content {
        margin-top: 30%;
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        font-family: Tilt Warp;
    }

    .sidebar-content a{
        font-family: 'Tilt Warp';
        color: #ffffff;
        text-decoration: none;
        padding: 1rem;
        display: flex;
        align-items: center;
        font-size: 1rem;
        border-radius: 5px;
        transition: background 0.3s;
        font-family: Tilt Warp Regular;
        margin-bottom: .5rem;
    }

    .sidebar-content span{
        font-family: Tilt Warp;
        font-size: 1rem;
    }

    .sidebar-content i{
        margin-right: 0.5rem;
    }

    .sidebar-content a:hover {
        background-color: white;
        color: #12753E; 
    }

    .sidebar-content .active{
        background-color: white;
        color: #12753E;
    }

    .user-profile {
        padding: 15px;
        border-top: 1px solid white;
        display: flex;
        align-items: center;
        position: sticky;
        bottom: 0;
        background-color: #12753E;
        width: 100%;
    }

    #logout {
        float: right;
        border: 1px solid black;
        height: 100%;
        width: 100%;
        font-color: black;
    }

    .user-avatar img{
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 2px solid white;
        padding: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        font-family: Tilt Warp;
    }

    .username {
        font-family: Tilt Warp;
    }

    .main-content {
        flex: 1;
        padding: 20px;
        background-color: #ecf0f1;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .sidebar {
            width: 70px;
        }

        .sidebar-header h2, .menu-text, .username {
            display: none;
        }

        .menu-item {
            display: flex;
            justify-content: center;
        }

        .user-profile {
            justify-content: center;
        }
    }

    .content {
        flex: 1;
        background-color: #ffffff;
        padding: 4rem;
        margin-left: 17%;
    }

    .content-header h1 {
        font-size: 1.5rem;
        color: #333333;
        font-family: Wensley Demo;
        margin-left: 32%;
    }

    .content-header p {
        color: #999;
        font-size: 1rem;
        margin-top: -3%;
        font-family: LT Cushion Light;
        margin-left: 44%;
    }

    .content-header img {
        float: left;
        margin-left: 22%;
        margin-top: -1%;
        filter: drop-shadow(0px 4px 5px rgba(0, 0, 0, 0.3));
    }

    .content-body h1{
        font-family: Montserrat;
        font-size: 2rem;
        padding: 10px;
    }

    .content-body hr{
        border: 1px solid #95A613;
    }

    .notification-card {
        display: flex;
        flex-direction: column;
    }

    /* Modified events section for 3-column layout */
    .notification-content {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        flex: 1;
    }

    .notifs {
        background-color: #d7f3e4;
        border-radius: 5px;
        padding: 25px;
        position: relative;
        transition: transform 0.2s;
        height: 100%;
    }

    .notifs:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .event.selected{
        background: #2b3a8f;
    }

    .event.selected h3{
        color: white;
    }

    .event.selected p{
        color: rgb(231, 231, 231);
    }

    /* Responsive adjustments for the grid */
    @media (max-width: 992px) {
        .events-section {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .events-section {
            grid-template-columns: 1fr;
        }
    }

    .events-section h2 {
        font-size: 22px;
        font-family: Montserrat ExtraBold;
        font-weight: bold;
        margin-bottom: 20px;
        color: #333;
    }

    .notification-content h3 {
        font-size: 15px;
        margin-bottom: 5px;
        font-family: Montserrat;
        color:rgb(14, 19, 44);
    }

    .notification-content p {
        font-size: 13px;
        color: #585858;
        font-family: Montserrat Medium;
    }

    .notification p {
        font-size: 14px;
        font-family: Montserrat;
    }

    .events-btn {

        text-decoration: none;
        color: black;
        display: block;
        height: 100%;
    }

    .notifs.read {
    opacity: 0.7;
    background-color: #f0f0f0;
    border-left: 4px solid #ccc;
    }

    .notifs.read:hover {
        background-color: #e8e8e8;
    }

    .notifs.important {
        background-color: #d7f3e4;
        border-left: 4px solid #12753E;
    }

    .events-btn.read {
        color: #888;
    }

    .events-btn small {
        display: block;
        font-size: 12px;
        margin-top: 10px;
        color: #777;
    }

    .notifs.read .events-btn small {
        color: #aaa;
    }


    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.4);
        /* Flexbox for perfect centering */
        display: none;
        align-items: center;
        justify-content: center;
        
    }

    .modal-content {
        position: relative;
        background-color: white;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        width: 60%;
        max-width: 600px;
        animation: modalopen 0.4s;
        border: 2px solid #12753E;
        /* No margin needed with flexbox centering */
        margin-left: 10%;

    }

    @keyframes modalopen {
        from {opacity: 0; transform: scale(0.9);}
        to {opacity: 1; transform: scale(1);}
    }

    .close-btn {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.3s;
        margin-top: -2%;
    }

    .close-btn:hover {
        color: #12753E;
    }

    .modal-header {
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
        margin-bottom: 20px;
    }

    .modal-header h2 {
        margin: 0;
        color: #2b3a8f;
        font-family: Montserrat Extrabold;
    }

    .modal-body .detail-item {
        margin-bottom: 15px;
    }

    .modal-body .detail-item h3 {
        margin: 0;
        font-size: 1em;
        font-family: Montserrat;
        color: rgb(14, 19, 44);
    }

    .modal-body .detail-item p {
        margin: 5px 0 0;
        color: #555;
        font-size: .9em;
        font-family: Montserrat Medium;
    }

    .modal-footer {
        padding-top: 15px;
        border-top: 1px solid #eee;
        margin-top: 20px;
        text-align: right;
    }

    .pdf-preview {
        align-content: center;
    }
        
    .pdf-icon img{
            width: 120px;
            height: 80px;
            cursor: pointer; /* Add pointer cursor to indicate it's clickable */
        }
        
    .pdf-filename a{
            font-size: 17px;
            margin-right: 123px;
            color:  #12753E;

        }
    .pdf-filename{
            margin-top: 10px;
            margin-left: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-content">
                <a href="user-dashboard.php" class="menu-item">
                    <span class="menu-icon"><i class="fas fa-home mr-3"></i></span>
                    <span class="menu-text">Home</span>
                </a>
                <a href="user-events.php" class="menu-item">
                    <span class="menu-icon"><i class="fas fa-calendar-alt mr-3"></i></span>
                    <span class="menu-text">Events</span>
                </a>
                <a href="user-notif.php" class="menu-item active">
                    <span class="menu-icon"><i class="fas fa-bell mr-3"></i></span>
                    <span class="menu-text">Notification</span>
                </a>
            </div>
            <div class="user-profile">
                <div class="user-avatar"><img src="styles/photos/jess.jpg"></div>
                <div class="username"><?php echo htmlspecialchars($_SESSION['first_name']); ?> <?php echo isset($_SESSION['last_name']) ? htmlspecialchars($_SESSION['last_name']) : ''; ?></div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="content-header">
            <img src="styles/photos/DO-LOGO.png" width="70px" height="70px">
            <p>Learning and Development</p>
            <h1>EVENT MANAGEMENT SYSTEM</h1>
        </div><br><br><br><br><br>

        <div class="content-body">
            <h1>Notifications</h1>
            <hr><br>

    <div class="notification-card">
        <div class="notification-content">
            <?php
            if ($notif_result->num_rows > 0) {
                while ($row = $notif_result->fetch_assoc()) { 
                    $notification_id = $row['id'];
                    $is_read = $row['is_read'] ? 'read' : 'unread';
                    $is_important = $row['is_read'] ? 'read' : 'important';
                    ?>
                    <div class="notifs <?php echo $is_important; ?>">
                        <?php 
                        // Determine the redirect URL and action based on notification type
                        if (!empty($row['event_id']) && $row['notification_subtype'] == 'certificate') {
                            // For certificate notifications, create a visible button that triggers the modal
                            ?>
                            <a style="cursor: pointer;" class="events-btn <?php echo $is_read; ?>" 
                            onclick="showModal('<?php echo $row['event_id']; ?>', '<?php echo addslashes(htmlspecialchars($row['message'])); ?>');">
                                <h3><?php echo htmlspecialchars($row['message']); ?></h3>
                                <small><?php echo $row['created_at']; ?></small>
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
                        }  elseif (!empty($row['event_id']) && $row['notification_subtype'] == 'event_registration') {
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
                <?php }
            } else { ?>
                <p>No notifications found.</p>
            <?php } ?>
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
                <p id="modal-message">Your certificate is now available to download.</div></p>
            </div>

            <div class="pdf-preview">
                <div class="pdf-icon"><br>
                    <a href="<?php echo isset($certificate_path) ? htmlspecialchars($certificate_path) : 'Sample.pdf'; ?>" download>
                        <img src="styles/photos/PDF.png">
                    </a>
                    <div class="pdf-filename">
                        <a href="<?php echo isset($certificate_path) ? htmlspecialchars($certificate_path) : 'Sample.pdf'; ?>" download>
                            Certificate of Participation
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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

// Function to close the modal
function closeModal() {
    modal.style.display = "none";
    
    // Remove selected class from all events
    document.querySelectorAll('.event').forEach(div => {
        div.classList.remove('selected');
    });
}

// Close the modal if user clicks outside of it
window.onclick = function(event) {
    if (event.target == modal) {
        closeModal();
    }
}

function markAsRead(notificationId) {
    // Redirect to mark as read script
    window.location.href = "mark_notification_read.php?notification_id=" + notificationId + "&redirect=" + encodeURIComponent("user-notif.php");
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