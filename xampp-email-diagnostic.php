<?php
// Full diagnostic script for email sending in XAMPP

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Logging function
function writeDetailedLog($message) {
    $logPath = 'C:\xampp\htdocs\LD\email_diagnostics.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logPath, "[{$timestamp}] {$message}\n", FILE_APPEND);
}

// Environment Diagnostics
function checkEnvironment() {
    writeDetailedLog("PHP Version: " . phpversion());
    writeDetailedLog("XAMPP Version: " . (defined('XAMPP_VERSION') ? XAMPP_VERSION : 'Unknown'));
    
    // Check Extensions
    $requiredExtensions = ['openssl', 'curl', 'mysqli'];
    foreach ($requiredExtensions as $ext) {
        writeDetailedLog("Extension {$ext}: " . (extension_loaded($ext) ? 'Loaded' : 'Not Loaded'));
    }

    // PHP Mail Configuration
    writeDetailedLog("sendmail_path: " . ini_get('sendmail_path'));
    writeDetailedLog("SMTP setting: " . ini_get('smtp_port'));
}

// SMTP Connection Test
function testSMTPConnection() {
    $host = 'smtp.gmail.com';
    $port = 587;
    
    writeDetailedLog("Attempting SMTP Connection Test");
    
    $connection = fsockopen($host, $port, $errno, $errstr, 30);
    
    if (!$connection) {
        writeDetailedLog("SMTP Connection Failed: {$errno} - {$errstr}");
        return false;
    }
    
    writeDetailedLog("SMTP Connection Successful");
    fclose($connection);
    return true;
}

// PHPMailer Test
function testPHPMailerEmail() {
    require 'vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        $mail->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
        $mail->Debugoutput = function($str, $level) {
            writeDetailedLog("PHPMailer Debug ($level): $str");
        };
        
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'jeznooo@gmail.com';
        $mail->Password   = 'hvkg vecv wuzu mepl';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        $mail->setFrom('jeznooo@gmail.com', 'Diagnostic Test');
        $mail->addAddress('jeznooo@gmail.com');
        
        $mail->isHTML(true);
        $mail->Subject = 'XAMPP Email Diagnostic ' . date('Y-m-d H:i:s');
        $mail->Body    = 'This is a diagnostic email from XAMPP.';
        
        $result = $mail->send();
        
        writeDetailedLog($result ? 'Email Sent Successfully' : 'Email Sending Failed');
        writeDetailedLog('PHPMailer Error: ' . $mail->ErrorInfo);
        
        return $result;
    } catch (Exception $e) {
        writeDetailedLog('PHPMailer Exception: ' . $e->getMessage());
        return false;
    }
}

// Run Diagnostics
function runCompleteDiagnostics() {
    writeDetailedLog("Starting Comprehensive Email Diagnostics");
    
    checkEnvironment();
    testSMTPConnection();
    $emailResult = testPHPMailerEmail();
    
    writeDetailedLog("Diagnostic Process Complete");
    
    return $emailResult;
}

// Execute Diagnostics
$diagnosticResult = runCompleteDiagnostics();
echo $diagnosticResult ? "Diagnostic Test Passed" : "Diagnostic Test Failed";
?>
