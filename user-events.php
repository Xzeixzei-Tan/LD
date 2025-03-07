<?php
require_once 'config.php';

$sql = "SELECT id, title, description, event_mode, start_datetime, end_datetime, venue FROM events ORDER BY start_datetime DESC";
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
    <title>USER-events</title>
    <style> 
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body,
html {
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
    color: white;

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
    margin-top: -7%;
}

.content-area {
    display: flex;
    justify-content: space-between;
}

.events-section, {
    background-color: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    font-family: 'Wesley Demo', serif;
    flex: 1;
    min-width: 300px;
}

.events-section {
    flex-basis: 100%;
    transition: flex-basis 0.3s;
}

.event.selected{
    background: #12753E;
}

.event.selected h3{
    color: white;
}

.event.selected p{
    color: rgb(231, 231, 231);
}

#details-section {
    display: none;
    flex-basis: 30%;
    margin-left: 20px;
    background-color: white;
    padding: 30px;
    border-radius: 8px;
    border: 2px solid #12753E;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.events-section.shrink {
    flex-basis: 70%;
}

.details-section h2 {
    margin-top: 0;
    font-family: Montserrat Extrabold;
    font-weight: bold;
    margin-bottom: 10%;
}

#detail-title{
    font-family: Montserrat Extrabold;
    color: #12753E;
}

.details-section .detail-item {
    margin-bottom: 15px;
}

.details-section .detail-item h3 {
    margin: 0;
    font-size: 1em;
    font-family: Montserrat;
    color: #12753E;
}

.details-section .detail-item p {
    margin: 5px 0 0;
    color: #000000;
    font-size: .8em;
    font-family: Montserrat Medium
}

.events-section h2 {
    font-size: 22px;
    font-family: Montserrat ExtraBold;
    font-weight: bold;
    margin-bottom: 20px;
    color: #333;
}

.event {
    background-color: #d7f3e4;
    border-radius: 5px;
    padding: 20px;
    margin-bottom: 15px;
    position: relative;
}

.event-content h3 {
    font-size: 18px;
    margin-bottom: 5px;
    font-family: Montserrat;
    color: #12753E;
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
}

.content-area { 
    display: flex; 
    justify-content: space-between; 
}
.details-section { 
    display: none; 
    flex-basis: 30%; 
    margin-left: 20px; 
    background-color: #f9f9f9; 
    padding: 20px; 
    border-radius: 8px; 
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); 
}
.events-section { 
    flex-basis: 100%; 
    transition: flex-basis 0.3s; 
}
.events-section.shrink { 
    flex-basis: 70%; 
}
.details-section h2 { 
    margin-top: 0; 
}
.details-section .detail-item { 
    margin-bottom: 15px; 
}
.details-section .detail-item h3 { 
    margin: 0; font-size: 1.2em; 
}
.details-section .detail-item p { 
    margin: 5px 0 0; color: #555; 
}
.expand-btn { 
    cursor: pointer; float: right; 
}
.expand { 
    flex-basis: 100% !important; 
}
.hidden { 
    display: none; 
}
    </style>
</head>
<body>
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
            <div class="user-avatar"><img src="styles/photos/jess.jpg"></div>
            <div class="username">Jess Constante</div>
        </div>
    </div>

    <div class="content">
        <div class="content-header">
            <img src="styles/photos/DO-LOGO.png" width="70px" height="70px">
            <p>Learning and Development</p>
            <h1>EVENT MANAGEMENT SYSTEM</h1>
        </div><br><br><br>

        <div class="content-body">
            <h1>Events</h1>
            <hr><br>

            <div class="content-area">
                <div class="events-section">
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<a class="events-btn" href="javascript:void(0);" onclick="showDetails(' . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . ')">';
                            echo '<div class="event">';
                            echo '<div class="event-content">';
                            echo '<h3>' . htmlspecialchars($row["title"]) . '</h3>';
                            echo '<p>' . htmlspecialchars(substr($row["description"], 0, 100)) . '...</p>';
                            echo '</div></a>';
                            echo '</div>';
                        }
                    } else {
                        echo "<p>No events found.</p>";
                    }
                    ?>
                </div>

                <div class="details-section" id="details-section">
                    <i class="fas fa-expand expand-btn" onclick="toggleExpand()"></i>
                    <h2>Details</h2>
                    <div class="detail-item">
                        <h3 id="detail-title"></h3>
                        <p id="detail-description"></p>
                    </div>
                    <div class="detail-item">
                        <h3>Mode:</h3>
                        <p id="detail-mode"></p>
                    </div>
                    <div class="detail-item">
                        <h3>Start:</h3>
                        <p id="detail-start"></p>
                    </div>
                    <div class="detail-item">
                        <h3>End:</h3>
                        <p id="detail-end"></p>
                    </div>
                    <div class="detail-item">
                        <h3>Venue:</h3>
                        <p id="detail-venue"></p>
                    </div><br>
                                <a class="create-btn" href="Register.php">Register</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showDetails(eventData) {
    document.getElementById('detail-title').textContent = eventData.title;
    document.getElementById('detail-description').textContent = eventData.description;
    document.getElementById('detail-mode').textContent = eventData.event_mode;
    document.getElementById('detail-start').textContent = eventData.start_datetime;
    document.getElementById('detail-end').textContent = eventData.end_datetime;
    document.getElementById('detail-venue').textContent = eventData.venue || "Not specified";

    document.getElementById('details-section').style.display = 'block';
    document.querySelector('.events-section').classList.add('shrink');
}

function selectEvent(event) {
    document.querySelectorAll('.event').forEach(div => {
        div.classList.remove('selected');
    });

    event.currentTarget.classList.add('selected');
}

function toggleExpand() {
    let detailsSection = document.getElementById('details-section');
    let eventsSection = document.querySelector('.events-section');
    let expandIcon = document.querySelector('.expand-btn');

    if (detailsSection.classList.contains('expand')) {
        detailsSection.classList.remove('expand');
        eventsSection.classList.remove('hidden');
        expandIcon.classList.replace('fa-compress', 'fa-expand');
    } else {
        detailsSection.classList.add('expand');
        eventsSection.classList.add('hidden');
        expandIcon.classList.replace('fa-expand', 'fa-compress');
    }
}

// Modify the PHP to add the onclick event
document.addEventListener('DOMContentLoaded', () => {
    const eventDivs = document.querySelectorAll('.event');
    eventDivs.forEach(div => {
        div.addEventListener('click', selectEvent);
    });
});
</script>

</body>
</html>

<?php
$conn->close();
?>
