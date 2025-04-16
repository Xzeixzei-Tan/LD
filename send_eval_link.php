<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require 'vendor/autoload.php';

// Get event ID and evaluation link from URL
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
$evaluation_link = isset($_GET['eval_link']) ? filter_var($_GET['eval_link'], FILTER_VALIDATE_URL) : '';

if (!$event_id || !$evaluation_link) {
    die(json_encode([
        'success' => false,
        'message' => "Invalid event ID or evaluation link."
    ]));
}

// Get event details
$eventSQL = "SELECT title FROM events WHERE id = ?";
$stmt = $conn->prepare($eventSQL);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$eventResult = $stmt->get_result();

if ($eventResult->num_rows == 0) {
    die(json_encode([
        'success' => false,
        'message' => "Event not found."
    ]));
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

// Get total participants count
$totalParticipants = $participantsResult->num_rows;

$notificationCount = 0;
$emailsSent = 0;
$errors = [];

// Generate and send evaluation link notifications
while ($participant = $participantsResult->fetch_assoc()) {
    $participant_name = $participant['name'];
    $email = $participant['email'];
    $user_id = $participant['user_id'];

    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        $errors[] = "Database connection error";
        continue;
    }

    // Insert notification
    $notificationMessage = "Please complete the evaluation for the event: {$event_title}. Click the link to proceed: {$evaluation_link}";
    $notificationSQL = "INSERT INTO notifications (user_id, message, created_at, is_read, notification_type, notification_subtype, event_id, evaluation_link)
                        VALUES (?, ?, NOW(), 0, 'user', 'evaluation', ?, ?)";
    $notifStmt = $conn->prepare($notificationSQL);

    if ($notifStmt === false) {
        error_log("Notification prepare error: " . $conn->error);
        $errors[] = "Failed to prepare notification for $email";
        continue;
    }

    $bind_result = $notifStmt->bind_param("isis", $user_id, $notificationMessage, $event_id, $evaluation_link);

    if ($bind_result === false) {
        error_log("Notification bind error: " . $notifStmt->error);
        $errors[] = "Failed to bind notification parameters for $email";
        continue;
    }

    if (!$notifStmt->execute()) {
        error_log("Notification execute error: " . $notifStmt->error);
        $errors[] = "Failed to execute notification query for $email";
        continue;
    }

    $notificationCount++;

    // Send email via Mailtrap
    if (sendEvaluationLinkEmail($email, $participant_name, $event_title, $evaluation_link)) {
        $emailsSent++;
    } else {
        $errors[] = "Failed to send email to $email";
    }
}

// Return response as JSON
echo json_encode([
    'success' => ($emailsSent > 0),
    'total' => $totalParticipants,
    'notificationCount' => $notificationCount,
    'emailsSent' => $emailsSent,
    'errors' => $errors
]);
exit();

/**
 * Send email with evaluation link using Mailtrap SMTP
 */
function sendEvaluationLinkEmail($email, $participant_name, $event_title, $evaluation_link) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid email address: $email");
        return false;
    }

    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("PHPMailer not found. Please install via Composer.");
        return false;
    }

    require_once 'vendor/autoload.php';

    $phpmailer = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $phpmailer->isSMTP();
        $phpmailer->Host = 'sandbox.smtp.mailtrap.io';
        $phpmailer->SMTPAuth = true;
        $phpmailer->Port = 2525;
        $phpmailer->Username = '8f66f6a214e1cb';
        $phpmailer->Password = '1911d833808710';
        $phpmailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;

        $phpmailer->setFrom('testing@yourdomain.com', 'DepEd General Trias City');
        $phpmailer->addAddress($email, $participant_name);
        $phpmailer->addReplyTo('support@depedgentriascity.ph', 'Support');

        $phpmailer->isHTML(true);
        $phpmailer->Subject = "Event Evaluation Request: $event_title";
        $phpmailer->Body = "
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
        $phpmailer->AltBody = "Dear $participant_name, Please complete the evaluation for $event_title. Evaluation Link: $evaluation_link";

        if ($phpmailer->send()) {
            error_log("Evaluation email sent to $email");
            return true;
        } else {
            error_log("Failed to send email to $email. Error: " . $phpmailer->ErrorInfo);
            return false;
        }

    } catch (Exception $e) {
        error_log("Email exception for $email: " . $e->getMessage());
        return false;
    }
}
?>