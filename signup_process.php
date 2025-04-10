<?php
require_once 'config.php';
session_start();

// Check if form fields are not empty
$firstName = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$lastName = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$middleName = isset($_POST['middle_name']) ? trim($_POST['middle_name']) : '';
$suffix = isset($_POST['suffix']) ? trim($_POST['suffix']) : '';
$sex = isset($_POST['sex']) ? $_POST['sex'] : '';
$contact_no = isset($_POST['contact_no']) ? trim($_POST['contact_no']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : '';
$positionId = isset($_POST['position']) ? $_POST['position'] : '';

// Always set affiliation ID to 1
$affiliationId = 1;

// Validate that required fields are not empty
if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
    echo "<script>alert('Please fill in all required fields.'); window.location.href='signup.php';</script>";
    exit();
}

// Check if email already exists
$emailCheckSql = "SELECT id FROM users WHERE email = ?";
$emailCheckStmt = $conn->prepare($emailCheckSql);
$emailCheckStmt->bind_param("s", $email);
$emailCheckStmt->execute();
$emailCheckStmt->store_result();

if ($emailCheckStmt->num_rows > 0) {
    $_SESSION['status'] = 'This User already exists.';
    header("Location: signup.php");
    exit();
}
$emailCheckStmt->close();

// Check if a user with the same first name, middle name, and last name already exists
$nameCheckSql = "SELECT id FROM users WHERE first_name = ? AND last_name = ?";
$params = "ss";
$paramValues = [$firstName, $lastName];

// Only include middle name in the check if it's not empty
if (!empty($middleName)) {
    $nameCheckSql .= " AND middle_name = ?";
    $params .= "s";
    $paramValues[] = $middleName;
}

$nameCheckStmt = $conn->prepare($nameCheckSql);
$nameCheckStmt->bind_param($params, ...$paramValues);
$nameCheckStmt->execute();
$nameCheckStmt->store_result();

if ($nameCheckStmt->num_rows > 0) {
    $_SESSION['status'] = 'User already exists';
    header("Location: signup.php");
    exit();
}
$nameCheckStmt->close();

// Get classification related to the selected position
$classSql = "SELECT classification_id FROM class_position WHERE id = ?";
$classStmt = $conn->prepare($classSql);
$classStmt->bind_param("i", $positionId);
$classStmt->execute();
$classStmt->bind_result($classification);
$classStmt->fetch();
$classStmt->close();

// Insert into users table
$userSql = "INSERT INTO users (first_name, last_name, middle_name, suffix, sex, email, password, contact_no) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($userSql);
$stmt->bind_param("ssssssss", $firstName, $lastName, $middleName, $suffix, $sex, $email, $password, $contact_no);

if ($stmt->execute()) {
    // Get the newly inserted user ID
    $userId = $stmt->insert_id;

    // Insert into users_lnd table with affiliation_id always set to 1
    $lndSql = "INSERT INTO users_lnd (user_id, position_id, classification_id, affiliation_id) 
               VALUES (?, ?, ?, ?)";
    $lndStmt = $conn->prepare($lndSql);
    $lndStmt->bind_param("iiii", $userId, $positionId, $classification, $affiliationId);

    if ($lndStmt->execute()) {
        // Create notifications
        
        // 1. Notification for the user who just signed up
        $userMessage = "Welcome to the platform! Your account has been created successfully.";
        $currentDateTime = date("Y-m-d H:i:s");
        $userNotificationType = "user";
        $userNotificationSubtype = "signup";
        
        $userNotificationSql = "INSERT INTO notifications (user_id, message, created_at, is_read, notification_type, notification_subtype) 
                               VALUES (?, ?, ?, 0, ?, ?)";
        $userNotificationStmt = $conn->prepare($userNotificationSql);
        $userNotificationStmt->bind_param("issss", $userId, $userMessage, $currentDateTime, $userNotificationType, $userNotificationSubtype);
        $userNotificationStmt->execute();
        $userNotificationStmt->close();
        
        // 2. Notification for admin about new user signup
        $fullName = $firstName . ' ' . $lastName;
        $adminMessage = "New user {$fullName} has signed up on the platform.";
        $adminNotificationType = "admin";
        $adminNotificationSubtype = "new_user_signup";
        
        $adminNotificationSql = "INSERT INTO notifications (user_id, message, created_at, is_read, notification_type, notification_subtype) 
                                VALUES (NULL, ?, ?, 0, ?, ?)";
        $adminNotificationStmt = $conn->prepare($adminNotificationSql);
        $adminNotificationStmt->bind_param("ssss", $adminMessage, $currentDateTime, $adminNotificationType, $adminNotificationSubtype);
        $adminNotificationStmt->execute();
        $adminNotificationStmt->close();
        
        echo "<script>alert('Signed Up Successfully!'); window.location.href='login.php';</script>";
    } else {
        echo "Error: " . $lndStmt->error;
    }
    
    $lndStmt->close();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>