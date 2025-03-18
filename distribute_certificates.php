<?php
require_once 'config.php';

// Get event ID from URL
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

if (!$event_id) {
    die("No event specified.");
}

// Get event details
$eventSQL = "SELECT title, start_date, end_date, venue FROM events WHERE id = ?";
$stmt = $conn->prepare($eventSQL);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$eventResult = $stmt->get_result();

if ($eventResult->num_rows == 0) {
    die("Event not found.");
}

$eventData = $eventResult->fetch_assoc();
$event_title = $eventData['title'];
$start_date = new DateTime($eventData['start_date']);
$end_date = new DateTime($eventData['end_date']);
$venue = $eventData['venue'];

// Format dates
$event_start_date = $start_date->format('j'); // Day number without leading zeros
$date_month = $start_date->format('F'); // Full month name
$event_year = $start_date->format('Y'); // 4-digit year
$event_end_date = $end_date->format('j'); // Day number without leading zeros

// Date format for certificate
$date_range = "";
if ($event_start_date == $event_end_date) {
    $date_range = $event_start_date;
} else {
    $date_range = $event_start_date . "-" . $event_end_date;
}

// Get all registered participants
$participantsSQL = "SELECT 
                    ru.id AS registration_id,
                    ru.user_id,
                    CONCAT(u.first_name, ' ', 
                        CASE WHEN u.middle_name IS NOT NULL AND u.middle_name != '' THEN CONCAT(u.middle_name, ' ') ELSE '' END,
                        u.last_name,
                        CASE WHEN u.suffix IS NOT NULL AND u.suffix != '' THEN CONCAT(' ', u.suffix) ELSE '' END
                    ) AS name,
                    u.email
                FROM registered_users ru
                JOIN users u ON ru.user_id = u.id
                WHERE ru.event_id = ?";

$stmt = $conn->prepare($participantsSQL);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$participantsResult = $stmt->get_result();

$certificateCount = 0;
// Use event title for folder name (sanitize it for file system)
$eventTitleSafe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $event_title);
$certificatesDir = 'certificates/' . $eventTitleSafe;

// Create certificates directory if it doesn't exist
if (!file_exists($certificatesDir)) {
    mkdir($certificatesDir, 0777, true);
}

// Generate certificates for each participant
while ($participant = $participantsResult->fetch_assoc()) {
    $participant_name = $participant['name'];
    $email = $participant['email'];
    $participant_id = $participant['registration_id'];
    $user_id = $participant['user_id'];
    
    // Define replacements
    $replacements = [
        'participant_name' => $participant_name,
        'event_title' => $event_title,
        'event_start_date and end_date' => $date_range,
        'venue' => $venue,
        'end_date' => $event_end_date,
        'date_month' => $date_month,
        'event_year' => $event_year
    ];
    
    // Use participant name for file name (sanitize it for file system)
    $participantNameSafe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $participant_name);
    $outputFile = $certificatesDir . '/' . $participantNameSafe . '.pdf';
    
    // Generate certificate using our custom function
    generateCertificatePDF('certificate.pdf', $outputFile, $replacements);
    
    // Record certificate generation in database
    $certSQL = "INSERT INTO certificates (user_id, event_id, generated_date, certificate_path) 
                VALUES (?, ?, NOW(), ?)";
    $certStmt = $conn->prepare($certSQL);
    $certStmt->bind_param("iis", $user_id, $event_id, $outputFile);
    $certStmt->execute();
    
    // Insert notification for the user
    $notificationMessage = "Your certificate for event: {$event_title} is now available for download";
    $notificationSQL = "INSERT INTO notifications (user_id, message, created_at, is_read, notification_type, notification_subtype, event_id) 
                        VALUES (?, ?, NOW(), 0, 'user', 'certificate', ?)";
    $notifStmt = $conn->prepare($notificationSQL);
    $notifStmt->bind_param("isi", $user_id, $notificationMessage, $event_id);
    $notifStmt->execute();
    
    $certificateCount++;
}

// Redirect back to the event page with a success message
header("Location: admin-events.php?event_id=$event_id&certificates=$certificateCount");
exit();

