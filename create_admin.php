<?php
include('config.php');

$admin_username = 'admin';
$admin_password = 'Admin@123';
$admin_email = 'admin@balikbox.com';
$admin_fullname = 'System Administrator';

// Check if admin already exists
$check = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$check->bind_param("s", $admin_username);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (username, full_name, email, password_hash, user_type, is_active) VALUES (?, ?, ?, ?, 'Admin', 1)");
    $stmt->bind_param("ssss", $admin_username, $admin_fullname, $admin_email, $hashed_password);
    
    if ($stmt->execute()) {
        echo "✅ Admin account created successfully!<br>";
        echo "Username: <strong>{$admin_username}</strong><br>";
        echo "Password: <strong>{$admin_password}</strong><br>";
        echo "Email: <strong>{$admin_email}</strong><br>";
        echo "<br><a href='index.php'>Go to Login</a>";
    } else {
        echo "❌ Error: " . $conn->error;
    }
} else {
    echo "⚠️ Admin account already exists!";
}
?>