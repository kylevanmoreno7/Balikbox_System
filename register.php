<?php
session_start();
include('config.php'); 

$error = "";
$success = "";

$username = "";
$full_name = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = 'Customer'; 

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } 
    else {
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Username or Email already exists!";
        } else {
            $plain_password = $password;
            
            $insert_stmt = $conn->prepare("INSERT INTO users (username, full_name, email, password_hash, user_type, is_active) VALUES (?, ?, ?, ?, ?, 1)");
            $insert_stmt->bind_param("sssss", $username, $full_name, $email, $plain_password, $user_type);    
        
            if ($insert_stmt->execute()) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['user_type'] = $user_type;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['username'] = $username;

                header("Location: dashboard.php");
                exit(); 
            } else {
                $error = "Database Error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Balikbox | Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-blue-700 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-96">
        <div class="text-center mb-6">
            <i class="fas fa-box-open text-3xl text-blue-600 mb-2"></i>
            <h2 class="text-2xl font-bold text-blue-800">Join Balikbox</h2>
            <p class="text-gray-500 text-sm">Create your customer account</p>
        </div>
        
        <?php if($error) echo "<div class='bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm text-center'>$error</div>"; ?>

        <form method="POST" class="space-y-4">
            <input type="text" name="username" placeholder="Username" required 
                   value="<?php echo htmlspecialchars($username); ?>"
                   class="w-full p-3 border rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
            
            <input type="text" name="full_name" placeholder="Full Name" required 
                   value="<?php echo htmlspecialchars($full_name); ?>"
                   class="w-full p-3 border rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
            
            <input type="email" name="email" placeholder="Email Address" required 
                   value="<?php echo htmlspecialchars($email); ?>"
                   class="w-full p-3 border rounded-lg outline-none focus:ring-2 focus:ring-blue-500">

            <div class="relative">
                <input type="password" id="password" name="password" placeholder="Password" required 
                       class="w-full p-3 border rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
                <button type="button" onclick="togglePassword('password', 'eye-icon-1')" class="absolute right-3 top-3 text-gray-400 hover:text-blue-600">
                    <i class="fas fa-eye" id="eye-icon-1"></i>
                </button>
            </div>

            <div class="relative">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required 
                       class="w-full p-3 border rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
                <button type="button" onclick="togglePassword('confirm_password', 'eye-icon-2')" class="absolute right-3 top-3 text-gray-400 hover:text-blue-600">
                    <i class="fas fa-eye" id="eye-icon-2"></i>
                </button>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 transition">
                <i class="fas fa-user-plus mr-2"></i> Register
            </button>
        </form>
        <p class="mt-4 text-center text-sm text-gray-600">
            Already have an account? 
            <a href="index.php" class="text-blue-600 font-bold hover:underline">Login</a>
        </p>
    </div>

<script>
function togglePassword(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const eyeIcon = document.getElementById(iconId);
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}
</script>
</body>
</html>