// Our PDF certificate generation function without using libraries
function generateCertificatePDF($templatePath, $outputFile, $replacements) {
    // Method using HTML/CSS to create PDF via command-line tool
    
    // Create a temporary HTML file
    $tempHtml = 'temp_' . uniqid() . '.html';

    // Get the logo and encode it to base64 - use just the filename since it's in the same folder
    $logoPath = 'styles/photos/DEPED-LOGO.png';
    if (file_exists($logoPath)) {
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoData;
    } else {
        // Log the error but continue without the logo
        error_log("Logo file not found: $logoPath");
        $logoSrc = '';
    }
    
    // HTML Template matching exactly the PDF template format
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Certificate of Participation</title>
        <style>
            body {
                font-family: Bookman Old Style;
                text-align: center;
                margin: auto;
                padding: 0;
                width: 1100px; /* Landscape A4 width */
                height: 800px; /* Landscape A4 height */
                position: relative;
                background-image: url("certificate_bg.jpg");
                background-size: cover;
                background-repeat: no-repeat;
            }
            .certificate {
                width: 100%;
                height: 100%;
                padding: 20px;
                box-sizing: border-box;
                position: relative;
            }
            .header {
                font-size: 16px;
                line-height: 1.4;
            }

            .header-1 {
                margin-top: 10px;
                font-size: 20px;
                font-family: Old English Text MT;
                line-height: 1.4;
            }

            .header-2 {
                font-size: 31px;
                font-family: Old English Text MT;
                line-height: 1.4;
            }

            .header-3 {
                font-size: 16px;
                font-family: Cambria;
                font-weight: bold;
                line-height: 1.4;
            }

            .title {
                font-family: Old English Text MT;
                font-size: 65px;
                color: black;
            }
            .awarded-to {
                font-family: Times New Roman;
                font-size: 23px;
                margin: 15px 0;
            }
            .recipient {
                font-family: Bookman Old Style;
                font-weight: Bold;
                font-size: 70px;
                display: inline-block;
            }

            .description, strong {
                font-family: Bookman Old Style;
                font-size: 25px;
                font-weight: 0;
                line-height: 1.5;
            }
            .date {
                margin-top: 35px;
                font-size: 24px;
            }
            .signature {
                margin-top: 5px;
            }
            .superintendent {
                letter-spacing: 1px;
                font-weight: bolder;
                font-size: 28px;
                margin-bottom: 5px;
            }
            .position {
                font-size: 21px;
                line-height: 1.4;
            }

            .logo {
                margin: auto;
                top: 10px;
                left: 20px;
                width: 130px; /* Adjust size as needed */
                height: 130px;
            }
        </style>
    </head>
    <body>
    <center>
        <div class="certificate">';
    
    $html .='<div class="header">

                <img src="' . $logoSrc . '" class="logo" alt="DEPED Logo"><br>
                <div class="header-1">
                Republic of the Philippines</div>
                <div class="header-2">Department of Education</div>
                <div class="header-3">REGION IV-A CALABARZON<br>
                SCHOOLS DIVISION OF GENERAL TRIAS CITY</div>
            </div>
            
            <div class="title">Certificate of Participation</div>
            
            <div class="awarded-to">is awarded to</div>
            
            <div class="recipient">' . $replacements['participant_name'] . '</div>
            
            <div class="description">
                for the meaningful engagement as <strong>PARTICIPANT</strong> during the<br>
                <strong>"' . $replacements['event_title'] . '" </strong> conducted by the Department of Education-Schools Division Office of General Trias City
                On ' . $replacements['event_start_date and end_date'] . ', at the ' . $replacements['venue'] . '
            </div>
            
            <div class="date">
                Given this ' . $replacements['end_date'] . '<sup>th</sup> day of ' . $replacements['date_month'] . ' ' . $replacements['event_year'] . '
            </div>
            
            <div class="signature">
                <div class="superintendent">IVAN BRIAN L. INDUCTIVO, CESO VI</div>
                <div class="position">
                    Assistant Schools Division Superintendent<br>
                    Officer-in-Charge<br>
                    Office of the Schools Division Superintendent
                </div>
            </div>
        </div>
    </center>    
    </body>
    </html>';
    
    // Write HTML to file
    file_put_contents($tempHtml, $html);
    
    // Convert HTML to PDF using command-line tool (wkhtmltopdf)
    // You need to have wkhtmltopdf installed on your server
    $command = "\"C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltopdf.exe\" --page-size A4 --orientation Landscape $tempHtml $outputFile";
    exec($command);
    
    // Alternative: If wkhtmltopdf isn't available, copy the template and use it as is
    // This doesn't allow for customization but provides a fallback
    if (!file_exists($outputFile)) {
        copy($templatePath, $outputFile);
    }
    
    // Clean up temp file
    if (file_exists($tempHtml)) {
        unlink($tempHtml);
    }
    
    return true;
}
?>