<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require 'vendor/autoload.php';
session_start();

$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

if (!$event_id) {
    // Check if we're in an AJAX call
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        // Return error as JSON for AJAX requests
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'No event specified']);
        exit;
    } else {
        // Display user-friendly error for direct browser access
        echo '<div style="text-align:center; margin-top:50px;">
              <h2>Error: No event specified</h2>
              <p>Please select an event from the events list.</p>
              <a href="admin-events.php" style="display:inline-block; margin-top:20px; padding:10px 20px; 
                 background-color:#3498db; color:white; text-decoration:none; border-radius:4px;">
                 Return to Events
              </a>
              </div>';
        exit;
    }
}

// Increase PHP execution time limit for this script
set_time_limit(300); // Increase to 5 minutes or adjust as needed
ini_set('memory_limit', '256M'); // Increase memory limit if needed

// Get event ID from URL
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

if (!$event_id) {
    die("No event specified.");
}

// Check if we're in batch processing mode
$batch_mode = isset($_GET['batch']) && $_GET['batch'] == 'true';
$batch_offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$batch_size = 10; // Process 10 participants at a time

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

// Get total number of participants for progress calculation
$countSQL = "SELECT COUNT(*) as total FROM registered_users WHERE event_id = ?";
$stmt = $conn->prepare($countSQL);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$totalResult = $stmt->get_result();
$totalRow = $totalResult->fetch_assoc();
$total_participants = $totalRow['total'];

// Use event title for folder name (sanitize it for file system)
$eventTitleSafe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $event_title);
$certificatesDir = 'certificates/' . $eventTitleSafe;

// Create certificates directory if it doesn't exist
if (!file_exists($certificatesDir)) {
    mkdir($certificatesDir, 0777, true);
}

// Initialize or retrieve session-stored progress data
if (!isset($_SESSION['certificate_progress'][$event_id])) {
    unset($_SESSION['certificate_progress'][$event_id]);
    $_SESSION['certificate_progress'][$event_id] = [
        'certificate_count' => 0,
        'email_count' => 0,
        'errors' => [],
        'completed' => false,
        'processed_users' => []
    ];
}

$progress = &$_SESSION['certificate_progress'][$event_id];

// Ensure processed_users exists (defensive programming)
if (!isset($progress['processed_users'])) {
    $progress['processed_users'] = [];
}

if ($batch_offset == 0) {  // If this is the first batch
    $progress['processed_users'] = [];  // Clear previously processed users
}

