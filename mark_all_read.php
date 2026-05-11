<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

markAllAsRead($_SESSION['user_id']);

$redirect = $_GET['redirect'] ?? 'dashboard.php';
header("Location: " . $redirect);
exit();
?>