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
        position: fixed;
        width: 250px;
        height: 100vh;
        background-color: #12753E;
        color: #ffffff;
        padding: 2rem 1rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .sidebar .logo {
        margin-bottom: 1rem;
        margin-left: 5%;
    }

    hr{
        border: 1px solid white;
    }

    .sidebar .menu {
    	margin-top: 50%;
        display: flex;
        flex-direction: column;
        margin-bottom: 18rem;
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
        font-family: Tilt Warp Regular;
        margin-bottom: .5rem;
    }

    .sidebar .menu a:hover, .sidebar .menu a.active {
        background-color: white;
        color: #12753E;
    }

    .sidebar .menu a i {
        margin-right: 0.5rem;
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
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        font-family: 'Wesley Demo', serif;
    }

    .events-section {
        flex: 3;
    }

    .notifications-section {
        flex: 2;
    }

    .events-section h2, .notifications-section h2 {
        font-size: 22px;
        font-family: Montserrat ExtraBold;
        font-weight: bold;
        margin-bottom: 20px;
        color: #333;
    }

    .event, .notification {

        background-color: #f9f9f9;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 15px;
        position: relative;
    }

    .event.featured {
        background-color: #    background-color: #f9f9f9;    ; 
    }

    .notification.important {
        background-color: #    background-color: #f9f9f9;
        ; 
        color: white;
    }

    .event-content h3 {
        font-size: 16px;
        margin-bottom: 5px;
        font-family: Montserrat;
    }

    .event-content p {
        font-size: 13px;
        color: inherit;
        font-family: Montserrat;
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

	<div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            
            <div class="menu">
                <a href="User-Dashboard.php" class="active"><i class="fas fa-home mr-3"></i>Home</a>
                <a href="User-Event.php"><i class="fas fa-calendar-alt mr-3"></i>Events</a>
                <a href="User-Notification.php"><i class="fas fa-bell mr-3"></i>Notification</a>
                <br><br><br><br><br><br><br><br><br><br><br><br><br>
                <a href="User-Profile.php"><i class="fas fa-user-circle mr-3"></i>Profile</i></a>
            </div>
        </div>

    <div class="content">
    	<div class="content-header">
	    	<img src="styles/photos/DO-LOGO.png" width="70px" height="70px">
	    	<p>Learning and Development</p>
	    	<h1>EVENT MANAGEMENT SYSTEM</h1>
    	</div><br><br><br<br><br><br><br>

    	<div class="content-body">
	    	<h1>Welcome, User!</h1>
	    	<hr><br>

            <div class="content-area">
                <div class="events-section">
                    <h2>Events</h2>
                    <div class="event-featured">
                        <div class="event-content">

                    <div class="event">
                        <a class="events-btn" href="User-Event.html">
                        <div class="event-content">
                            <h3>HRTech Connect: The Future of Work & Innovation</h3>
                            <p>Description: A must-attend conference for HR and IT professionals...</p>
                        </div>
                    </a>
                    </div>

                    <div class="event">
                        <div class="event-content">
                            <h3>TechConnect 2025: Innovating the Future</h3>
                            <p>Description: A premier gathering of tech leaders, developers, and...</p>
                        </div>
                    </div>

                    <div class="event">
                        <div class="event-content">
                            <h3>CyberShield 2025: Strengthening Digital Defense</h3>
                            <p>Description: A cybersecurity-focused event highlighting emerging...</p>
                        </div>
                    </div>

                    <div class="event">
                        <div class="event-content">
                            <h3>SecuTech 2025: The Next Era of Cyber Defense</h3>
                            <p>Description: An essential gathering of cybersecurity professionals...</p>
                        </div>
                    </div>
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