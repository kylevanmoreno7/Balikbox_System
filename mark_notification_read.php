<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$notification_id = $data['notification_id'] ?? 0;

if ($notification_id) {
    markAsRead($notification_id, $_SESSION['user_id']);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>