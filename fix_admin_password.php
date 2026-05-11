<?php
include('config.php');

// I-correct ang password ni admin to "Admin@123"
$new_password = 'Admin@123';
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);

// Check if admin exists
$check = $conn->query("SELECT user_id, username FROM users WHERE username = 'admin'");

if ($check->num_rows > 0) {
    // Update existing admin password
    $update = $conn->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
    $update->bind_param("s", $new_hash);
    
    if ($update->execute()) {
        echo "✅ Admin password updated successfully!<br>";
        echo "Username: <strong>admin</strong><br>";
        echo "New Password: <strong>Admin@123</strong><br>";
        echo "<br><a href='index.php'>Go to Login</a>";
    } else {
        echo "❌ Error: " . $conn->error;
    }
} else {
    // Wala si admin, i-create (FIX: gamit ang tamang hash)
    $fullname = "System Administrator";
    $email = "admin@balikbox.com";
    
    $insert = $conn->prepare("INSERT INTO users (username, full_name, email, password_hash, user_type, is_active) VALUES (?, ?, ?, ?, 'Admin', 1)");
    $insert->bind_param("ssss", $username, $fullname, $email, $new_hash);
    
    if ($insert->execute()) {
        echo "✅ Admin account created successfully!<br>";
        echo "Username: <strong>admin</strong><br>";
        echo "Password: <strong>Admin@123</strong><br>";
        echo "<br><a href='index.php'>Go to Login</a>";
    } else {
        echo "❌ Error: " . $conn->error;
    }
}
?>