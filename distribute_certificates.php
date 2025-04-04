<?php
require_once 'config.php';
require 'vendor/autoload.php';
session_start();

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
                        CASE WHEN u.middle_name IS NOT NULL AND u.middle_name != '' 
                             THEN CONCAT(UPPER(SUBSTRING(u.middle_name, 1, 1)), '. ') 
                             ELSE '' END,
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
$emailsSent = 0;
$errors = [];

// Use event title for folder name (sanitize it for file system)
$eventTitleSafe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $event_title);
$certificatesDir = 'certificates/' . $eventTitleSafe;

// Create certificates directory if it doesn't exist
if (!file_exists($certificatesDir)) {
    mkdir($certificatesDir, 0777, true);
}

try {
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
        if (generateCertificatePDF('certificate.pdf', $outputFile, $replacements)) {
            // Record certificate generation in database
            $certSQL = "INSERT INTO certificates (user_id, event_id, generated_date, certificate_path) 
                        VALUES (?, ?, NOW(), ?)";
            $certStmt = $conn->prepare($certSQL);
            $certStmt->bind_param("iis", $user_id, $event_id, $outputFile);
            $certStmt->execute();
            
            // Insert notification for the user
            $notificationMessage = "Your certificate for event: {$event_title} is now available to download";
            $notificationSQL = "INSERT INTO notifications (user_id, message, created_at, is_read, notification_type, notification_subtype, event_id) 
                                VALUES (?, ?, NOW(), 0, 'user', 'certificate', ?)";
            $notifStmt = $conn->prepare($notificationSQL);
            $notifStmt->bind_param("isi", $user_id, $notificationMessage, $event_id);
            $notifStmt->execute();

            // Send email notification with certificate attachment
            if (sendCertificateEmail($email, $participant_name, $event_title, $outputFile)) {
                $emailsSent++;
            }
            
            $certificateCount++;
        } else {
            $errors[] = "Failed to generate certificate for {$participant_name}";
        }
    }

    // Prepare session message with comprehensive information
    $_SESSION['confirm_message'] = [
        'status' => count($errors) > 0 ? 'partial_error' : 'success',
        'message' => count($errors) > 0 
            ? 'Event processed with some errors' 
            : 'Event processed successfully',
        'certificates' => $certificateCount,
        'emails' => $emailsSent,
        'event_id' => $event_id,
        'errors' => $errors
    ];

} catch (Exception $e) {
    // Catch any unexpected errors
    $_SESSION['confirm_message'] = [
        'status' => 'error',
        'message' => 'Unexpected error: ' . $e->getMessage(),
        'event_id' => $event_id
    ];
}

// Redirect back to the event page
header("Location: admin-events.php?event_id=$event_id");
exit();

 /* Generates a PDF certificate for a participant
 * 
 * @param string $templatePath Path to the certificate template
 * @param string $outputFile Path where the generated certificate will be saved
 * @param array $replacements Associative array of replacement values
 * @return bool Whether the certificate was generated successfully
 */

 function sendCertificateEmail($email, $participant_name, $event_title, $certificatePath) {
    // Prevent sending to invalid email addresses
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid email address: $email");
        return false;
    }

    // Check if PHPMailer exists
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("PHPMailer not found. Please install via Composer.");
        return false;
    }

    // Load PHPMailer
    require_once 'vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Debug settings (remove in production)
        $mail->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_OFF; // Set to DEBUG_SERVER for full debug info
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'lrnnganddev@gmail.com'; // REPLACE WITH YOUR EMAIL
        $mail->Password   = 'njda argh nxpi pbiw'; // REPLACE WITH YOUR APP PASSWORD
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Enable detailed error logging
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer Debug Level $level: $str");
        };
        
        // Recipients
        $mail->setFrom('lrnnganddev@gmail.com', 'DepEd General Trias City'); // REPLACE WITH YOUR EMAIL
        $mail->addAddress($email, $participant_name);
        $mail->addReplyTo('support@depedgentriascity.ph', 'Support');
        
        // Attachments
        if (file_exists($certificatePath)) {
            $mail->addAttachment($certificatePath, basename($certificatePath));
        } else {
            error_log("Certificate file not found: $certificatePath");
            return false;
        }
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "Your Certificate for: $event_title";
        $mail->Body    = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <p>Dear $participant_name,</p>
            <p>Thank you for participating in the <strong>\"$event_title\"</strong>.</p>
            <p>Please find your certificate of participation attached to this email.</p>
            <p>You can also view and download your certificate by logging into your account on the DepEd General Trias City website.</p>
            <p>Best regards,<br>DepEd General Trias City</p>
        </body>
        </html>";
        $mail->AltBody = "Dear $participant_name, Thank you for participating in the $event_title. Your certificate is attached.";
        
        // Send the email
        if($mail->send()) {
            error_log("Certificate email sent successfully to: $email for event: $event_title");
            return true;
        } else {
            error_log("Failed to send certificate email to: $email. Error: " . $mail->ErrorInfo);
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Email exception for $email: " . $e->getMessage());
        return false;
    }
}

