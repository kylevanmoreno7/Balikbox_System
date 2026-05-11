<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "finalsystem_db";

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include notification functions
if (file_exists('includes/notifications.php')) {
    require_once('includes/notifications.php');
}
?>