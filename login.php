<?php
include('config.php');

// Get the user role from the URL, default to 'Customer'
$role = isset($_GET['role']) ? $_GET['role'] : 'Customer';

// Pre-define error variable
$error = null;

// Handle Login Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $user_role = $_POST['user_role'];

    // Updated Query to verify password AND user_type
    $stmt = $conn->prepare("SELECT user_id, username, password_hash, user_type FROM users WHERE username = ? AND user_type = ?");
    $stmt->bind_param("ss", $username, $user_role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // Use password_verify for security, or simple check if using your create_admin.php logic
        if ($password == $user['password_hash'] || password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            
            echo "<script>window.location.href='dashboard.php';</script>";
            exit();
        }
    }
    $error = "Invalid credentials for $user_role.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo $role; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
</head>
<body class="bg-[#1e2a4a] flex items-center justify-center min-h-screen"> <div class="bg-[#2a3a5f] p-8 rounded-lg shadow-2xl w-full max-w-md"> <div class="text-center mb-6">
            <div class="w-16 h-16 <?php echo $role == 'Admin' ? 'bg-red-100' : 'bg-blue-100'; ?> rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas <?php echo $role == 'Admin' ? 'fa-user-shield text-red-600' : 'fa-user text-blue-600'; ?> text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-100">Login as <?php echo $role; ?></h2>
            <p class="text-gray-400 text-sm">Please enter your credentials</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 mb-4 text-xs">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-4">
            <input type="hidden" name="user_role" value="<?php echo $role; ?>">
            
            <div>
                <label class="block text-gray-300 text-xs font-bold mb-1 uppercase">Username</label>
                <input type="text" name="username" required class="w-full px-4 py-2 bg-[#1a253a] border border-[#3e4a6f] text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div class="relative"> 
                <label class="block text-gray-300 text-xs font-bold mb-1 uppercase">Password</label>
                <div class="relative flex items-center">
                    <input type="password" id="password" name="password" required 
                        class="w-full px-4 py-2 bg-[#1a253a] border border-[#3e4a6f] text-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none pr-12">
                    
                    <button type="button" 
                            onclick="togglePassword()" 
                            class="absolute right-0 top-0 h-full w-10 flex items-center justify-center text-gray-400 hover:text-white z-20 cursor-pointer focus:outline-none">
                        <i id="toggleIcon" class="fas fa-eye-slash pointer-events-none"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full <?php echo $role == 'Admin' ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'; ?> text-white font-bold py-3 rounded-lg transition mt-2">
                Sign In
            </button>
        </form>

        <div class="mt-6 text-center border-t border-[#3e4a6f] pt-4">
            <p class="text-sm text-gray-300">
                <?php if($role == 'Customer'): ?>
                    Don't have an account? <a href="register.php?role=Customer" class="text-blue-400 font-bold hover:underline">Register here</a>
                <?php else: ?>
                    Contact management for Admin registration.
                <?php endif; ?>
            </p>
        </div>
    </div>
</body>
</html>
