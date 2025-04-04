<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];
    
    // Fetch event details
    $event_sql = "SELECT title FROM events WHERE id = ?";
    $stmt = $conn->prepare($event_sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $event_result = $stmt->get_result();
    
    if ($event_result->num_rows > 0) {
        $event_row = $event_result->fetch_assoc();
        $event_title = $event_row['title'];
        
        // Fetch the actual certificate path from the database
        $cert_sql = "SELECT certificate_path FROM certificates WHERE event_id = ? AND user_id = ?";
        $cert_stmt = $conn->prepare($cert_sql);
        $cert_stmt->bind_param("ii", $event_id, $user_id);
        $cert_stmt->execute();
        $cert_result = $cert_stmt->get_result();
        
        if ($cert_result->num_rows > 0) {
            $cert_row = $cert_result->fetch_assoc();
            $certificate_path = $cert_row['certificate_path'];
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'certificate_path' => $certificate_path,
                'event_title' => $event_title
            ]);
            exit;
        } else {
            // Default certificate path if record doesn't exist
            $certificate_filename = "Certificate_" . $event_title . "_" . $user_id . ".pdf";
            $certificate_path = "certificates/" . preg_replace('/[^a-zA-Z0-9_-]/', '_', $event_title). "/" . $certificate_filename;
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'certificate_path' => $certificate_path,
                'event_title' => $event_title
            ]);
            exit;
        }
    }
}

header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Event not found']);
exit;