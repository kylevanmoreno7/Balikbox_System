<?php
session_start();
include('config.php');
include('includes/notifications.php'); // Add this line - was missing!

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['unread_count' => 0]);
    exit();
}

$unread_count = getUnreadCount($_SESSION['user_id']);
echo json_encode(['unread_count' => $unread_count]);
?>