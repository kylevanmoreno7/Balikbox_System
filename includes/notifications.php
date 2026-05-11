<?php
// File: includes/notifications.php

if (!function_exists('createNotification')) {
    function createNotification($user_id, $title, $message, $type = 'shipment', $link = null) {
        global $conn;
        
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $title, $message, $type, $link);
        
        if ($stmt->execute()) {
            return $conn->insert_id;
        }
        return false;
    }
}

if (!function_exists('getUnreadCount')) {
    function getUnreadCount($user_id) {
        global $conn;
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }
}

if (!function_exists('getNotifications')) {
    function getNotifications($user_id, $limit = 10) {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        return $stmt->get_result();
    }
}

if (!function_exists('markAsRead')) {
    function markAsRead($notification_id, $user_id) {
        global $conn;
        $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE notification_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notification_id, $user_id);
        return $stmt->execute();
    }
}

if (!function_exists('markAllAsRead')) {
    function markAllAsRead($user_id) {
        global $conn;
        $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }
}

if (!function_exists('getAdminUsers')) {
    function getAdminUsers() {
        global $conn;
        $result = $conn->query("SELECT user_id, username, email FROM users WHERE user_type = 'Admin' AND is_active = TRUE");
        $admins = [];
        while($row = $result->fetch_assoc()) {
            $admins[] = $row;
        }
        return $admins;
    }
}

if (!function_exists('notifyAllAdmins')) {
    function notifyAllAdmins($title, $message, $type = 'shipment', $link = null) {
        $admins = getAdminUsers();
        foreach($admins as $admin) {
            createNotification($admin['user_id'], $title, $message, $type, $link);
        }
        return count($admins);
    }
}

if (!function_exists('notifyUser')) {
    function notifyUser($user_id, $title, $message, $type = 'shipment', $link = null) {
        return createNotification($user_id, $title, $message, $type, $link);
    }
}

if (!function_exists('time_ago')) {
    function time_ago($timestamp) {
        $time_ago = strtotime($timestamp);
        $current_time = time();
        $time_difference = $current_time - $time_ago;
        $seconds = $time_difference;
        
        $minutes = round($seconds / 60);
        $hours = round($seconds / 3600);
        $days = round($seconds / 86400);
        $weeks = round($seconds / 604800);
        $months = round($seconds / 2629440);
        $years = round($seconds / 31553280);
        
        if ($seconds <= 60) {
            return "Just now";
        } else if ($minutes <= 60) {
            return ($minutes == 1) ? "1 minute ago" : "$minutes minutes ago";
        } else if ($hours <= 24) {
            return ($hours == 1) ? "1 hour ago" : "$hours hours ago";
        } else if ($days <= 7) {
            return ($days == 1) ? "yesterday" : "$days days ago";
        } else if ($weeks <= 4.3) {
            return ($weeks == 1) ? "1 week ago" : "$weeks weeks ago";
        } else if ($months <= 12) {
            return ($months == 1) ? "1 month ago" : "$months months ago";
        } else {
            return ($years == 1) ? "1 year ago" : "$years years ago";
        }
    }
}
?>