// For AJAX batch processing requests, return JSON
if ($batch_mode) {
    // Get a batch of participants
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
            WHERE ru.event_id = ?
            LIMIT ?, ?";

    $stmt = $conn->prepare($participantsSQL);
    $stmt->bind_param("iii", $event_id, $batch_offset, $batch_size);
    $stmt->execute();
    $participantsResult = $stmt->get_result();
    
    try {
        try {
            // Process each participant in this batch
            while ($participant = $participantsResult->fetch_assoc()) {
                $participant_name = $participant['name'];
                $email = $participant['email'];
                $user_id = $participant['user_id'];
                
                // Skip if we've already processed this user
                if (in_array($user_id, $progress['processed_users'])) {
                    continue;
                }

                // Also check if certificate already exists in database
                $certCheckSQL = "SELECT id FROM certificates WHERE user_id = ? AND event_id = ?";
                $certCheckStmt = $conn->prepare($certCheckSQL);
                $certCheckStmt->bind_param("ii", $user_id, $event_id);
                $certCheckStmt->execute();
                $certCheckResult = $certCheckStmt->get_result();
                
                // Skip if certificate already exists in database
                if ($certCheckResult->num_rows > 0) {
                    // Add to processed users list to avoid rechecking
                    $progress['processed_users'][] = $user_id;
                    continue;
                }
                            
                // Add to processed users list
                $progress['processed_users'][] = $user_id;
                
                // Define replacements for certificate
                $replacements = [
                    'participant_name' => $participant_name,
                    'event_title' => $event_title,
                    'event_start_date and end_date' => $date_range,
                    'venue' => $venue,
                    'end_date' => $event_end_date,
                    'date_month' => $date_month,
                    'event_year' => $event_year
                ];
                
                // Sanitize participant name for filename
                $participantNameSafe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $participant_name);
                $outputFile = $certificatesDir . '/' . $participantNameSafe . '.pdf';
                
                // IMPORTANT: Actually generate the certificate file
                $certificateGenerated = generateCertificatePDF($outputFile, $replacements);
                
                if (!$certificateGenerated) {
                    $progress['errors'][] = "Failed to generate certificate for {$participant_name}";
                    error_log("Failed to generate certificate for {$participant_name}");
                    continue; // Skip database updates and email if certificate generation failed
                }
                
                $conn->begin_transaction();
                // Record in database
                try {
                    // Record certificate generation in database
                    $certSQL = "INSERT INTO certificates (user_id, event_id, generated_date, certificate_path) 
                                VALUES (?, ?, NOW(), ?)
                                ON DUPLICATE KEY UPDATE generated_date = NOW(), certificate_path = ?";
                    $certStmt = $conn->prepare($certSQL);
                    $certStmt->bind_param("iiss", $user_id, $event_id, $outputFile, $outputFile);
                    $certStmt->execute();
                    
                    // Insert notification for the user
                    $notificationMessage = "Your certificate for event: {$event_title} is now available to download";
                    error_log("About to insert notification for user_id: {$user_id}, event_id: {$event_id}, message: {$notificationMessage}");


                                    // First, verify user exists
                $userCheckSQL = "SELECT id FROM users WHERE id = ?";
                $userCheckStmt = $conn->prepare($userCheckSQL);
                $userCheckStmt->bind_param("i", $user_id);
                $userCheckStmt->execute();
                $userCheckResult = $userCheckStmt->get_result();

                if ($userCheckResult->num_rows === 0) {
                    error_log("ERROR: User ID {$user_id} does not exist in the users table!");
                    $progress['errors'][] = "User ID {$user_id} does not exist in the database";
                    continue;
                }

                // Next, verify event exists
                $eventCheckSQL = "SELECT id FROM events WHERE id = ?";
                $eventCheckStmt = $conn->prepare($eventCheckSQL);
                $eventCheckStmt->bind_param("i", $event_id);
                $eventCheckStmt->execute();
                $eventCheckResult = $eventCheckStmt->get_result();

                if ($eventCheckResult->num_rows === 0) {
                    error_log("ERROR: Event ID {$event_id} does not exist in the events table!");
                    $progress['errors'][] = "Event ID {$event_id} does not exist in the database";
                    continue;
                }

                // Try just using individual values instead of variables for binding
                $type = 'user';
                $subtype = 'certificate';
                $isRead = 0;
                $currentTime = date('Y-m-d H:i:s');

                $notificationSQL = "INSERT INTO notifications 
                                   (user_id, message, created_at, is_read, notification_type, notification_subtype, event_id) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)";
                                   
                // Debug the SQL with actual values
                $debugSQL = str_replace('?', '%s', $notificationSQL);
                $debugSQL = sprintf($debugSQL, 
                    $user_id, 
                    "'".$notificationMessage."'", 
                    "'".$currentTime."'", 
                    $isRead, 
                    "'".$type."'", 
                    "'".$subtype."'", 
                    $event_id
                );
                error_log("Notification SQL with values: " . $debugSQL);

                $notifStmt = $conn->prepare($notificationSQL);
                // Try binding with explicit types and values
                $notifStmt->bind_param("ississi", 
                    $user_id, 
                    $notificationMessage, 
                    $currentTime, 
                    $isRead, 
                    $type, 
                    $subtype, 
                    $event_id
                );
                               
                // Check if prepared statement is valid
                if ($notifStmt === false) {
                    error_log("Failed to prepare notification statement: " . $conn->error);
                    $progress['errors'][] = "Failed to prepare notification SQL for user: {$participant_name}";
                    continue;
                }
                    
                    // Add this debugging
                    $notifResult = $notifStmt->execute();
                    if (!$notifResult) {
                        error_log("Failed to insert notification for user_id: {$user_id}, error: " . $conn->error);
                        $progress['errors'][] = "Failed to insert notification for user: {$participant_name}";
                    } else {
                        error_log("Successfully inserted notification for user_id: {$user_id}");
                    }
                    
                    $conn->commit();
                    $progress['certificate_count']++;

                } catch (Exception $e) {
                    $conn->rollback();
                    error_log("Transaction failed: " . $e->getMessage());
                    $progress['errors'][] = "Failed to record certificate and notification for {$participant_name}";
                    continue; // Skip email if database transaction failed
                }
                
                // Send email with certificate - with improved error handling
                try {
                    $emailResult = sendCertificateEmail($email, $participant_name, $event_title, $outputFile);
                    if ($emailResult) {
                        $progress['email_count']++;
                        error_log("Successfully sent certificate email to: {$email}");
                    } else {
                        $progress['errors'][] = "Failed to send email to {$participant_name} ({$email})";
                        error_log("Failed to send certificate email to: {$email}");
                    }
                } catch (Exception $e) {
                    $progress['errors'][] = "Exception sending email to {$participant_name}: " . $e->getMessage();
                    error_log("Email exception for {$email}: " . $e->getMessage());
                }
                
                // Add small delay between processing users to prevent overloading
                usleep(100000); // 100ms delay
            }
            
            // Check if we've processed all participants
            $processed_count = count($progress['processed_users']);
            $is_complete = ($processed_count >= $total_participants);
            
            if ($is_complete) {
                $progress['completed'] = true;
                
                // Set final confirmation message
                $_SESSION['confirm_message'] = [
                    'status' => count($progress['errors']) > 0 ? 'partial_error' : 'success',
                    'message' => count($progress['errors']) > 0 
                        ? 'Event processed with some errors' 
                        : 'Event processed successfully',
                    'certificates' => $progress['certificate_count'],
                    'emails' => $progress['email_count'],
                    'event_id' => $event_id,
                    'errors' => $progress['errors']
                ];
            }
            
            // Return JSON response for the AJAX request
            header('Content-Type: application/json');
            echo json_encode([
                'status' => $is_complete ? 'complete' : 'in_progress',
                'certificates' => $progress['certificate_count'],
                'emails' => $progress['email_count'],
                'processed' => $processed_count,
                'total' => $total_participants,
                'errors' => count($progress['errors']),
                'errorDetails' => $progress['errors'],
                'next_offset' => $batch_offset + $batch_size
            ]);
            exit();
            
        } catch (Exception $e) {
            // Return error JSON
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
            exit();
        }
    } catch (Exception $e) {
        error_log("Fatal error processing user {$participant_name}: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        $progress['errors'][] = "Fatal error processing {$participant_name}: " . $e->getMessage();
    }
}    

