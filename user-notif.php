<?php
require_once 'config.php';
session_start();

$user_id = $_SESSION['user_id'];

// Display session messages if any exist
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-info">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']); // Clear the message after displaying
}

// Get event information if event_id is provided
$event_title = "Sample Event"; // Default value
$certificate_filename = "Certificate.pdf"; // Default value
$certificate_path = ""; // Initialize certificate path

if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];
    
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

// Get user information
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

// Check if the certificate file exists and handle file extension issue (.pdf vs .docx)
if (!empty($certificate_path)) {
    // Try both .pdf and .docx versions of the file
    $pdf_path = str_replace('.pdf', '.pdf', $certificate_path); // Keep as PDF
    $docx_path = str_replace('.pdf', '.docx', $certificate_path); // Try DOCX instead
    
    if (file_exists($docx_path)) {
        // If the DOCX version exists, use that
        $certificate_path = $docx_path;
        $certificate_filename = str_replace('.pdf', '.docx', $certificate_filename);
    } elseif (file_exists($pdf_path)) {
        // If the PDF version exists, use that
        $certificate_path = $pdf_path;
    }
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
        cursor: pointer;
        position: relative;
    }

    .logout-menu {
        position: absolute;
        top: 0;
        bottom: 100%;
        border-radius: 5px;
        padding: 10px;
        display: none;
        z-index: 1000;
        width: 85px;
    }

    .logout-menu.active {
        display: block;
    }

    .logout-btn {
        background-color: white;    
        display: block;
        width: 100%;
        padding: 8px 10px;
        color: #12753E;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-family: 'Tilt Warp', sans-serif;
        font-size: 14px;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s ease;
        position: absolute;
        top: 80%;
        left: 248%;
        z-index: 5;
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

    .cards {
        display: grid;
        display: inline-block;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }

    .card {
        margin: auto;
        margin-bottom: 2%;
        background-color: #F4F4F4;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        width: 600px;
        height: 80px;
        max-width: 1000px;
    }

    .notifications-header {
            font-size: 24px;
            font-weight: bold;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 20px;
        }
        
    .notification-card {
            display: flex;
            background-color: #f9f9f9;
            border-radius: 8px;
            overflow: hidden;
            width: 1000px;
            height: 160px;
        }
        
    .notification-content {
            padding: 20px;
            flex: 1;
            position: relative; /* Added position relative for absolute positioning of the red dot */
        }
        
    .notification-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            font-family: Montserrat;
        }
        
    .notification-instruction {
            color: #666;
            font-style: italic;
            margin-top: 15px;
            font-size: 14px;
            font-family: Montserrat;
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
        <!-- Modified user profile with logout menu -->
        <div class="user-profile" id="userProfileToggle">
            <div class="user-avatar"><img src="styles/photos/default.png"></div>
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
        </div><br><br><br><br><br>

        <div class="content-body">
            <h1>Notifications</h1>
            <hr><br>

            <div class="notification-card">
                <div class="notification-content">
                    <br>
                    <div class="notification-title">Your certificate from "<?php echo htmlspecialchars($event_title); ?>" is now available to download.</div>
                    <div class="notification-instruction">Click the file to download your certificate.</div>
                </div>
                
                <div class="pdf-preview">
                    <div class="pdf-icon"><br>
                        <a href="<?php echo !empty($certificate_path) ? htmlspecialchars($certificate_path) : 'Sample.pdf'; ?>" download>
                            <img src="styles/photos/PDF.png">
                        </a>
                        <div class="pdf-filename">
                            <a href="<?php echo !empty($certificate_path) ? htmlspecialchars($certificate_path) : 'Sample.pdf'; ?>" download>
                                Certificate of Participation
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        <!-- Add JavaScript for the user profile toggle and logout menu -->
document.getElementById('userProfileToggle').addEventListener('click', function() {
    document.getElementById('logoutMenu').classList.toggle('active');
});

// Close the menu when clicking outside
document.addEventListener('click', function(event) {
    const profile = document.getElementById('userProfileToggle');
    const menu = document.getElementById('logoutMenu');
    
    if (!profile.contains(event.target)) {
        menu.classList.remove('active');
    }
});
</script>
</body>
</html>