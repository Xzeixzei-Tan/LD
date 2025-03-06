<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
	<title>Events</title>
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

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #3498db;
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
    	font-family: 'Montserrat ExtraBold';
    	font-size: 2rem;
    	padding: 10px;
    }

    .content-body hr{
    	border: 1px solid #95A613;
    }
    .join-btn{
    	padding: 11px;
    	padding-left: 15px;
    	padding-right: 15px;
        font-family: Montserrat;
        font-weight: bold;
        font-size: 12px;
        color: white;
        text-decoration: none;
        background-color: #12753E;
        border-radius: 5px;
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
        font-weight: bolder;
    	padding: 10px;
    }

    .content-body hr{
    	border: 1px solid #95A613;
    }
    .join-btn{
    	float: right;
    	padding: 11px;
    	padding-left: 20px;
    	padding-right: 20px;
        font-family: Montserrat;
        font-weight: bold;
        font-size: 12px;
        color: white;
        text-decoration: none;
        background-color: #12753E;
        border-radius: 5px;
    }

    .content-area {
        display: flex;
        gap: 15px;
        background-color: #ffffff;
    }

    .events-section {
        border-radius: 8px;
        padding: 20px;
        font-family: Wesley Demo;
        flex: 3;
    }

    .notifications-section {
        margin-bottom: 20%;
        flex: 2;
        background-color: white;
        border-radius: 8px;
        padding: 24px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        font-family: Montserrat;
        font-weight: bolder;
    }

    .events-section h2{
        font-size: 22px;
        margin-bottom: 20px;
        color: #333;
        font-family: Montserrat;
        font-weight: bolder;
    }

    .notifications-section h2{
        font-size: 22px;
        margin-bottom: 20px;
        color: #333;
        font-family: Montserrat;
        font-weight: bolder;
    }

    .notifications-section p{
        font-size: 13px;
        font-family: Montserrat;
        font-weight: medium;
    }

    .event, .notification {
        background-color:  rgb(215, 222, 247);
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 15px;
        position: relative;
    }

    .event.featured {
        background-color: #    background-color: #f9f9f9;
    }

    .notification.important {
        background-color: #    background-color: #f9f9f9;
        color: white;
    }

    .event h1 {
        font-size: 16px;
        margin-bottom: 5px;
        font-family: Montserrat ExtraBold;
    }

    .event-target {
        color: black;
        font-size: 15px;
    }

    .event-date {
        color: #CF7F00;
        font-size: 18px;
    }

    .event-desc {
        font-weight: lighter;        
        color: #999;
        padding: 5px;
    }

    .events-section a{
        text-decoration: none;
        color: black;
    }
    
    .event-content{
        padding: 8px;
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

    .events-btn{
        text-decoration: none;
        color: black;
    }
</style>
<body>

<!-- Sidebar -->
<div class="sidebar">
        <div class="sidebar-content">
            <a href="user-dashboard.php" class="menu-item">
                <span class="menu-icon"><i class="fas fa-home mr-3"></i></span>
                <span class="menu-text">Home</span>
            </a>
            <a href="user-events.php" class="menu-item active">
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
            <div class="user-avatar">JC</div>
            <div class="username">Jess Constante</div>
        </div>
    </div>

    <div class="content">
    	<div class="content-header">
	    	<img src="styles/photos/DO-LOGO.png" width="70px" height="70px">
	    	<p>Learning and Development</p>
	    	<h1>EVENT MANAGEMENT SYSTEM</h1>
    	</div><br><br><br><br><br>

    	<div class="content-body">
	    	<h1>Events</h1>
	    	<hr><br><br>

            <div class="content-area">
                <div class="events-section">
                    <div class="event featured">
                        <a class="events-btn" href="select_quiz.php">
                        <div class="event-content">
                            <h3>Enhancing Classroom Management for...</h3><i class="fa-solid fa-circle-info"></i>
                            <p>A workshop aimed at equipping teachers with practical strategies...</p>
                        </div></a>
                    </div>

                    
                    <div class="event">
                        <div class="event-content">
                            <h3>HRTech Connect: The Future of Work & Innovation</h3>
                            <p>Description: A must-attend conference for HR and IT professionals...</p>
                        </div>
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

                <div class="notifications-section">
                    <h2>Digital Teaching Strategies</h2>
                    <h2>for 21st Century Learners</h2>

                <hr><br>
                <div class="event-target">
                    <p>Target Participants: sample</p>
                    <p>Event Mode: Face-to-face</p>
                    <br>
                <div class="event-date">
                    <p>Date &amp; Time: March 15, 2025 at 9:00 AM
                    <p>Location: DepEd Division of General Trias City - Conference Hall

            <br><br>
            <div class="notification">
                <div class="event-desc">
                    <p>Description: A training program designed to equip educators with innovative teaching strategies using digital tools and technology in the classroom.</p>
                </div>
                </div>
            <hr>
            <br><br>
            <div class="reg-text">
            <center><a class="join-btn" href="register.php">REGISTER</a></center>

            </div>
            </div>
                    

                    

                    


                    
                </div>
            </div>
    	</div>
    </div>
</div>
</body>


</html>