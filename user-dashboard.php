<?php
require_once 'config.php';

// Start the session
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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
    
    // Add JavaScript to automatically show the modal when the page loads
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
            // Default certificate path if record doesn't exist
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
	<title>Dashboard</title>
</head>
<style type="text/css">
	* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body, html {
    height: 100%;
}

/* Root font size for responsive typography */
:root {
    font-size: 16px; /* Base font size */
}

/* Sidebar styling */
.sidebar {
    position: fixed;
    width: 250px;
    height: 100vh;
    background-color: #12753E;
    color: #ffffff;
    padding: 2rem 1rem;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    transition: all 0.3s ease;
    z-index: 999;
}

.sidebar.collapsed {
    width: 90px;
    padding: 2rem 0.5rem;
}

.sidebar .logo {
    margin-bottom: 1rem;
    margin-left: 5%;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sidebar.collapsed .logo {
    margin-left: 0;
    justify-content: center;
}

.toggle-btn {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 5px;
    border-radius: 4px;
    transition: background 0.2s;
}

.toggle-btn:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.sidebar .menu {
    margin-top: 50%;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.sidebar.collapsed .menu {
    align-items: center;
    margin-top: 50%;
}

.sidebar .menu a {
    color: #ffffff;
    text-decoration: none;
    padding: 1rem;
    display: flex;
    align-items: center;
    font-size: 1rem;
    border-radius: 5px;
    transition: background 0.3s;
    font-family: 'Tilt Warp', sans-serif;
    margin-bottom: .5rem;
    width: 100%;
}

.sidebar.collapsed .menu a {
    justify-content: center;
    padding: 1rem 0;
    width: 90%;
}

.sidebar .menu a span {
    margin-left: 0.5rem;
    transition: opacity 0.2s;
    font-family: 'Tilt Warp', sans-serif;
    font-size: clamp(0.8rem, 1vw, 1rem);
}

.sidebar.collapsed .menu a span {
    opacity: 0;
    width: 0;
    height: 0;
    overflow: hidden;
    display: none;
}

.sidebar .menu a:hover,
.sidebar .menu a.active {
    background-color: white;
    color: #12753E;
}

.sidebar .menu a i {
    margin-right: 0.5rem;
    min-width: 20px;
    text-align: center;
    font-size: clamp(1rem, 1.2vw, 1.5rem);
}

.sidebar.collapsed .menu a i {
    margin-right: 0;
    font-size: 1.2rem;
}

/* Content area styling */
.content {
    flex: 1;
    background-color: #ffffff;
    padding: 2rem;
    margin-left: 250px;
    transition: margin-left 0.3s ease;
}

.content.expanded {
    margin-left: 90px;
}

.content-header h1 {
    font-size: clamp(1.2rem, 2vw, 1.5rem);
    color: #333333;
    font-family: 'Wensley Demo', sans-serif;
    text-align: center;
    margin: 0 auto 0.5rem;
}

.content-header p {
    color: #999;
    font-size: clamp(0.8rem, 1.5vw, 1rem);
    text-align: center;
    margin: 0 auto;
    font-family: 'LT Cushion Light', sans-serif;
}

.content-header img {
    display: block;
    max-width: 100%;
    height: auto;
    margin: 0 auto 1rem;
    filter: drop-shadow(0px 4px 5px rgba(0, 0, 0, 0.3));
}

.content-body h1 {
    font-size: clamp(1.5rem, 3vw, 2.2rem);
    padding: 10px 0;
    font-family: 'Montserrat ExtraBold', sans-serif;
    color: black;
}

.content-body hr {
    width: 100%;
    border: none;
    height: 2px;
    background-color: #95A613;
    margin-bottom: 20px;
}

/* Content area sections */
.content-area {
    display: flex;
    flex-direction: row;
    padding: 20px 0 40px;
    gap: 30px;
    flex-wrap: wrap;
}

.events-section, .notifications-section {
    background-color: white;
    border-radius: 12px;
    border: 1px solid #e0e0e0;
    padding: 1.5rem;
    box-shadow: 0 6px 16px rgba(18, 117, 62, 0.08);
    font-family: 'Wesley Demo', serif;
    transition: all 0.3s ease;
    max-height: fit-content;
    text-decoration: none;
    flex: 1;
    min-width: 250px;
}

.events-section {
    flex: 3;
    max-height: 500px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    position: relative;
    z-index: 1;
}

.events-section:hover, .notifications-section:hover {
    box-shadow: 0 8px 24px rgba(18, 117, 62, 0.12);
}

/* Scrollbar Styling */
.events-section::-webkit-scrollbar,
.notifications-section::-webkit-scrollbar {
    width: 6px;
}

.events-section::-webkit-scrollbar-track,
.notifications-section::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.events-section::-webkit-scrollbar-thumb,
.notifications-section::-webkit-scrollbar-thumb {
    background: #12753E;
    border-radius: 10px;
}

.events-section::-webkit-scrollbar-thumb:hover,
.notifications-section::-webkit-scrollbar-thumb:hover {
    background: #0e5c31;
}

.notifications-section {
    flex: 2;
    max-height: 500px;
    overflow-y: auto;
}

.events-section h2, .notifications-section h2 {
    font-size: clamp(1.2rem, 2vw, 1.4rem);
    font-family: 'Montserrat ExtraBold', sans-serif;
    font-weight: bold;
    margin-bottom: 20px;
    color: #12753E;
    position: relative;
    border-bottom: 2px solid #f0f2fa;
    padding-bottom: 10px;
    z-index: 10;
}

.event, .notification {
    text-decoration: none;
    background-color: #f8fcfa;
    border-left: 4px solid #12753E;
    border-radius: 8px;
    padding: 18px;
    position: relative;
    transition: all 0.2s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
    margin-bottom: 15px;
}

.event:hover, .notification:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(18, 117, 62, 0.1);
    background-color: #edf7f2;
}

.event-content h3 {
    color: #12753E;
    font-size: clamp(1rem, 1.8vw, 1.1rem);
    margin-bottom: 8px;
    font-family: 'Montserrat ExtraBold', sans-serif;
}

.event-content p {
    font-size: clamp(0.85rem, 1.5vw, 0.95rem);
    color: #555;
    font-family: 'Montserrat Medium', sans-serif;
    line-height: 1.4;
}

.event-content p strong {
    font-family: 'Montserrat', sans-serif;
    color: rgb(84, 95, 89);
}

.event-content span {
    position: absolute;
    bottom: 10px;
    right: 15px;
    padding: 6px 14px;
    border-radius: 20px;
    font-family: 'Tilt Warp', sans-serif;
    font-size: clamp(0.75rem, 1vw, 0.8rem);
    letter-spacing: 0.5px;
    text-transform: uppercase;
    font-weight: 500;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Status-specific styling */
.event-content span.ongoing {
    background: #12753E;
    color: white;
    animation: pulse 2s infinite;
}

.event-content span.upcoming {
    background: #95A613;
    color: white;
}

/* Add a pulse animation for ongoing events */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(18, 117, 62, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(18, 117, 62, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(18, 117, 62, 0);
    }
}

.event-dates {
    font-size: clamp(0.75rem, 1.2vw, 0.85rem);
    color: #777;
    margin-top: 5px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
}

.event-dates i {
    margin-right: 5px;
    color: #12753E;
}

.notification {
    border-left: 4px solid #95A613;
    margin-bottom: 15px;
}

/* New Styles for Read Notifications */
.notification .read {
    opacity: 0.7;
}

.notification.read {
    border-left: 4px solid #ccc;
    background-color: #f0f0f0;
}

.notification.read:hover {
    background-color: #e8e8e8;
}

.notification.read p {
    color: #888;
}

.notification.read small {
    color: #aaa;
}

.notification p {
    font-size: clamp(0.85rem, 1.5vw, 0.95rem);
    font-family: 'Montserrat Medium', sans-serif;
    color: #555;
    line-height: 1.4;
}

#events-btn.read {
    text-decoration: none;
    color: #888;
}

#events-btn.unread {
    text-decoration: none;
}