// The functions for generating certificates and sending emails
function sendCertificateEmail($email, $participant_name, $event_title, $certificatePath) {
    // Log entry point
    error_log("Attempting to send email to: $email for event: $event_title");

    // Prevent sending to invalid email addresses
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid email address: $email");
        return false;
    }

    // Check if certificate file exists
    if (!file_exists($certificatePath)) {
        error_log("Certificate file not found: $certificatePath");
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
        $mail->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_OFF;

        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'lrnnganddev@gmail.com'; // REPLACE WITH YOUR EMAIL
        $mail->Password   = 'njda argh nxpi pbiw'; // REPLACE WITH YOUR APP PASSWORD
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Important: Set connection timeout
        $mail->Timeout = 120; // 120 seconds timeout
        $mail->SMTPKeepAlive = true;

        // Add these settings
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Enable detailed error logging
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer Debug Level $level: $str");
        };
        
        // Recipients
        $mail->setFrom('lrnnganddev@gmail.com', 'DepEd General Trias City'); // REPLACE WITH YOUR EMAIL
        $mail->addAddress($email, $participant_name);
        $mail->addReplyTo('support@depedgentriascity.ph', 'Support');
        
        // Add attachment
        $mail->addAttachment($certificatePath, basename($certificatePath));
        
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
        error_log("Stack trace: " . $e->getTraceAsString());
        return false;
    }
}

