<!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
        <link href="styles/admin-archives[.css" rel="stylesheet">
        <title>admin-archives</title>
        <style>
            .content-area { display: flex; justify-content: space-between; }
            .details-section { display: none; flex-basis: 30%; margin-left: 20px; background-color: #f9f9f9; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
            .events-section { flex-basis: 100%; transition: flex-basis 0.3s; }
        </style>
    </head>
    <body>
    
    <div class="container">
        <div class="sidebar">
            <div class="menu">
                <a href="admin-dashboard.php"><i class="fas fa-home mr-3"></i>Home</a>
                <a href="admin-events.php"><i class="fas fa-calendar-alt mr-3"></i>Events</a>
                <a href="admin-users.php"><i class="fas fa-users mr-3"></i>Users</a>
                <a href="admin-notif.php"><i class="fas fa-bell mr-3"></i>Notification</a>
                <a href="admin-archives.php" class="active"><i class="fa fa-archive" aria-hidden="true"></i>Archived</a>
            </div>
        </div>
    
        <div class="content">
            <div class="content-header">
                <img src="styles/photos/DO-LOGO.png" width="70px" height="70px">
                <p>Learning and Development</p>
                <h1>EVENT MANAGEMENT SYSTEM</h1>
            </div><br><br><br>
    
            <div class="content-body">
                <h1>Archived Events</h1>
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
                                echo '<p><strong>Event Specification:</strong> ' . htmlspecialchars(substr($row["event_specification"], 0, 100)) . '</p>';
                                echo '</div></a>';
                                echo '</div>';
                            }
                        } else {
                            echo "<p>No events found.</p>";
                        }
                        ?>
                    </div>
    </div>
    
   
    
    </body>
    </html>
    
