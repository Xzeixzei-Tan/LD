<?php
require_once 'config.php';

// Start the session
session_start();

// Display session messages if any exist
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-info">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']); // Clear the message after displaying
}

// Get the user ID from session
$user_id = $_SESSION['user_id'];

// Check if an event ID is specified in the URL
$selected_event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : null;

// Determine which tab is active (default to "Unregistered" if not specified)
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'unregistered';

// Fetch all events from the database
$sql = "SELECT e.id, e.title, e.start_date, e.end_date, e.venue, e.specification, e.delivery, e.proponent,
               (SELECT COUNT(*) FROM registered_users ru WHERE ru.event_id = e.id AND ru.user_id = ?) AS is_registered,
               CASE 
                   WHEN NOW() BETWEEN e.start_date AND e.end_date THEN 'Ongoing'
                   WHEN NOW() < e.start_date THEN 'Upcoming'
                   ELSE 'Past'
               END AS status
        FROM events e  
        ORDER BY e.start_date DESC";
$stmt = $conn->prepare($sql);

// Check if the prepare statement was successful
if ($stmt === false) {
    echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    exit();
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    // Log the error and display a user-friendly message
    error_log("Query failed in user-events.php: " . $conn->error);
    die("There was a problem loading the events. Please try again later.");
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

// Separate events into registered and unregistered
$registered_events = [];
$unregistered_events = [];

while ($row = $result->fetch_assoc()) {
    if ($row['is_registered'] > 0) {
        $registered_events[] = $row;
    } else {
        $unregistered_events[] = $row;
    }
}

// If an event is selected, fetch its details and speakers
$selected_event = null;
$speakers = [];
$is_registered = false;

if ($selected_event_id) {
    // Fetch event details
    $detail_sql = "SELECT e.*, 
                  (SELECT COUNT(*) FROM registered_users ru WHERE ru.event_id = e.id AND ru.user_id = ?) AS is_registered,
                  CASE 
                      WHEN NOW() BETWEEN e.start_date AND e.end_date THEN 'Ongoing'
                      WHEN NOW() < e.start_date THEN 'Upcoming'
                      ELSE 'Past'
                  END AS status
                  FROM events e WHERE e.id = ?";
    $stmt = $conn->prepare($detail_sql);
    $stmt->bind_param("ii", $user_id, $selected_event_id);
    $stmt->execute();
    $detail_result = $stmt->get_result();
    
    if ($detail_result && $detail_result->num_rows > 0) {
        $selected_event = $detail_result->fetch_assoc();
        $is_registered = ($selected_event['is_registered'] > 0);
        
        // Set the active tab based on the selected event's registration status
        $active_tab = $is_registered ? 'registered' : 'unregistered';
    } else {
        // Event not found, set message
        $_SESSION['message'] = "The selected event was not found.";
        header("Location: user-events.php");
        exit();
    }

    $schedule_sql = "SELECT day_date, day_number, start_time, end_time 
                     FROM event_days 
                     WHERE event_id = ? 
                     ORDER BY day_number ASC";
    $stmt = $conn->prepare($schedule_sql);
    $stmt->bind_param("i", $selected_event_id);
    $stmt->execute();
    $schedule_result = $stmt->get_result();
    
    $event_schedule = [];
    if ($schedule_result) {
        while ($day = $schedule_result->fetch_assoc()) {
            $event_schedule[] = $day;
        }
    }
    $stmt->close();
    
    // Fetch speakers for the selected event
    $speakers_sql = "SELECT name FROM speakers WHERE event_id = ?";
    $stmt = $conn->prepare($speakers_sql);
    $stmt->bind_param("i", $selected_event_id);
    $stmt->execute();
    $speakers_result = $stmt->get_result();
    
    if ($speakers_result) {
        while ($speaker = $speakers_result->fetch_assoc()) {
            $speakers[] = $speaker['name'];
        }
    }
    $stmt->close();
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
    background: #f8f9fa;
    height: 100%;
}

/* Sidebar styles - unchanged */
.sidebar {
    position: fixed;
    width: 250px;
    height: 100vh;
    background-color: #12753E;
    color: #ffffff;
    padding: 2rem 1rem;
    display: flex;
    flex-direction: column;
    justify-content: flex-start; /* Changed from space-between to maintain positions */
    transition: width 0.3s ease;
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

/* Keep the menu at the same position */
.sidebar .menu {
    margin-top: 50%;
    display: flex;
    flex-direction: column;
    flex-grow: 1; /* Allow it to grow but maintain position */
}

/* Adjust menu items when sidebar is collapsed */
.sidebar.collapsed .menu {
    align-items: center;
    margin-top: 50%; /* Keep the same top margin */
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
    font-family: Tilt Warp Regular;
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
}

.sidebar.collapsed .menu a i {
    margin-right: 0;
    font-size: 1.2rem;
}

/* Fix user profile section for collapsed sidebar */
.user-profile {
    padding: 15px;
    border-top: 1px solid white;
    display: flex;
    align-items: center;
    position: absolute; /* Changed from sticky to absolute for more control */
    bottom: 0;
    left: 0;
    right: 0;
    background-color: #12753E;
    cursor: pointer;
}

.sidebar.collapsed .user-profile {
    justify-content: center;
    padding: 15px 0;
}

.sidebar.collapsed .username {
    display: none;
}

.user-avatar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid white;
    padding: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
    transition: opacity 0.3s ease;
}
.username {
        
    font-family: Tilt Warp;
}

.sidebar.collapsed .user-avatar img {
    margin-right: 0;
    cursor: default;
}

/* Update logout menu position for collapsed sidebar */
.logout-menu {
    position: absolute;
    top: 0;
    bottom: 100%;
    border-radius: 5px;
    padding: 10px;
    display: none;
    z-index: 10000;
    width: 85px;
}

.sidebar.collapsed .logout-menu {
    left: -50px;
}

.logout-menu.active {
    display: block;
}

.logout-btn {
        background-color: white; 
        border: 2px solid #12753E;  
        display: block;
        width: 100%;
        padding: 8px 10px;
        color: #12753E;
        border-radius: 4px;
        font-family: 'Tilt Warp', sans-serif;
        font-size: 14px;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s ease;
        position: absolute;
        top: 80%;
        left: 250%;
        z-index: 10005 !important; /* Increase this value significantly */
        box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Optional: add shadow for better visibility */
}



/* Content adjustments */
.content {
    flex: 1;
    background-color: #ffffff;
    padding: 4rem;
    margin-left: 17%;
    transition: margin-left 0.3s ease;
}

.content.expanded {
    margin-left: 90px;
}

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .sidebar {
            width: 70px;
        }

        .sidebar-header h2, .menu-text, .username{
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

/* Header styles - unchanged */
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

/* Content body - improved */
.content-body h1 {
    font-family: Montserrat ExtraBold;
    font-size: 2.2rem;
    padding: 10px;
    color: black;
    letter-spacing: 0.5px;
}

.content-body hr {
    border: 1px solid #95A613;
    margin-bottom: 30px;
}

/* Tabs redesign */
.tabs {
    display: flex;
    border-bottom: 2px solid #e0e0e0;
    margin-bottom: 25px;
    gap: 10px;
}

.tab {
    padding: 12px 25px;
    background-color: #f5f5f5;
    border: none;
    border-radius: 8px 8px 0 0;
    cursor: pointer;
    font-family: Montserrat;
    font-weight: 600;
    font-size: 15px;
    color: #555;
    transition: all 0.3s ease;
    box-shadow: 0 -2px 5px rgba(0,0,0,0.05);
}

.tab:hover {
    background-color: #e8f5ef;
}

.tab.active {
    background-color: #12753E;
    color: white;
    font-weight: bold;
    box-shadow: 0 -2px 8px rgba(18,117,62,0.2);
}

.badge {
    background-color: #95A613;
    color: white;
    border-radius: 50%;
    padding: 3px 8px;
    font-size: 0.8em;
    font-weight: bold;
    font-family: Montserrat;
    margin-left: 8px;
    box-shadow: 0 2px 3px rgba(0,0,0,0.1);
}

/* Tab content */
.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* Events section redesign */
.events-section {
    background-color: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    font-family: 'Wesley Demo', serif;
    flex: 1;
    min-width: 30%;
    max-height: fit-content;
    border: 0;
    margin-top: 20px;
    transition: all 0.3s ease;
}

.events-section {
    flex-basis: 100%;
    transition: flex-basis 0.3s, transform 0.3s;
}

.events-section.shrink {
    flex-basis: 65%;
}

.events-section h2 {
    font-size: 24px;
    font-family: Montserrat ExtraBold;
    font-weight: bold;
    margin-bottom: 25px;
    color: #333;
    border-left: 4px solid #12753E;
    padding-left: 15px;
}

/* Event items redesign */
.event {
    background-color:rgb(245, 245, 245);
    border-radius: 10px;
    padding: 22px;
    margin-bottom: 18px;
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 3px 8px rgba(0,0,0,0.05);
    border-left: 4px solid transparent;
}

.event-dates {
    font-size: 13px;
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

.events-section {
    background-color: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    font-family: 'Wesley Demo', serif;
    flex: 1;
    min-width: 30%;
    max-height: 800px;
    border: 0;
    margin-top: 20px;   
    transition: all 0.3s ease;
    overflow: auto;
}

.events-section {
    flex-basis: 100%;
    transition: flex-basis 0.3s, transform 0.3s;
}

.event.selected {
    background:rgb(218, 238, 227);
    border-left: 5px solid #12753E;
    transform: translateX(5px);
}

.event.selected h3 {
    color: #12753E;
}

.event.selected p {
    color: #445;
}

.event:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(43, 58, 143, 0.1);
    border-left: 5px solid #12753E;
}

.events-btn {
    text-decoration: none;
    color: inherit;
    display: block;
}

.event-content h3 {
    font-size: 19px;
    margin-bottom: 10px;
    font-family: Montserrat ExtraBold;
    color: #12753E;
    transition: color 0.3s ease;
}

.event-content p {
    font-size: 14px;
    color: #585858;
    font-family: Montserrat Medium;
    line-height: 1.5;
}

.event-content p strong {
    font-weight: bold;
    font-family: Montserrat;
    color: #444;
}

.status-badge {
    color: white;
    font-family: Montserrat Medium;
    font-size: 12px;
    padding: 6px 15px;
    border-radius: 20px;
    position: absolute;
    top: 20px;
    right: 20px;
    box-shadow: 0 3px 6px rgba(0,0,0,0.15);
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    transition: transform 0.2s ease;
}

.status-badge i {
    margin-right: 5px;
}

.status-badge:hover {
    transform: translateY(-2px);
}

/* Status-specific badges */
.status-upcoming {
    background: linear-gradient(135deg, #3498db, #2980b9);
    animation: pulse-blue 2s infinite;
}

.status-ongoing {
    background: #12753E;
    animation: pulse-red 1.5s infinite;
}

.status-past {
    background: linear-gradient(135deg, #7f8c8d, #596a6b);
}

@keyframes pulse-blue {
    0% {
        box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(52, 152, 219, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(52, 152, 219, 0);
    }
}

@keyframes pulse-red {
    0% {
        box-shadow: 0 0 0 0 rgba(18, 117, 62, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(231, 76, 60, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(231, 76, 60, 0);
    }
}

.search-container {
  position: relative;
  flex-grow: 1;
  max-width: fit-content;
}

/* Search Input */
.search-input {
  width: 100%;
  height: 42px;
  padding: 0 45px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 14px;
  font-family: 'Montserrat', sans-serif;
  color: #2d3748;
  background-color: white;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.search-input:focus {
  outline: none;
  border-color: #2b3a8f;
  box-shadow: 0 0 0 3px rgba(43, 58, 143, 0.1);
}

.search-input::placeholder {
  color: #a0aec0;
  font-weight: 400;
}

/* Search Icon */
.search-icon {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #a0aec0;
  font-size: 14px;
}

/* Add clear button for search */
.search-container::after {
  content: "\f00d";
  font-family: "Font Awesome 5 Free";
  font-weight: 900;
  position: absolute;
  right: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #cbd5e0;
  font-size: 12px;
  cursor: pointer;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.search-container:has(.search-input:not(:placeholder-shown))::after {
  opacity: 1;
}

/* Content area layout */
.content-area { 
    display: flex; 
    justify-content: space-between; 
}

/* Details section redesign */
.details-section, #details-section {
    display: none;
    flex-basis: 30%;
    max-height: fit-content;
    margin-left: 20px;
    margin-top: 20px;
    background-color: white;
    padding: 25px;
    border-radius: 12px;
    border: 0;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.5s ease;
}

#detail-title {
    font-size: 24px;
    font-family: Montserrat Extrabold;
    margin-bottom: 15px;
    color: #12753E;
    padding-bottom: 10px;
}

.details-section h2 { 
    margin-top: 0;
    font-family: Montserrat Extrabold;
    font-weight: bold;
    margin-bottom: 2%;
    font-size: 22px;
    color: #333;
}

.details-section hr {
    border: 1px solid #f0f0f0;
    margin-bottom: 20px;
}

.detail-items {
    display: flex;
    flex-wrap: wrap;
}

.detail-items-1 {
    margin-top: 2%;
    margin-right: 18%;
}

.detail-items-2 {
    margin-top: 2%;
}

.details-section .detail-item {
    margin-bottom: 20px;
}

.detail-item{
    width: 360px;
    margin-bottom: 20px;
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 16px;
    transition: transform 0.2s;
}

.details-section .detail-item h4 {
    color: #555;
    font-size: 16px;
    font-family: Montserrat ;
    margin-top: 0;
    margin-bottom: 8px;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.details-section .detail-item p {
    margin: 5px 0;
    color: #12753E;
    font-weight: Bold;
    font-family: Montserrat Medium;
    font-size: 16px;
}

.expand-btn {
    cursor: pointer;
    float: right;
    transition: transform 0.3s ease;
    background-color: #f2f9f6;
    margin-top: -1%;
    padding: 8px 10px;
    border-radius: 50%;
    color: #12753E;
}

.expand-btn:hover {
    background-color: #12753E;
    color: white;
}

/* Expanded content */
.expanded-content {
    display: none;
}

.details-section.expand .expanded-content {
    display: block;
}

.details-section.expand .expand-btn {
    transform: rotate(180deg);
}

/* Register/Unregister button */
.create-btn {
    float: right;
    padding: 12px 22px;
    font-family: Montserrat;
    font-weight: bold;
    font-size: 14px;
    color: white;
    text-decoration: none;
    background-color:rgb(17, 118, 62);
    border-radius: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 8px rgba(18,117,62,0.2);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.create-btn:hover {
    background-color: #0e5c31;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(18,117,62,0.3);
}

/* Expand/collapse helpers */
.expand { 
    flex-basis: 100% !important; 
}

.hidden { 
    display: none; 
}

/* Alert message styling */
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-family: Montserrat;
}

.alert-info {
    background-color: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

/* Notification styling */
.notification p {
    font-size: 14px;
    font-family: Montserrat;
}

/* Responsive adjustments */
@media (max-width: 900px) {
    .detail-items-2 {
        width: 100%;
        margin-left: 0;
    }
    
    .detail-items {
        flex-direction: column;
    }
    
    .content {
        margin-left: 80px;
        padding: 2rem;
    }
    
    .content-header h1 {
        margin-left: 20%;
    }
    
    .content-header p {
        margin-left: 30%;
    }
    
    .content-header img {
        margin-left: 10%;
    }
}

@media (max-width: 768px) {
    .content-area {
        flex-direction: column;
    }
    
    .events-section.shrink {
        flex-basis: 100%;
    }
    
    .details-section, #details-section {
        margin-left: 0;
        margin-top: 20px;
    }
}

p{
    font-family: Montserrat;
}
.user-profile {
    padding: 15px;
    border-top: 1px solid white;
    display: flex;
    align-items: center;
    position: absolute; /* Changed from sticky to absolute for more control */
    bottom: 0;
    left: 0;
    right: 0;
    background-color: #12753E;
    cursor: pointer;
}

.logout-menu {
        position: absolute;
        top: 0;
        bottom: 100%;
        border-radius: 5px;
        padding: 10px;
        display: none;
        z-index: 10000;
        width: 87px;
    }

    .logout-menu.active {
        display: block;
    }

    .logout-btn {
        background-color: white; 
        border: 2px solid #12753E;  
        display: block;
        width: 100%;
        padding: 8px 10px;
        color: #12753E;
        border-radius: 4px;
        font-family: 'Tilt Warp', sans-serif;
        font-size: 14px;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s ease;
        position: absolute;
        top: 80%;
        left: 260%;
        z-index: 10005 !important; /* Increase this value significantly */
        box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Optional: add shadow for better visibility */
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
</style>
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
        <a href="user-dashboard.php" >
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="user-events.php" class="active">
            <i class="fas fa-calendar-alt"></i>
            <span>Events</span>
        </a>
        <a href="user-notif.php">
            <i class="fas fa-bell mr-3"></i>
            <span>Notification</span>
        </a>
        </div>
 <!-- Modified user profile with logout menu -->
        <div class="user-profile" id="userProfileToggle">
            <div class="user-avatar"><img src="styles/photos/me.jpg"></div>
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
        </div><br><br><br>

        <div class="content-body">
            <h1>Events</h1>
            <hr><br>

            <div class="search-container">
                <span class="search-icon"><i class="fa fa-search" aria-hidden="true"></i></span>
                <input type="text" class="search-input" placeholder="Search for events...">
            </div>

            <div class="content-area">
                <div class="events-section <?php echo $selected_event ? 'shrink' : ''; ?>">
                    <div class="tabs">
                        <button class="tab <?php echo $active_tab == 'registered' ? 'active' : ''; ?>" onclick="switchTab('registered')">
                            Registered <span class="badge"><?php echo count($registered_events); ?></span>
                        </button>
                        <button class="tab <?php echo $active_tab == 'unregistered' ? 'active' : ''; ?>" onclick="switchTab('unregistered')">
                            Unregistered <span class="badge"><?php echo count($unregistered_events); ?></span>
                        </button>
                    </div>

                    <!-- Registered Events Tab -->
                    <div id="registered-tab" class="tab-content <?php echo $active_tab == 'registered' ? 'active' : ''; ?>">
                        <h2>Registered Events</h2>
                        <?php
                        if (count($registered_events) > 0) {
                            foreach ($registered_events as $row) {
                                $isSelected = ($selected_event_id == $row['id']) ? 'selected' : '';
                                echo '<div class="event ' . $isSelected . '">';
                                echo '<a class="events-btn" href="user-events.php?event_id=' . urlencode($row['id']) . '&tab=registered">';
                                echo '<div class="event-content">';
                                echo '<h3>' . htmlspecialchars($row["title"]) . '</h3>';
                                echo '<p>'. '<strong>Event Specification: '. '</strong>' . htmlspecialchars($row["specification"]) . '</p>';
                                echo '<div class="event-dates">'.'<p>' . '<strong><i class="fas fa-calendar-day"></i>Date: '. '</strong>' . date('M d, Y', strtotime($row["start_date"])) . '</p>'. '</div>';
                                echo '<span class="status-badge status-' . strtolower($row["status"]) . '">';
                                if(strtolower($row["status"]) == "upcoming") {
                                    echo '<i class="fas fa-hourglass-start"></i> ';
                                } else if(strtolower($row["status"]) == "ongoing") {
                                    echo '<i class="fas fa-circle"></i>';
                                } else {
                                    echo '<i class="fas fa-check-circle"></i> ';
                                }
                                echo htmlspecialchars($row["status"]) . '</span>';
                                echo '</div></a>';
                                echo '</div>';
                            }
                        } else {
                            echo "<p>You haven't registered for any events yet.</p>";
                        }
                        ?>
                    </div>

                    <!-- Unregistered Events Tab -->
                    <div id="unregistered-tab" class="tab-content <?php echo $active_tab == 'unregistered' ? 'active' : ''; ?>">
                        <h2>Available Events</h2>
                        <?php
                        if (count($unregistered_events) > 0) {
                            foreach ($unregistered_events as $row) {
                                $isSelected = ($selected_event_id == $row['id']) ? 'selected' : '';
                                echo '<div class="event ' . $isSelected . '">';
                                echo '<a class="events-btn" href="user-events.php?event_id=' . urlencode($row['id']) . '&tab=unregistered">';
                                echo '<div class="event-content">';
                                echo '<h3>' . htmlspecialchars($row["title"]) . '</h3>';
                                echo '<p>' . '<strong>Event Specification: '. '</strong>' . htmlspecialchars($row["specification"]) . '</p>';
                                echo '<div class="event-dates">'.'<p>' . '<strong><i class="fas fa-calendar-day"></i>Date: '. '</strong>' . date('M d, Y', strtotime($row["start_date"])) . '</p>'. '</div>';
                                echo '<span class="status-badge status-' . strtolower($row["status"]) . '">';
                                if(strtolower($row["status"]) == "upcoming") {
                                    echo '<i class="fas fa-hourglass-start"></i> ';
                                } else if(strtolower($row["status"]) == "ongoing") {
                                    echo '<i class="fas fa-circle"></i> ';
                                } else {
                                    echo '<i class="fas fa-check-circle"></i> ';
                                }
                                echo htmlspecialchars($row["status"]) . '</span>';
                                echo '</div></a>';
                                echo '</div>';
                            }
                        } else {
                            echo "<p>No available events found.</p>";
                        }
                        ?>
                    </div>
                </div>  

                <div class="details-section" id="details-section" <?php echo $selected_event ? 'style="display: block;"' : ''; ?>>
                    <i class="fas fa-expand expand-btn" onclick="toggleExpand()"></i>
                    <h2>Details</h2>
                    <hr>
                    <h3 id="detail-title"><?php echo htmlspecialchars($selected_event["title"]); ?></h3>
                    <div class="detail-items">
                        <div class="detail-items-1"> 
                            <?php if ($selected_event): ?>
                            <div class="detail-item">
                                <h4>Delivery:</h4>
                                <p id="detail-mode"><?php echo htmlspecialchars($selected_event["delivery"]); ?></p>
                            </div>

                            <div class="detail-item">
                                <h4>Venue:</h4>
                                <p id="detail-venue"><?php echo htmlspecialchars($selected_event["venue"] ?? "Not specified"); ?></p>
                            </div>

                            <div class="detail-item">
                                <h4>Event Specification:</h4>
                                <p id="detail-specification"><?php echo htmlspecialchars($selected_event["specification"]); ?></p>
                            </div>

                            <div class="detail-item">
                                <h4>Organizer:</h4>
                                <p id="detail-organizer"><?php echo htmlspecialchars($selected_event["proponent"] ?? "Not specified"); ?></p>
                            </div>

                            <div class="detail-item">
                                <h4>Speaker(s):</h4>
                                <p id="detail-speakers">
                                <?php 
                                if (!empty($speakers)) {
                                    echo htmlspecialchars(implode(", ", $speakers));
                                } else {
                                    echo "Not specified";
                                }
                                ?>
                                </p>
                            </div>
                        </div>
                        <div class="detail-items-2">
                            
                            <div class="detail-item expanded-content">
                                <h4>Start:</h4>
                                <p id="detail-start"><?php echo htmlspecialchars($selected_event["start_date"]); ?></p>
                            </div>

                            <div class="detail-item expanded-content">
                                <h4>End:</h4>
                                <p id="detail-end"><?php echo htmlspecialchars($selected_event["end_date"]); ?></p>
                            </div>

                            <div class="detail-item expanded-content">
                                <h4>Event Schedule:</h4>
                                <div id="detail-event-days">
                                    <?php 
                                    if (!empty($event_schedule)) {
                                        foreach ($event_schedule as $day) {
                                            $formatted_date = date('F j, Y', strtotime($day['day_date']));
                                            $start_time = date('g:i A', strtotime($day['start_time']));
                                            $end_time = date('g:i A', strtotime($day['end_time']));
                                            
                                            echo '<p><strong>Day ' . htmlspecialchars($day['day_number']) . '</strong>: ' . 
                                                htmlspecialchars($formatted_date) . ', ' . 
                                                htmlspecialchars($start_time) . ' - ' . 
                                                htmlspecialchars($end_time) . '</p>';
                                        }
                                    } else {
                                        echo "<p>No schedule information available</p>";
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="detail-item expanded-content">
                                <h4>Status:</h4>
                                <p id="detail-status"><?php echo htmlspecialchars($selected_event["status"]); ?></p>
                            </div>

                        </div>
                    </div>
                    <br>
                    <?php if ($selected_event): ?>
                        <?php if (!$is_registered): ?>
                            <?php if ($selected_event["status"] === "Past" || $selected_event["status"] === "Ongoing"): ?>        
                                <a class="create-btn" style="visibility: hidden;">Register</a>
                            <?php else: ?>
                                <a class="create-btn" href="register.php?event_id=<?php echo urlencode($selected_event['id']); ?>">Register</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a class="create-btn" href="unregister.php?event_id=<?php echo urlencode($selected_event['id']); ?>" style="background-color:rgb(117, 130, 14); border-style: none; cursor: pointer;" onclick="return confirm('Are you sure you want to unregister from this event?');">Unregister</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="detail-item">
                            <p>Select an event to view details</p>
                        </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Function to switch tabs
function switchTab(tabName) {
    // Hide all tab contents
    var tabContents = document.getElementsByClassName('tab-content');
    for (var i = 0; i < tabContents.length; i++) {
        tabContents[i].classList.remove('active');
    }
    
    // Remove active class from all tabs
    var tabs = document.getElementsByClassName('tab');
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove('active');
    }
    
    // Show the selected tab content and mark tab as active
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Explicitly activate the clicked tab button
    document.querySelector('.tab:nth-child(' + (tabName === 'registered' ? '1' : '2') + ')').classList.add('active');
    
    // Update URL with the active tab parameter
    if (window.location.href.includes('event_id=')) {
        // If there's an event ID in the URL, preserve it
        var eventId = new URLSearchParams(window.location.search).get('event_id');
        window.history.replaceState(null, null, `?event_id=${eventId}&tab=${tabName}`);
    } else {
        window.history.replaceState(null, null, `?tab=${tabName}`);
    }
}

// Function to show alert for past or ongoing events
function showStatusAlert(status) {
    alert("You can't register to '" + status + "' events.");
}

function toggleExpand() {
    let detailsSection = document.getElementById('details-section');
    let eventsSection = document.querySelector('.events-section');
    let expandIcon = document.querySelector('.expand-btn');
    let expandedContent = document.querySelectorAll('.expanded-content');

    if (detailsSection.classList.contains('expand')) {
        // Collapse
        detailsSection.classList.remove('expand');
        eventsSection.classList.remove('hidden');
        expandIcon.classList.replace('fa-compress', 'fa-expand');
    } else {
        // Expand
        detailsSection.classList.add('expand');
        eventsSection.classList.add('hidden');
        expandIcon.classList.replace('fa-expand', 'fa-compress');
    }
}

// Document ready function
document.addEventListener('DOMContentLoaded', function() {
    // User profile toggle and logout menu
    const userProfileToggle = document.getElementById('userProfileToggle');
    const logoutMenu = document.getElementById('logoutMenu');
    const sidebar = document.getElementById('sidebar');
    const content = document.querySelector('.content');
    const toggleBtn = document.getElementById('toggleSidebar');
    const userAvatar = document.querySelector('.user-avatar');

    // Check if sidebar state is saved in localStorage
    const isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    
    // Set initial state based on localStorage
    if (isSidebarCollapsed) {
        sidebar.classList.add('collapsed');
        content.classList.add('expanded');
        userAvatar.style.cursor = 'default'; // Make avatar non-clickable
        userProfileToggle.style.pointerEvents = 'none'; // Disable click events
    } else {
        userAvatar.style.cursor = 'pointer'; // Make avatar clickable
        userProfileToggle.style.pointerEvents = 'auto'; // Enable click events
    }

    // Toggle sidebar when button is clicked
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('expanded');

            // Update avatar clickability based on sidebar state
            if (sidebar.classList.contains('collapsed')) {
                userAvatar.style.cursor = 'default';
                userProfileToggle.style.pointerEvents = 'none';
            } else {
                userAvatar.style.cursor = 'pointer';
                userProfileToggle.style.pointerEvents = 'auto';
            }
            
            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    }

    // Toggle logout menu when user profile is clicked
    if (userProfileToggle) {
        userProfileToggle.addEventListener('click', function(event) {
            // Only toggle logout menu if sidebar is not collapsed
            if (!sidebar.classList.contains('collapsed')) {
                event.stopPropagation();
                logoutMenu.classList.toggle('active');
            }
        });
    }

    // Close the logout menu when clicking outside
    document.addEventListener('click', function(event) {
        if (logoutMenu && userProfileToggle && !userProfileToggle.contains(event.target)) {
            logoutMenu.classList.remove('active');
        }
    });

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
    
    // Check if there's a selected event
    const selectedEvent = document.querySelector('.event.selected');
    
    // If a selected event exists, scroll to it
    if (selectedEvent) {
        // Smooth scroll to the element
        selectedEvent.scrollIntoView({
            behavior: 'smooth',
            block: 'center' // Centers the element in the viewport
        });
        
        // Optional: Add a brief highlight effect
        setTimeout(function() {
            selectedEvent.style.transition = 'background-color 0.5s';
            const originalBackground = selectedEvent.style.backgroundColor;
            selectedEvent.style.backgroundColor = '#20cd6d'; // Flash with a different color
            
            setTimeout(function() {
                selectedEvent.style.backgroundColor = originalBackground;
            }, 700);
        }, 300);
    }
    
    // Get the search input element
    const searchInput = document.querySelector('.search-input');
    
    // Add event listener for input changes
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            // Get all event elements from both tabs
            const registeredEvents = document.querySelectorAll('#registered-tab .event');
            const unregisteredEvents = document.querySelectorAll('#unregistered-tab .event');
            
            // Search function for events
            function filterEvents(events) {
                let visibleCount = 0;
                
                events.forEach(event => {
                    // Get the event title and other searchable content
                    const title = event.querySelector('h3').textContent.toLowerCase();
                    const specification = event.querySelector('p').textContent.toLowerCase();
                    const date = event.querySelector('.event-dates') ? 
                        event.querySelector('.event-dates').textContent.toLowerCase() : 
                        event.querySelectorAll('p')[1].textContent.toLowerCase();
                    const status = event.querySelector('.status-badge').textContent.toLowerCase();
                    
                    // Combine all searchable content
                    const searchableContent = `${title} ${specification} ${date} ${status}`;
                    
                    // Check if the search term exists in any of the content
                    if (searchableContent.includes(searchTerm)) {
                        event.style.display = 'block';
                        visibleCount++;
                    } else {
                        event.style.display = 'none';
                    }
                });
                
                return visibleCount;
            }
            
            // Apply filter to both tabs
            const registeredCount = filterEvents(registeredEvents);
            const unregisteredCount = filterEvents(unregisteredEvents);
            
            // Update the badge counts
            updateBadgeCount('registered', registeredCount);
            updateBadgeCount('unregistered', unregisteredCount);
            
            // Show "No results found" message if needed
            displayNoResultsMessage('registered-tab', registeredCount);
            displayNoResultsMessage('unregistered-tab', unregisteredCount);
        });
        
        // Add clear button functionality
        searchInput.addEventListener('keyup', function(e) {
            // Check if Escape key was pressed or input is empty
            if (e.key === 'Escape' || this.value === '') {
                this.value = '';
                // Trigger the input event to reset the search
                this.dispatchEvent(new Event('input'));
            }
        });
    }
    
    // Function to update badge count
    function updateBadgeCount(tabName, count) {
        const badge = document.querySelector(`.tab:nth-child(${tabName === 'registered' ? '1' : '2'}) .badge`);
        if (badge) {
            badge.textContent = count;
        }
    }
    
    // Function to display "No results found" message
    function displayNoResultsMessage(tabId, count) {
        const tabContent = document.getElementById(tabId);
        
        // Remove existing no-results message if it exists
        const existingMessage = tabContent.querySelector('.no-results-message');
        if (existingMessage) {
            existingMessage.remove();
        }
        
        // Add no-results message if no events were found
        if (count === 0) {
            const noResultsMessage = document.createElement('p');
            noResultsMessage.className = 'no-results-message';
            noResultsMessage.textContent = 'No events found matching your search criteria.';
            noResultsMessage.style.textAlign = 'center';
            noResultsMessage.style.padding = '20px';
            noResultsMessage.style.color = '#666';
            noResultsMessage.style.fontFamily = 'Montserrat, sans-serif';
            
            // Insert after the heading
            const heading = tabContent.querySelector('h2');
            heading.insertAdjacentElement('afterend', noResultsMessage);
        }
    }
    
    // When clicking the X (clear) button
    const searchContainer = document.querySelector('.search-container');
    if (searchContainer) {
        searchContainer.addEventListener('click', function(e) {
            // Check if the click was on the after pseudo-element (approximated by position)
            const rect = searchContainer.getBoundingClientRect();
            
            // If click is in the right 30px of the container (where the X appears)
            if (searchInput && e.clientX > rect.right - 30 && searchInput.value !== '') {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
                searchInput.focus();
            }
        });
    }
});

</script>

</body>
</html>

<?php
$conn->close();
?>