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
        }
        
        .notification-content {
            padding: 20px;
            flex: 1;
        }
        
        .notification-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .notification-instruction {
            color: #666;
            font-style: italic;
            margin-top: 15px;
        }
        
        .pdf-preview {
            background-color: #f5f5f5;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 220px;
        }
        
        .pdf-icon {
            width: 60px;
            height: 80px;
            background-color: #c00;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            position: relative;
        }
        
        .pdf-icon:after {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            border-style: solid;
            border-width: 0 15px 15px 0;
            border-color: transparent white transparent transparent;
        }
        
        .pdf-icon span {
            color: white;
            font-weight: bold;
            font-size: 16px;
        }
        
        .pdf-filename {
            font-size: 14px;
            text-align: center;
        }
</style>
<body>

	<div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            
            <div class="menu">
                <a href="User-Dashboard.php" ><i class="fas fa-home mr-3"></i>Home</a>
                <a href="User-Event.php"><i class="fas fa-calendar-alt mr-3"></i>Events</a>
                <a href="User-Notification.php" class="active"><i class="fas fa-bell mr-3"></i>Notification</a>
                <br><br><br><br><br><br><br><br><br><br><br><br><br>
                <a href="User-Profile.php"><i class="fas fa-user-circle mr-3"></i>Profile</i></a>
            </div>
        </div>

    <div class="content">
    	<div class="content-header">
	    	<img src="DO-LOGO.png" width="70px" height="70px">
	    	<p>Learning and Development</p>
	    	<h1>EVENT MANAGEMENT SYSTEM</h1>
    	</div><br><br><br><br><br>

        <div class="content-body">
        <h1>Notifications</h1>
        <hr><br>

        <div class="notification-card">
        <div class="notification-content">
            <div class="notification-title">Your certificate from "Sample Event" is now available to download.</div>
            <div class="notification-instruction">Click the PDF file to download.</div>
        </div>
        
        <div class="pdf-preview">
            <div class="pdf-icon">
                <span>PDF</span>
            </div>
            <a href="Sample.pdf"><div class="pdf-filename">Sample Event Certificate.pdf</div>
        </div>
    </div>
    </div>
</div>
</body>
</html>