<?php
session_start();
include('config.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, username, password_hash, user_type FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // Warning: This plaintext comparison is highly insecure. Secure password hashing should be implemented.
        if ($password == $user['password_hash']) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            header("Location: dashboard.php");
            exit();
        }
    }
    $error = "Invalid credentials.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Balikbox | Ship with Confidence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
   <style>
    html {
        scroll-behavior: smooth;
    }   

    @layer utilities {
        .nav-gap {
            gap: 5rem; 
        }
    }
    
    /* Automated Hover Logic */
    .group:hover .group-hover\:flex {
        display: flex !important;
    }

    /* "Stay Open" Logic when clicked */
    .menu-active {
        display: flex !important;
    }
    
    /* Rotate arrow when active */
    .rotate-180 {
        transform: rotate(180deg);
    }
</style>
</head>
<body class="min-h-screen" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1e40af 100%);">
    <nav class="bg-white/10 backdrop-blur-md shadow-lg px-6 py-4 fixed w-full top-0 z-50 border-b border-white/10">
    <div class="container mx-auto flex justify-between items-center">
        <div class="flex items-center gap-2">
            <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center shadow-lg">
                <a href="index.php" class="fas fa-box-open text-white text-xl"></a>
            </div>
            <a href="index.php" class="font-bold text-2xl text-white tracking-tight">BALIKBOX</a>
            <span class="text-xs text-blue-200 ml-2">Logistics</span>
        </div>
        
        <div class="hidden md:flex nav-gap">
            <a href="index.php" class="text-white hover:text-blue-300 font-medium">Home</a>
            <a href="track_view.php" class="text-white hover:text-blue-300 font-medium">Track</a>
            <a href="#about" class="text-white hover:text-blue-300 font-medium">About Us</a>
            
            <div class="relative group">
                <button onclick="toggleHelpMenu(event)" class="text-white hover:text-blue-300 font-medium flex items-center gap-1.5 focus:outline-none">
                    Help
                    <i id="helpArrow" class="fas fa-chevron-down text-xs opacity-70 mt-0.5 transition-transform duration-200"></i>
                </button>
                <div id="helpDropdown" class="absolute left-0 top-full mt-2 w-48 bg-white/10 backdrop-blur-md rounded-xl shadow-xl border border-white/10 py-2 group-hover:flex hidden flex-col z-[110]">
                    <a href="#" class="px-5 py-2 text-white hover:bg-white/10 text-sm">FAQs</a>
                    <a href="#" class="px-5 py-2 text-white hover:bg-white/10 text-sm">Contact Support</a>
                    <a href="#" class="px-5 py-2 text-white hover:bg-white/10 text-sm">Shipping Guide</a>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-1 text-sm font-bold text-white pr-10">
            <a href="register.php" class="hover:text-blue-300 transition">Sign Up</a>
            <span class="opacity-50">/</span>
            <button onclick="openLogin('Customer')" class="hover:text-blue-300 transition">Login</button>
        </div>
    </div>
