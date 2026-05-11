<?php
session_start();
include('config.php');
include('includes/notifications.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "❌ You are not logged in. <a href='index.php'>Login first</a>";
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$user_type = $_SESSION['user_type'];

echo "<h2>🔔 Notification System Test</h2>";
echo "Logged in as: <strong>$username</strong> (Type: $user_type, ID: $user_id)<br><br>";

// 1. Check if notifications table exists
$table_check = $conn->query("SHOW TABLES LIKE 'notifications'");
if ($table_check->num_rows == 0) {
    echo "❌ Notifications table does NOT exist!<br>";
    echo "Run this SQL in phpMyAdmin:<br>";
    echo "<pre>CREATE TABLE notifications (
        notification_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('shipment', 'payment', 'approval', 'delivery', 'system') DEFAULT 'shipment',
        is_read BOOLEAN DEFAULT FALSE,
        link VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    );</pre>";
    exit();
} else {
    echo "✅ Notifications table exists<br>";
}

// 2. Test create notification
echo "<br><strong>Creating test notification...</strong><br>";
$result = createNotification($user_id, 'Test Notification', 'This is a test at ' . date('H:i:s'), 'system', 'dashboard.php');

if ($result) {
    echo "✅ Notification created! (ID: $result)<br>";
} else {
    echo "❌ Failed to create notification. Error: " . $conn->error . "<br>";
}

// 3. Get unread count
$unread = getUnreadCount($user_id);
echo "<br>📬 Unread count: <strong>$unread</strong><br>";

// 4. Get all notifications
$notifications = getNotifications($user_id, 10);
echo "<br><strong>Your Notifications:</strong><br>";
if ($notifications->num_rows > 0) {
    echo "<ul>";
    while($n = $notifications->fetch_assoc()) {
        echo "<li>[ID: {$n['notification_id']}] {$n['title']} - " . ($n['is_read'] ? 'Read' : 'UNREAD') . " - {$n['created_at']}</li>";
    }
    echo "</ul>";
} else {
    echo "No notifications found.<br>";
}

// 5. Test mark as read
if ($unread > 0) {
    echo "<br><strong>Testing mark as read...</strong><br>";
    $first_notif = $conn->query("SELECT notification_id FROM notifications WHERE user_id = $user_id AND is_read = FALSE LIMIT 1");
    if ($first_notif->num_rows > 0) {
        $first = $first_notif->fetch_assoc();
        markAsRead($first['notification_id'], $user_id);
        echo "✅ Marked notification ID {$first['notification_id']} as read<br>";
        
        $new_unread = getUnreadCount($user_id);
        echo "New unread count: $new_unread<br>";
    }
}

echo "<br><hr>";
echo "<a href='dashboard.php' style='background:blue;color:white;padding:10px;border-radius:5px;text-decoration:none;'>← Back to Dashboard</a>";
?>