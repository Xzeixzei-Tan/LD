<?php
require_once 'config.php';

// Check if event ID is provided
if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    die("Error: No event ID provided");
}

$event_id = intval($_GET['event_id']);

// Get event details
$eventSql = "SELECT title FROM events WHERE id = ?";
$eventStmt = $conn->prepare($eventSql);
$eventStmt->bind_param("i", $event_id);
$eventStmt->execute();
$eventResult = $eventStmt->get_result();

if ($eventResult->num_rows === 0) {
    die("Error: Event not found");
}

$eventRow = $eventResult->fetch_assoc();
$eventTitle = $eventRow['title'];

// Get registered users for this event
$usersSql = "SELECT ru.id, ru.full_name, ru.email, ru.position, ru.office, ru.register_date 
             FROM registered_users ru 
             WHERE ru.event_id = ? 
             ORDER BY ru.register_date DESC";
             
$usersStmt = $conn->prepare($usersSql);
$usersStmt->bind_param("i", $event_id);
$usersStmt->execute();
$usersResult = $usersStmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="styles/admin-events.css" rel="stylesheet">
    <title>Event Participants - <?php echo htmlspecialchars($eventTitle); ?></title>
    <style>
        .content-area {
            padding: 20px;
        }
        
        .back-btn {
            margin-bottom: 20px;
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
        }
        
        .participant-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .participant-table th, .participant-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        
        .participant-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        .participant-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .participant-table tr:hover {
            background-color: #e9e9e9;
        }
        
        .export-btn {
            margin-top: 20px;
            padding: 8px 16px;
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
        }
        
        .no-participants {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f8f8;
            border-left: 5px solid #ccc;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="sidebar">
        <div class="menu">
            <a href="admin-dashboard.php"><i class="fas fa-home mr-3"></i>Home</a>
            <a href="admin-events.php" class="active"><i class="fas fa-calendar-alt mr-3"></i>Events</a>
            <a href="admin-users.php"><i class="fas fa-users mr-3"></i>Users</a>
            <a href="admin-notif.php"><i class="fas fa-bell mr-3"></i>Notification</a>
        </div>
    </div>

    <div class="content">
        <div class="content-header">
            <img src="styles/photos/DO-LOGO.png" width="70px" height="70px">
            <p>Learning and Development</p>
            <h1>EVENT MANAGEMENT SYSTEM</h1>
        </div><br><br><br>

        <div class="content-body">
            <h1>Registered Participants</h1>
            <h2><?php echo htmlspecialchars($eventTitle); ?></h2>
            <hr><br>

            <div class="content-area">
                <a href="admin-events.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Events</a>
                
                <?php if ($usersResult->num_rows > 0): ?>
                    <div class="participant-count">
                        <h3>Total Participants: <?php echo $usersResult->num_rows; ?></h3>
                    </div>
                    
                    <a href="export-participants.php?event_id=<?php echo $event_id; ?>" class="export-btn">
                        <i class="fas fa-file-export"></i> Export to Excel
                    </a>
                    
                    <table class="participant-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Position</th>
                                <th>Office</th>
                                <th>Registration Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $counter = 1;
                            while ($row = $usersResult->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['position']); ?></td>
                                    <td><?php echo htmlspecialchars($row['office']); ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($row['register_date'])); ?></td>
                                    <td>
                                        <a href="edit-participant.php?id=<?php echo $row['id']; ?>" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $row['id']; ?>)" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-participants">
                        <p><i class="fas fa-info-circle"></i> No participants have registered for this event yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(userId) {
    if (confirm('Are you sure you want to remove this participant?')) {
        window.location.href = 'delete-participant.php?id=' + userId + '&event_id=<?php echo $event_id; ?>';
    }
}
</script>

</body>
</html>

<?php
$conn->close();
?>