.notification-content {
    text-decoration: none;
    transition: all 0.2s ease;
}

.notification.read .notification-content {
    opacity: 0.7;
}

.notification.important {
    text-decoration: none;
    border-left: 4px solid rgb(28, 26, 153);
    background-color:rgb(249, 250, 254);
}

.notification.important:hover {
    background-color:rgb(242, 243, 253);
}

.events-btn {
    text-decoration: none;
    color: #333;
    display: block;
    height: fit-content;
    width: 100%;
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
    align-items: center;
    justify-content: center;
}

.modal-content {
    position: relative;
    background-color: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    width: 90%;
    max-width: 500px;
    animation: modalopen 0.4s;
    border: 2px solid #12753E;
    margin: 0 auto;
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
    font-family: 'Montserrat Extrabold', sans-serif;
    font-size: clamp(1.2rem, 2vw, 1.5rem);
}

.modal-body .detail-item {
    margin-bottom: 15px;
}

.modal-body .detail-item h3 {
    margin: 0;
    font-size: clamp(0.9rem, 1.5vw, 1em);
    font-family: 'Montserrat', sans-serif;
    color: rgb(14, 19, 44);
}

.modal-body .detail-item p {
    margin: 5px 0 0;
    color: #555;
    font-size: clamp(0.8rem, 1.3vw, 0.9em);
    font-family: 'Montserrat Medium', sans-serif;
}

.modal-footer {
    padding-top: 15px;
    border-top: 1px solid #eee;
    margin-top: 20px;
    text-align: right;
}