function generateCertificatePDF($templatePath, $outputFile, $replacements) {
    // Create a temporary HTML file
    $tempHtml = 'temp_' . uniqid() . '.html';

    // Get the logo and encode it to base64
    $logoPath = 'styles/photos/DEPED-LOGO.png';
    if (file_exists($logoPath)) {
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoData;
    } else {
        error_log("Logo file not found: $logoPath");
        $logoSrc = '';
    }

    // HTML Template with enhanced responsive CSS
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Certificate of Participation</title>
        <style>
            @page {
                size: landscape;
                margin: 0;
            }
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
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .header {
                font-size: 16px;
                line-height: 1.4;
                margin-bottom: 10px;
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

            .certificate {
                width: 100%;
                height: 100%;
                padding: 20px;
                box-sizing: border-box;
                position: relative;
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .title {
                font-family: Old English Text MT;
                font-size: 62px;
                color: black;
                margin: 10px 0;
            }

            .awarded-to {
                font-family: Times New Roman;
                font-size: 23px;
                margin: 10px 0;
            }

            .recipient {
                font-family: Bookman Old Style;
                font-weight: Bold;
                font-size: 55px; /* Base font size */
                max-width: 900px; /* Limit width */
                line-height: 1.1; /* Tighter line height */
                margin: 10px 0;
                word-break: break-word;
                hyphens: auto;
                text-transform: uppercase;
                display: inline-block;
                white-space: normal;
            }
            .recipient.extra-long {
                font-size: 45px;
            }
            .recipient.super-long {
                font-size: 35px;
            }

            .event-title {
                font-size: 26px;
                font-weight: 2000;
                max-width: 900px;
                word-break: break-word;
                hyphens: auto;
            }
            .event-title.long {
                font-size: 25px;
                font-weight: 3000;
            }
            .event-title.extra-long {
                font-size: 23px;
                font-weight: 3000;
            }

            strong {
                font-weight: 1000;
            }

            .description {
                font-family: Bookman Old Style;
                font-size: 24px;
                line-height: 1.5;
                max-width: 900px;
                margin: 10px 0;
                word-break: break-word;
                hyphens: auto;
            }
            .description.long {
                font-size: 23px;
            }
            .description.extra-long {
                font-size: 21px;
                line-height: 1.3;
            }

            .venue {
                font-size: 26px;
                max-width: 900px;
                word-break: break-word;
                hyphens: auto;
            }
            .venue.long {
                font-size: 25px;
            }
            .venue.extra-long {
                font-size: 23px;
            }

            .date {
                margin-top: 40px;
                font-size: 24px;
                margin-bottom: -23px;
            }
            .signature {
                margin-top: 20px;
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
                width: 130px; 
                height: 130px;
            }

            
        </style>
    </head>
    <body>
    <center>
        <div class="certificate">';
    
    // Determine name length and apply appropriate class
    $participant_name = $replacements['participant_name'];
    $name_class = 'recipient';
    if (strlen($participant_name) > 30) {
        $name_class .= ' extra-long';
    }
    if (strlen($participant_name) > 40) {
        $name_class .= ' super-long';
    }

    // Determine event title length and apply appropriate class
    $event_title = $replacements['event_title'];
    $event_title_class = 'event-title';
    if (strlen($event_title) > 30) {
        $event_title_class .= ' long';
    }
    if (strlen($event_title) > 40) {
        $event_title_class .= ' extra-long';
    }

    // Handle venue display based on content
    $venue = $replacements['venue'];
    $venue_part = '';
    
    // Check if venue is empty or contains only whitespace
    if (empty(trim($venue))) {
        // For empty venue, assume it's an online event
        $venue_part = "held online";
    } else {
        // For non-empty venue, check if it contains "online"
        if (stripos($venue, 'online') !== false) {
            // If "online" is in the venue text, format accordingly
            $venue_class = 'venue';
            if (strlen($venue) > 30) $venue_class .= ' long';
            if (strlen($venue) > 40) $venue_class .= ' extra-long';
            
            $venue_part = "held online via <span class=\"$venue_class\">" . $venue . "</span>";
        } else {
            // For physical venues
            $venue_class = 'venue';
            if (strlen($venue) > 30) $venue_class .= ' long';
            if (strlen($venue) > 40) $venue_class .= ' extra-long';
            
            $venue_part = "at the <span class=\"$venue_class\">" . $venue . "</span>";
        }
    }

    // Determine description length and apply appropriate class
    $description = "for the meaningful engagement as <strong>PARTICIPANT</strong> during the<br>
        <strong class=\"{$event_title_class}\">\"" . $event_title . "\" </strong> conducted by the Department of Education-Schools Division Office of General Trias City
        On " . $replacements['date_month'] . ' ' . $replacements['event_start_date and end_date'] . ', ' . $venue_part;
    $description_class = 'description';
    if (strlen(strip_tags($description)) > 150) {
        $description_class .= ' long';
    }
    if (strlen(strip_tags($description)) > 200) {
        $description_class .= ' extra-long';
    }

    $html .= '
            <div class="header">
                <img src="' . $logoSrc . '" class="logo" alt="DEPED Logo"><br>
                <div class="header-1">
                Republic of the Philippines</div>
                <div class="header-2">Department of Education</div>
                <div class="header-3">REGION IV-A CALABARZON<br>
                SCHOOLS DIVISION OF GENERAL TRIAS CITY</div>
            </div>
            
            <div class="title">Certificate of Participation</div>
            
            <div class="awarded-to">is awarded to</div>
            
            <div class="' . $name_class . '">' . $participant_name . '</div>
            
            <div class="' . $description_class . '">
                ' . $description . '
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
    $command = "\"C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltopdf.exe\" --page-size A4 --orientation Landscape $tempHtml $outputFile";
    exec($command);
    
    // Alternative: If wkhtmltopdf isn't available, copy the template and use it as is
    if (!file_exists($outputFile)) {
        copy($templatePath, $outputFile);
    }
    
    // Clean up temp file
    if (file_exists($tempHtml)) {
        unlink($tempHtml);
    }
    
    return true;
}