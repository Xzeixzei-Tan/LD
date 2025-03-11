<?php
require_once 'config.php';

$sql = "SELECT id, title, start_datetime, end_datetime, venue FROM events ORDER BY start_datetime DESC";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
	<title>admin-notif</title>
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
        background-color: #2b3a8f;
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
        color: #2b3a8f;
    }

    .sidebar .menu a i {
        margin-right: 0.5rem;
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

    .content-body h1 {
        font-family: Montserrat ExtraBold;
        font-size: 2rem;
        padding: 10px;
        color: ##12753E;
    }

    .content-body hr {
        border: 1px solid #95A613;
    }

    .create-btn {
        float: right;
        padding: 11px;
        padding-left: 15px;
        padding-right: 15px;
        font-family: Montserrat;
        font-weight: bold;
        font-size: 13px;
        color: white;
        text-decoration: none;
        background-color: #12753E;
        border-radius: 5px;
        margin-top: 20px;
    }

    .content-area {
        display: flex;
        flex-direction: column;
    }

    /* Modified events section for 3-column layout */
    .events-section {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        flex: 1;
    }

    .event {
        background-color: #d7f3e4;
        border-radius: 5px;
        padding: 20px;
        position: relative;
        transition: transform 0.2s;
        height: 100%;
    }

    .event:hover {
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

    .event-content h3 {
        font-size: 18px;
        margin-bottom: 5px;
        font-family: Montserrat;
        color:rgb(14, 19, 44);
    }

    .event-content p {
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
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        width: 60%;
        max-width: 600px;
        animation: modalopen 0.4s;
        border: 2px solid #2b3a8f;
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
	</style>
</head>
<body>

<div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
        
            <div class="menu">
                <a href="admin-dashboard.php"><i class="fas fa-home"></i>Home</a>
                <a href="admin-events.php"><i class="fas fa-calendar-alt"></i>Events</a>
                <a href="admin-users.php"><i class="fas fa-users"></i>Users</a>
                <a href="admin-notif.php" class="active"><i class="fas fa-bell"></i>Notification</a> 
            </div>
        </div>

    <div class="content">
    	<div class="content-header">
	    	<img src="styles/photos/DO-LOGO.png" width="70px" height="70px">
	    	<p>Learning and Development</p>
	    	<h1>EVENT MANAGEMENT SYSTEM</h1>
    	</div><br><br><br><br><br>

    	<div class="content-body">
	    	<h1>Notification</h1>
	    	<hr><br>
            <div class="content-area">
                <div class="events-section">
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="event">';
                            echo '<a class="events-btn" href="javascript:void(0);" onclick="showModal(' . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . ')">';
                            echo '<div class="event-content">';
                            echo '<h3>' . htmlspecialchars($row["title"]) . '</h3>';
                            echo '</div></a>';
                            echo '</div>';
                        }
                    } else {
                        echo "<p>No events found.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div id="eventModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <div class="modal-header">
            <h2 id="modal-title"></h2>
        </div>
        <div class="modal-body">
            <div class="detail-item">
                <h3>Total Participants:</h3>
                <p id="modal-description"></p>
            </div>
            <div class="detail-item">
                <h3>Delivery:</h3>
                <p id="modal-mode"></p>
            </div>
            <div class="detail-item">
                <h3>Start:</h3>
                <p id="modal-start"></p>
            </div>
            <div class="detail-item">
                <h3>End:</h3>
                <p id="modal-end"></p>
            </div>
            <div class="detail-item">
                <h3>Venue:</h3>
                <p id="modal-venue"></p>
            </div>
        </div>
        <div class="modal-footer">
            
        </div>
    </div>
</div>

<script>
// Get the modal
var modal = document.getElementById("eventModal");

// Function to show the modal with event details
function showModal(eventData) {
    document.getElementById('modal-title').textContent = eventData.title;
    document.getElementById('modal-description').textContent = eventData.description || "No description available";
    document.getElementById('modal-mode').textContent = eventData.event_mode || "Not specified";
    document.getElementById('modal-start').textContent = eventData.start_datetime;
    document.getElementById('modal-end').textContent = eventData.end_datetime;
    document.getElementById('modal-venue').textContent = eventData.venue || "Not specified";

    // Display the modal with flex to center it
    modal.style.display = "flex";
    
    // Add selected class to clicked event
    event.currentTarget.closest('.event').classList.add('selected');
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
</script>

</body>
</html>

<?php
$conn->close();
?>