.pdf-preview {
    align-content: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.pdf-icon img {
    max-width: 120px;
    width: 100%;
    height: auto;
    cursor: pointer;
    display: block;
    margin: 0 auto;
}

.pdf-filename a {
    font-size: clamp(0.8rem, 1.3vw, 0.95rem);
    color: #12753E;
    text-align: center;
    display: block;
}

.pdf-filename {
    margin-top: 10px;
    width: 100%;
    text-align: center;
}

/* Media Queries for Responsiveness */
@media (max-width: 1200px) {
    :root {
        font-size: 15px;
    }
    
    .content {
        padding: 1.5rem;
    }
}

@media (max-width: 992px) {
    :root {
        font-size: 14px;
    }
    
    .content {
        margin-left: 90px;
        padding: 1.5rem;
    }
    
    .sidebar {
        width: 90px;
        padding: 2rem 0.5rem;
    }
    
    .sidebar .logo {
        margin-left: 0;
        justify-content: center;
    }
    
    .sidebar .menu a {
        justify-content: center;
        padding: 1rem 0;
        width: 90%;
    }
    
    .sidebar .menu a span {
        display: none;
    }
    
    .user-profile {
        justify-content: center;
        padding: 15px 0;
    }
    
    .username {
        display: none;
    }
    
    .content-area {
        flex-direction: column;
    }
    
    .events-section, .notifications-section {
        width: 100%;
        max-width: 100%;
    }
}

@media (max-width: 768px) {
    :root {
        font-size: 13px;
    }
    
    .content {
        padding: 1rem;
        margin-left: 70px;
    }
    
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
    
    .modal-content {
        width: 95%;
        padding: 15px;
    }
    
    .events-section, .notifications-section {
        padding: 15px;
    }
}

@media (max-width: 576px) {
    :root {
        font-size: 12px;
    }
    
    .content {
        padding: 0.75rem;
        margin-left: 60px;
    }
    
    .sidebar {
        width: 60px;
    }
    
    .user-avatar img {
        width: 30px;
        height: 30px;
    }
    
    .toggle-btn {
        font-size: 1.2rem;
    }
    
    .sidebar .menu a i {
        font-size: 1rem;
    }
    
    .event, .notification {
        padding: 12px;
    }
    
    .event-content span {
        position: static;
        display: inline-block;
        margin-top: 10px;
    }
}

/* Logout section styling */
.logout-section {
    padding-bottom: 2rem;
    width: 100%;
    border-top: 1px solid white;
    display: flex;
    align-items: center;
    position: absolute;
    bottom: 50px;
    left: 0;
    right: 0;
    background-color: #12753E;
    cursor: pointer;
    padding: 5px;
}

.logout-btn {
    color: #ffffff;
    text-decoration: none;
    padding: 20px;
    display: flex;
    align-items: center;
    font-size: 1rem;
    border-radius: 5px;
    transition: background 0.3s;
    font-family: 'Tilt Warp', sans-serif;
    margin-bottom: -50px;
    width: 100%;

}

.sidebar.collapsed .logout-btn {
    justify-content: center;
    padding: 10px;
    width: 90%;
}

.logout-btn span {
    margin-left: 0.5rem;
    transition: opacity 0.2s;
    font-family: 'Tilt Warp', sans-serif;
    font-size: clamp(0.8rem, 1vw, 1rem);
}

.sidebar.collapsed .logout-btn span {
    opacity: 0;
    width: 0;
    height: 0;
    overflow: hidden;
    display: none;
}

.logout-btn:hover {
    background-color: #ffffff;
    color: #12753E;
}

.logout-btn i {
    margin-right: 0.5rem;
    min-width: 20px;
    text-align: center;
    font-size: clamp(1rem, 1.2vw, 1.5rem);
}

.sidebar.collapsed .logout-btn i {
    margin-right: 0;
    font-size: 1.2rem;
}

/* Make sidebar a flex container with flex-direction column */
.sidebar {
    display: flex;
    flex-direction: column;
}

/* This ensures the menu takes up available space, pushing logout to bottom */
.sidebar .menu {
    flex: 1;
}

/* Responsive adjustments for logout button */
@media (max-width: 992px) {
    .logout-btn {
        justify-content: center;
        padding: 10px;
    }
    
    .logout-btn i {
        margin-right: 0;
        font-size: 1.2rem;
    }
    
    .logout-btn span {
        display: none;
    }
}
</style>
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
         <!-- Logout button added at the bottom of sidebar with a separator line -->
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
                                    $notification_id = $notif['id']; // Add id to your SELECT statement if not already included
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
                        <img src="styles/photos/PDF.png">
                    </a>
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

<!-- Add JavaScript for the user profile toggle and logout menu -->
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
    
    // Find the certificate path based on the event ID
    // Ideally this would be passed from the notification display code
    var certificatePath = "certificates/" + eventId + "/Certificate_" + eventId + "_<?php echo $user_id; ?>.pdf";
    document.querySelector('.pdf-filename a').href = certificatePath;
    
    // Show the modal
    document.getElementById('eventModal').style.display = "flex";
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
    window.location.href = "mark_notification_read.php?notification_id=" + notificationId + "&redirect=" + encodeURIComponent("user-dashboard.php");
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