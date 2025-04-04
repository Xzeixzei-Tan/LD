<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require 'vendor/autoload.php';

// Get event ID from URL
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
$evaluation_link = isset($_GET['eval_link']) ? filter_var($_GET['eval_link'], FILTER_VALIDATE_URL) : '';

if (!$event_id || !$evaluation_link) {
    die("Invalid event ID or evaluation link.");
}

// Get event details
$eventSQL = "SELECT title FROM events WHERE id = ?";
$stmt = $conn->prepare($eventSQL);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$eventResult = $stmt->get_result();

if ($eventResult->num_rows == 0) {
    die("Event not found.");
}

$eventData = $eventResult->fetch_assoc();
$event_title = $eventData['title'];

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

$notificationCount = 0;
$emailsSent = 0;

// Generate and send evaluation link notifications
while ($participant = $participantsResult->fetch_assoc()) {
    $participant_name = $participant['name'];
    $email = $participant['email'];
    $user_id = $participant['user_id'];

    // Connection check
    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Insert notification for the user
    $notificationMessage = "Please complete the evaluation for the event: {$event_title}. Click the link to proceed: {$evaluation_link}";
    $notificationSQL = "INSERT INTO notifications (user_id, message, created_at, is_read, notification_type, notification_subtype, event_id) 
                        VALUES (?, ?, NOW(), 0, 'user', 'evaluation', ?)";
    $notifStmt = $conn->prepare($notificationSQL);

    // Check if preparation was successful
    if ($notifStmt === false) {
        // Log or display preparation error
        error_log("Notification prepare error: " . $conn->error);
        // Optional: die("Prepare failed: " . $conn->error);
    }

    // Bind parameters
    $bind_result = $notifStmt->bind_param("isi", $user_id, $notificationMessage, $event_id);

    // Check if binding was successful
    if ($bind_result === false) {
        error_log("Notification bind error: " . $notifStmt->error);
        // Optional: die("Bind failed: " . $notifStmt->error);
    }

    // Execute and check for errors
    if (!$notifStmt->execute()) {
        error_log("Notification execute error: " . $notifStmt->error);
        // Optional: die("Execute failed: " . $notifStmt->error);
    }

    // Optional: Check number of affected rows
    $affectedRows = $notifStmt->affected_rows;
    error_log("Notifications inserted: " . $affectedRows);

    $notifStmt->bind_param("isi", $user_id, $notificationMessage, $event_id);
    $notifStmt->execute();
    
    $notificationCount++;
    
    // Send email notification with evaluation link
    if (sendEvaluationLinkEmail($email, $participant_name, $event_title, $evaluation_link)) {
        $emailsSent++;
    }
}

// Redirect back to the event page with a success message
header("Location: sample admin events.php?event_id=$event_id&notifications=$notificationCount&emails=$emailsSent");
exit();

/**
 * Sends an email with evaluation link to a participant
 * 
 * @param string $email Recipient email address
 * @param string $participant_name Participant's full name
 * @param string $event_title Title of the event
 * @param string $evaluation_link URL of the evaluation form
 * @return bool Whether the email was sent successfully
 */
function sendEvaluationLinkEmail($email, $participant_name, $event_title, $evaluation_link) {
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
        $mail->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_OFF;
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
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
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "Event Evaluation Request: $event_title";
        $mail->Body    = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <p>Dear $participant_name,</p>
            <p>We kindly request you to complete the evaluation for the event: <strong>\"$event_title\"</strong>.</p>
            <p>Please click on the link below to access the evaluation form:</p>
            <p><a href='$evaluation_link'>Complete Evaluation</a></p>
            <p>Your feedback is important to us and will help us improve future events.</p>
            <p>Best regards,<br>DepEd General Trias City</p>
        </body>
        </html>";
        $mail->AltBody = "Dear $participant_name, Please complete the evaluation for $event_title. Evaluation Link: $evaluation_link";
        
        // Send the email
        if($mail->send()) {
            error_log("Evaluation link email sent successfully to: $email for event: $event_title");
            return true;
        } else {
            error_log("Failed to send evaluation link email to: $email. Error: " . $mail->ErrorInfo);
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Email exception for $email: " . $e->getMessage());
        return false;
    }
}
?>