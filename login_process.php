<?php
require_once 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['email'];
    $password = $_POST['password'];

    // Hardcoded credentials
    $valid_username = "lndAdmin";
    $valid_password = "adminlnd1234";


    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['user'] = $username; // Store session
        header("Location: admin-landing_page.php"); // Redirect to landing page
        exit();
    } else {
        //User credentials checking
        $stmt = $conn->prepare("SELECT email, password FROM users WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $_SESSION['user'] = $username;
            $_SESSION['role'] = $row['password'];

            header("Location: user-landing_page.php");
            exit();
        } else {
        $_SESSION['status'] = "Invalid Credentials";
        header("Location: login.php");
        exit;
        }

        $stmt->close();
        $conn->close();
    }
}
?>