function generateCertificatePDF($outputFile, $replacements) {
    // Check if mPDF is available
    if (!class_exists('\Mpdf\Mpdf')) {
        error_log("mPDF not found. Please install via Composer: composer require mpdf/mpdf");
        return false;
    }

    try {
        // Set up font configuration
        $fontDirs = [
            __DIR__ . '/fonts',
        ];

        $fontData = [
            'oldenglish' => [
                'R' => 'OLDENGL.TTF',
            ],
            'bookmanold' => [
                'R' => 'BOOKOS.TTF',
                'B' => 'BOOKOSB.TTF',
            ], 
            'cambria' => [
                'R' => 'CAMBRIA.TTF',
                'B' => 'CAMBRIAB.TTF',
            ],  
        ];

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'tempDir' => sys_get_temp_dir(),
            'fontDir' => $fontDirs,
            'fontdata' => $fontData,
            'default_font' => 'times',
            'debug' => true,
            'compress' => true, // Enable compression
            'img_dpi' => 72, // Lower DPI for embedded images
        ]);

        // Get the logo and encode it to base64
        $logoPath = 'styles/photos/DEPED-LOGO.png';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        } else {
            error_log("Logo file not found: $logoPath");
            $logoSrc = '';
        }

        // Determine name and title lengths for responsive sizing
        $participant_name = $replacements['participant_name'];
        $event_title = $replacements['event_title'];
        $venue = $replacements['venue'];

        // HTML Template with enhanced responsive CSS
        // Build HTML content with correct font matching the image
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Certificate of Participation</title>
            <style>
                @page {
                    size: landscape;
                    margin: 0;
                    padding: 0;
                }
                body {
                    font-family: "Bookman Old Style", bookmanold, serif;
                    text-align: center;
                    margin: 0;
                    padding: 0;
                    width: 100%;
                    height: 100%;
                    position: relative;
                }
                .certificate {
                    width: 100%;
                    padding: 30px;
                    box-sizing: border-box;
                    position: relative;
                }
                .certificate:after {
                    content: "";
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    height: 5px;
                    background-color: black;
                }
                .logo {
                    width: 100px;
                    height: auto;
                    margin-top: 30px;
                    margin-bottom: 15px;
                }
                .republic {
                    font-family: "Old English Text MT", oldenglish, serif;
                    font-size: 16px;
                    margin-bottom: -6px;
                }
                .department {
                    font-family: "Old English Text MT", oldenglish, serif;
                    font-size: 25px;
                }
                .region {
                    font-family: "Cambria Bold", cambria;
                    font-weight: bold;
                    font-size: 13px;
                    line-height: 1.4;
                    text-transform: uppercase;
                    margin-bottom: -5px;
                }
                .title {
                    font-family: "Old English Text MT", oldenglish, serif;
                    font-size: 50px;
                    margin: 0 0 15px 0;
                }
                .awarded-to {
                    font-family: "Cambria", cambria, serif;
                    font-size: 18px;
                    margin: 15px 0;
                }
                .recipient {
                    font-family: "Bookman Old Style", bookmanold, serif;
                    font-size: ' . (strlen($participant_name) > 40 ? '32px' : (strlen($participant_name) > 30 ? '38px' : '46px')) . ';
                    font-weight: bold;
                    text-transform: uppercase;
                    margin: 15px 0;
                    line-height: 1.1;
                }
                .description {
                    font-family: "Bookman Old Style", bookmanold, serif;
                    font-size: ' . (strlen($event_title) > 40 ? '18px' : (strlen($event_title) > 30 ? '19px' : '20px')) . ';
                    line-height: 1.5;
                    margin: 15px auto;
                    width: 95%;
                    max-width: 90%;
                }
                .participant-text {
                    font-family: "Bookman Old Style", bookmanold, serif;
                    font-weight: bold;
                    text-transform: uppercase;
                }
                .event-title {
                    font-family: "Bookman Old Style", bookmanold, serif;
                    font-weight: bold;
                }
                .date-text {
                    font-family: "Bookman Old Style", bookmanold, serif;
                    margin-top: 30px;
                    font-size: 20px;
                }
                .signature {
                    margin-top: 40px;
                }
                .superintendent {
                    font-family: "Bookman Old Style", bookmanold, serif;
                    font-weight: bold;
                    font-size: 24px;
                    margin-bottom: 5px;
                    text-transform: uppercase;
                }
                .position {
                    font-family: "Bookman Old Style", bookmanold, serif;
                    font-size: 18px;
                    line-height: 1.4;
                }
            </style>
        </head>
        <body>
            <div class="certificate">
                <img src="' . $logoSrc . '" class="logo" alt="DEPED Logo">
                
                <div class="republic">Republic of the Philippines</div>
                <div class="department">Department of Education</div>
                <div class="region">REGION IV-A CALABARZON<br>SCHOOLS DIVISION OF GENERAL TRIAS CITY</div>
                
                <div class="title">Certificate of Participation</div>
                
                <div class="awarded-to">is awarded to</div>
                
                <div class="recipient">
                    ' . $participant_name . '
                </div>
                
                <div class="description">
                    for the meaningful engagement as <span class="participant-text">PARTICIPANT</span> during the<br>
                    <span class="event-title">"' . $event_title . '"</span> conducted by the Department of Education-Schools Division Office of General Trias City
                    On ' . $replacements['date_month'] . ' ' . $replacements['event_start_date and end_date'] . ', ' . $venue . '
                </div>
                
                <div class="date-text">
                    Given this ' . $replacements['end_date'] . '<sup>th</sup> day of ' . $replacements['date_month'] . ' ' . $replacements['event_year'] . '
                </div>
                
                <div class="signature">
                    <div class="superintendent">
                        IVAN BRIAN L. INDUCTIVO, CESO VI
                    </div>
                    <div class="position">
                        Assistant Schools Division Superintendent<br>
                        Officer-in-Charge
                        Office of the Schools Division Superintendent
                    </div>
                </div>
            </div>
        </body>
        </html>';

        // Write HTML to PDF
        $mpdf->WriteHTML($html);
                
        // Output to file
        $mpdf->Output($outputFile, 'F');
        
        return file_exists($outputFile);
    } catch (Exception $e) {
        error_log("mPDF error: " . $e->getMessage());
        return false;
    }
}

// Send email with certificate - with improved retry logic
$maxEmailRetries = 3;
$emailSent = false;

for ($retryCount = 0; $retryCount < $maxEmailRetries && !$emailSent; $retryCount++) {
    try {
        if ($retryCount > 0) {
            error_log("Retrying email send attempt {$retryCount} for {$email}");
            // Add increasing delay between retries
            sleep($retryCount * 2); // 2, 4, 6 seconds delay
        }
        
        $emailResult = sendCertificateEmail($email, $participant_name, $event_title, $outputFile);
        if ($emailResult) {
            $progress['email_count']++;
            error_log("Successfully sent certificate email to: {$email}");
            $emailSent = true;
        } else {
            error_log("Failed attempt {$retryCount} to send certificate email to: {$email}");
        }
    } catch (Exception $e) {
        error_log("Email exception attempt {$retryCount} for {$email}: " . $e->getMessage());
    }
}

if (!$emailSent) {
    $progress['errors'][] = "Failed to send email to {$participant_name} ({$email})";
    error_log("Failed to send certificate email to: {$email} after {$maxEmailRetries} attempts");
}

?>