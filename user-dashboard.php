<?php
require_once 'config.php';

// Start the session
session_start();

$user_id = $_SESSION['user_id'];

// Display session messages if any exist
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-info">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']); // Clear the message after displaying
}

// Set the default sort order to ASC (Soonest events first)
$sortOrder = isset($_GET['sort']) && ($_GET['sort'] == 'DESC') ? 'DESC' : 'ASC';

// Fetch upcoming and ongoing events from the database
$sql = "SELECT id, title, event_specification, start_datetime, end_datetime,
        CASE 
            WHEN NOW() BETWEEN start_datetime AND end_datetime THEN 'Ongoing'
            ELSE 'Upcoming'
        END AS status
        FROM events 
        WHERE end_datetime >= NOW()
        ORDER BY start_datetime $sortOrder";
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

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
	<title>Dashboard-Template</title>
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
    	font-size: 2rem;
    	padding: 10px;
        font-family: Montserrat ExtraBold;
    }

    .content-body hr{
    	border: 1px solid #95A613;
    }

    .content-area {
        display: flex;
        padding: 10px 5px 30px;
        gap: 30px;
    }

    .events-section, .notifications-section {
        background-color: white;
        border-radius: 8px;
        border: 2px solid #12753E;
        padding: 30px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        font-family: 'Wesley Demo', serif;
    }

    .events-section {
        flex: 3;
        max-height: 400px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        position: relative;
        border: 2px solid #12753E;
        padding: 30px;
    }

    /* Scrollbar Styling */
    .events-section::-webkit-scrollbar,
    .notifications-section::-webkit-scrollbar {
        width: 8px;
    }

    .events-section::-webkit-scrollbar-thumb,
    .notifications-section::-webkit-scrollbar-thumb {
        background: #555;
        border-radius: 4px;
    }

    .notifications-section {
        flex: 2;
    }

    .events-section h2, .notifications-section h2 {
        font-size: 22px;
        font-family: Montserrat ExtraBold;
        font-weight: bold;
        margin-bottom: 20px;
        color: #12753E;
    }

    .event, .notification {
        background-color: #d7f3e4;
        border: 1px solid #12753E;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 15px;
        position: relative;
    }

    .event-content h3 {
        color: #12753E;
        font-size: 18px;
        margin-bottom: 5px;
        font-family: Montserrat ExtraBold;
    }

    .event-content p {
        font-size: 14px;
        color: inherit;
        font-family: Montserrat;
    }

    .event-content span{
        position: absolute;
        bottom: 10%;
        right: 2%;
        background:rgb(119, 152, 135);
        color:rgb(237, 249, 242);
        padding: 6px 14px;
        border-radius: 20px;
        font-family: Tilt Warp;
        font-size: 13px;   
    }

    .notification p { 
        font-size: 14px;
        font-family: Montserrat;
    }

    .events-btn{
        text-decoration: none;
        color: black;
    }

</style>
<body>
        <!-- Sidebar -->
        <div class="sidebar">
        <div class="sidebar-content">
            <a href="user-dashboard.php" class="menu-item active">
                <span class="menu-icon"><i class="fas fa-home mr-3"></i></span>
                <span class="menu-text">Home</span>
            </a>
            <a href="user-events.php" class="menu-item">
                <span class="menu-icon"><i class="fas fa-calendar-alt mr-3"></i></span>
                <span class="menu-text">Events</span>
            </a>
            <a href="user-notif.php" class="menu-item">
                <span class="menu-icon"><i class="fas fa-bell mr-3"></i></span>
                <span class="menu-text">Notification</span>
            </a>

            <!-- Add more menu items as needed -->
        </div>
        <div class="user-profile">
            <div class="user-avatar"><img src="styles/photos/jess.jpg"></div>
            <div class="username"><?php echo htmlspecialchars($_SESSION['first_name']); ?> <?php echo isset($_SESSION['last_name']) ? htmlspecialchars($_SESSION['last_name']) : ''; ?></div>
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
                    <div class="event-featured">
                        <div class="event-content">
                        <?php while ($event = $result->fetch_assoc()) : ?>    
                            <div class="event">
                                <a class="events-btn" href="user-events.php?event_id=<?php echo urlencode($event['id']); ?>">
                                <div class="event-content">
                                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                    <p>Event Specification: <?php echo htmlspecialchars($event['event_specification']); ?></p>
                                    <p style="visibility: hidden;"><?php echo htmlspecialchars($event['start_datetime']); ?></p>
                                    <?php if ($event["status"] === "Ongoing") { ?>
                                            <span>Ongoing...</span>
                                    <?php } ?>        
                                </div>
                                </a>
                            </div>
                        <?php endwhile; ?>    
                        </div>
                    </div>
                </div>
                <div class="notifications-section">
                    <h2>Notifications</h2>
                    <div class="notification important">
                        <a class="events-btn" href="select_quiz.php">
                        <div class="notification-content">
                            <p>Your certificate from "Sample Event" is here. Download it now.</p>
                        </div></a>
                    </div>

                    <div class="notification">
                        <div class="notification-content">
                            <p>Sample event notification</p>
                        </div>
                    </div>

                    <div class="notification">
                        <div class="notification-content">
                            <p>Sample event notification</p>
                        </div>
                    </div>

                    <div class="notification">
                        <div class="notification-content">
                            <p>Sample event notification</p>
                        </div>
                    </div>
                </div>
            </div>
    	</div>
    </div>
</div>
</body>


</html>