<?php
require_once 'config.php';

// Collect form data
$firstName = $_POST['first_name'];
$lastName = $_POST['last_name'];
$middleName = $_POST['middle_name'];
$suffix = $_POST['suffix'];
$sex = $_POST['sex'];
$contact_no = $_POST['contact_no'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password hashing
$school = $_POST['school'];
$positionId = $_POST['position']; // Position ID from dropdown
$classification = $_POST['classification'];

// First, insert into main users table (assuming you have one)
$userSql = "INSERT INTO users (first_name, last_name, middle_name, suffix, sex, email, password, contact_no) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($userSql);
$stmt->bind_param("ssssssss", $firstName, $lastName, $middleName, $suffix, $sex, $email, $password, $contact_no);

if ($stmt->execute()) {
    // Get the ID of the newly inserted user
    $userId = $stmt->insert_id;
    
    // Now insert additional details into users_lnd table
    $lndSql = "INSERT INTO users_lnd (users_id, position_id, classification, school_office_assignment) 
               VALUES (?, ?, ?, ?)";
    $lndStmt = $conn->prepare($lndSql);
    $lndStmt->bind_param("iiss", $userId, $positionId, $classification, $school);
    
    if ($lndStmt->execute()) {
        ?>
            <script type="text/javascript">
            alert("Signed In Successfully!");
            window.location.href="login.php";
            </script>
        <?php
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