</nav>

    <div class="relative pt-20 md:pt-16 overflow-hidden h-[70vh] flex flex-col justify-center items-center text-center text-white" 
     style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1e40af 100%);">
        <div class="relative z-10 container mx-auto px-6">
            <i class="fas fa-box-open text-6xl mt-4 mb-5 shadow-lg"></i>
            <h1 class="text-5xl font-bold mb-4 drop-shadow-lg">“Send Your Balikbayan Box with Ease”</h1>
            <p class="text-xl pt-6 mb-6 opacity-90 drop-shadow-md">Login As:</p>
            
            <div class="flex flex-col md:flex-row gap-6 justify-center items-center">
                <button onclick="openLogin('Customer')" class="group bg-white/10 backdrop-blur-md border border-white/20 p-3 rounded-2xl w-36 transition-all hover:bg-blue-600 shadow-xl">
                    <i class="fas fa-user text-3xl mb-3"></i>
                    <h3 class="text-xl font-bold">Customer</h3>
                </button>
                <button onclick="openLogin('Admin')" class="group bg-white/10 backdrop-blur-md border border-white/20 p-3 rounded-2xl w-36 transition-all hover:bg-red-600 shadow-xl">
                    <i class="fas fa-user-shield text-3xl mb-3"></i>
                    <h3 class="text-xl font-bold">Admin</h3>
                </button>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-6 pt-16 pb-4">
        <div class="grid md:grid-cols-3 gap-8">
            <div class="text-center p-6 bg-blue-50 rounded-xl shadow-lg">
                <i class="fas fa-shipping-fast text-4xl text-blue-600 mb-4"></i>
                <h3 class="text-xl font-bold mb-2">Fast Delivery</h3>
                <p class="text-gray-600">15-30 days delivery time worldwide</p>
            </div>
            <div class="text-center p-6 bg-blue-50 rounded-xl shadow-lg">
                <i class="fas fa-map-marker-alt text-4xl text-blue-600 mb-4"></i>
                <h3 class="text-xl font-bold mb-2">Real-time Tracking</h3>
                <p class="text-gray-600">Track your box from pickup to delivery</p>
            </div>
            <div class="text-center p-6 bg-blue-50 rounded-xl shadow-lg">
                <i class="fas fa-shield-alt text-4xl text-blue-600 mb-4"></i>
                <h3 class="text-xl font-bold mb-2">Secure & Insured</h3>
                <p class="text-gray-600">Up to ₱10,000 insurance coverage</p>
            </div>
        </div>
    </div>

    <div id="about" class="bg-blue-50 py-20 mt-16 scroll-mt-24">
        <div class="container mx-auto px-6 max-w-4xl">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-history text-3xl text-blue-600"></i>
                </div>
                <h2 class="text-4xl font-bold text-gray-900">About Balikbox Logistics</h2>
            </div>
            <p class="text-lg text-gray-700 leading-relaxed mb-6">
                We created Balikbox Logistics with a single mission: to simplify the process of sending care packages to families in the Philippines. We understand that every Balikbayan box is more than just cargo; it represents a tangible connection to loved ones back home.
            </p>
            <p class="text-lg text-gray-700 leading-relaxed mb-6">
                Our network combines modern logistics technology with local care, ensuring that your package is handled with respect and delivered on time, every time.
            </p>
            <div class="mt-12 bg-blue-100 p-8 rounded-2xl shadow-md border border-gray-100">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Our Commitment</h3>
                <ul class="list-disc list-inside text-gray-700 space-y-3">
                    <li>Transparent Pricing: No hidden fees, ever.</li>
                    <li>End-to-End Tracking: Full visibility from pickup to delivery.</li>
                    <li>Global Reach: Serving Filipino communities worldwide.</li>
                    <li>Dedicated Support: Assistance in both English and Tagalog.</li>
                </ul>
            </div>
        </div>
    </div>

    <div id="loginModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/60 backdrop-blur-sm px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden relative">
            <button onclick="closeLogin()" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div id="loginContent">
                </div>
        </div>
    </div>

    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-6 text-center">
            <p class="text-sm">&copy; 2024 Balikbox Logistics. All rights reserved.</p>
            <p class="text-xs text-gray-400 mt-2">Delivering smiles to the Philippines since 2020</p>
        </div>
    </footer>

    <script>
        function toggleHelpMenu(event) {
    // Prevent the click from immediately bubbling up to the window
    event.stopPropagation();
    
    const dropdown = document.getElementById('helpDropdown');
    const arrow = document.getElementById('helpArrow');
    
    // Toggle the 'menu-active' class which overrides the 'hidden' state
    dropdown.classList.toggle('menu-active');
    arrow.classList.toggle('rotate-180');
}

// Close the menu if you click anywhere else on the screen
window.addEventListener('click', function() {
    const dropdown = document.getElementById('helpDropdown');
    const arrow = document.getElementById('helpArrow');
    
    if (dropdown.classList.contains('menu-active')) {
        dropdown.classList.remove('menu-active');
        arrow.classList.remove('rotate-180');
    }
});

    function openLogin(role) {
        const modal = document.getElementById('loginModal');
        const content = document.getElementById('loginContent');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        fetch(`login.php?role=${role}`)
            .then(response => response.text())
            .then(data => {
                content.innerHTML = data;
            });
    }

    function closeLogin() {
        const modal = document.getElementById('loginModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    window.onclick = function(event) {
        const modal = document.getElementById('loginModal');
        if (event.target == modal) closeLogin();
    }
    </script>
</body>
</html>