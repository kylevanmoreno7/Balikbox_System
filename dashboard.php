<?php
session_start();
if (!isset($_SESSION['user_id'])) { 
    header("Location: index.php"); 
    exit(); 
}
include('config.php');
include('includes/notifications.php');

// Get user role from session
$role = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'Customer';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Balikbox | Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="min-h-screen" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1e40af 100%);">
    
    <!-- Navigation - Lahi ang design depende sa role -->
    <nav class="bg-white/10 backdrop-blur-md shadow-lg px-6 py-4 fixed w-full top-0 z-50 border-b border-white/10">
        <div class="container mx-auto flex justify-between items-center">
            
            <!-- Logo Section - Mag depende sa role -->
            <div class="flex items-center gap-2">
                <?php if ($role == 'Admin'): ?>
                    <!-- ADMIN LOGO: Shield icon -->
                    <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-box-open text-white text-xl"></i>
                    </div>
                    <span class="font-bold text-2xl text-white tracking-tight">BALIKBOX</span>
                    <span class="text-xs text-purple-200 ml-2">Admin Portal</span>
                <?php else: ?>
                    <!-- CUSTOMER LOGO: Box icon -->
                    <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-box-open text-white text-xl"></i>
                    </div>
                    <span class="font-bold text-2xl text-white tracking-tight">BALIKBOX</span>
                    <span class="text-xs text-blue-200 ml-2">Logistics</span>
                <?php endif; ?>
            </div>
            
            <!-- Right Side - User Info -->
            <div class="flex items-center gap-4">
                <?php include('includes/notification_bell.php'); ?>
                
                <?php if ($role == 'Admin'): ?>
                    <!-- ADMIN DISPLAY: Shield + "Admin" text -->
                    <div class="flex items-center gap-3 bg-white/10 rounded-full px-4 py-2">
                        <i class="fas fa-user-shield text-purple-200 text-lg"></i>
                        <span class="text-sm font-medium text-white">Admin</span>
                    </div>
                <?php else: ?>
                    <!-- CUSTOMER DISPLAY: User circle + username -->
                    <div class="flex items-center gap-3 bg-white/10 rounded-full px-4 py-2">
                        <i class="fas fa-user-circle text-blue-200 text-lg"></i>
                        <span class="text-sm font-medium text-white"><?php echo $username; ?></span>
                    </div>
                <?php endif; ?>
                
                <a href="logout.php" class="bg-red-500/80 hover:bg-red-600 text-white px-5 py-2 rounded-xl text-sm font-bold transition shadow-lg backdrop-blur-sm">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto pt-28 px-6 pb-12">
        <div class="max-w-6xl mx-auto">
            
            <!-- Welcome Banner - Mag depende sa role -->
            <div class="bg-gradient-to-r from-blue-600/30 to-purple-600/30 backdrop-blur-sm rounded-2xl p-6 mb-10 border border-white/20">
                <div class="flex items-center gap-4">
                    <?php if ($role == 'Admin'): ?>
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center shadow-xl">
                            <i class="fas fa-shield-alt text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-white">Welcome back, Admin! 👋</h1>
                            <p class="text-purple-100 text-sm">Manage shipments, forwarders, and track deliveries.</p>
                        </div>
                    <?php else: ?>
                        <div class="w-16 h-16 bg-blue-500 rounded-2xl flex items-center justify-center shadow-xl">
                            <i class="fas fa-box-open text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-white">Welcome back, <?php echo $username; ?>! 👋</h1>
                            <p class="text-blue-100 text-sm">Manage your shipments and track your boxes easily.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- CUSTOMER VIEW -->
            <?php if ($role == 'Customer'): 
                $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM shipments WHERE sender_id = ?");
                $count_stmt->bind_param("i", $user_id);
                $count_stmt->execute();
                $total_shipments = $count_stmt->get_result()->fetch_assoc()['total'];
            ?>
                <div class="grid md:grid-cols-3 gap-6 mb-10">
                    <a href="send.php" class="bg-white/95 backdrop-blur-sm p-6 rounded-2xl shadow-xl card-hover group border border-white/20">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-paper-plane text-white text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-1">Send a Box</h3>
                        <p class="text-gray-500 text-sm">Ship items to your family in the Philippines</p>
                        <div class="mt-4 flex items-center text-blue-600 text-sm font-semibold group-hover:translate-x-1 transition-transform">
                            Get Started <i class="fas fa-arrow-right ml-2"></i>
                        </div>
                    </a>

                    <a href="receive.php" class="bg-white/95 backdrop-blur-sm p-6 rounded-2xl shadow-xl card-hover group border border-white/20">
                        <div class="w-16 h-16 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-2xl flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-map-marker-alt text-white text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-1">Track Package</h3>
                        <p class="text-gray-500 text-sm">Track your box by tracking number</p>
                        <div class="mt-4 flex items-center text-yellow-600 text-sm font-semibold group-hover:translate-x-1 transition-transform">
                            Track Now <i class="fas fa-arrow-right ml-2"></i>
                        </div>
                    </a>

                    <a href="my_shipments.php" class="bg-white/95 backdrop-blur-sm p-6 rounded-2xl shadow-xl card-hover group border border-white/20">
                        <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-boxes text-white text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-1">My Shipments</h3>
                        <p class="text-gray-500 text-sm">View all your sent boxes</p>
                        <div class="mt-4 flex items-center text-green-600 text-sm font-semibold group-hover:translate-x-1 transition-transform">
                            View All 
                            <?php if($total_shipments > 0): ?>
                                <span class="ml-2 bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full"><?php echo $total_shipments; ?></span>
                            <?php endif; ?>
                            <i class="fas fa-arrow-right ml-2"></i>
                        </div>
                    </a>
                </div>

                <!-- Stats Row -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/10">
                        <p class="text-2xl font-bold text-white"><?php echo $total_shipments; ?></p>
                        <p class="text-xs text-blue-200 uppercase tracking-wide">Total Shipments</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/10">
                        <p class="text-2xl font-bold text-white"><?php 
                            $delivered = $conn->prepare("SELECT COUNT(*) as t FROM shipments WHERE sender_id = ? AND admin_status = 'delivered'");
                            $delivered->bind_param("i", $user_id);
                            $delivered->execute();
                            echo $delivered->get_result()->fetch_assoc()['t'];
                        ?></p>
                        <p class="text-xs text-blue-200 uppercase tracking-wide">Delivered</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/10">
                        <p class="text-2xl font-bold text-white"><?php 
                            $in_transit = $conn->prepare("SELECT COUNT(*) as t FROM shipments WHERE sender_id = ? AND admin_status = 'in_transit'");
                            $in_transit->bind_param("i", $user_id);
                            $in_transit->execute();
                            echo $in_transit->get_result()->fetch_assoc()['t'];
                        ?></p>
                        <p class="text-xs text-blue-200 uppercase tracking-wide">In Transit</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/10">
                        <p class="text-2xl font-bold text-white"><?php 
                            $pending = $conn->prepare("SELECT COUNT(*) as t FROM shipments WHERE sender_id = ? AND admin_status = 'pending_approval'");
                            $pending->bind_param("i", $user_id);
                            $pending->execute();
                            echo $pending->get_result()->fetch_assoc()['t'];
                        ?></p>
                        <p class="text-xs text-blue-200 uppercase tracking-wide">Pending</p>
                    </div>
                </div>

                <!-- Recent Shipments for Customer -->
                <?php 
                $recent_stmt = $conn->prepare("SELECT tracking_no, receiver_name, admin_status, created_at, arrival_date FROM shipments WHERE sender_id = ? ORDER BY created_at DESC LIMIT 5");
                $recent_stmt->bind_param("i", $user_id);
                $recent_stmt->execute();
                $recent_shipments = $recent_stmt->get_result();
                ?>
                
                <?php if($recent_shipments->num_rows > 0): ?>
                <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl overflow-hidden border border-white/20">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h3 class="font-bold text-gray-800 flex items-center">
                            <i class="fas fa-history mr-2 text-blue-600"></i> Recent Shipments
                        </h3>
                        <a href="my_shipments.php" class="text-sm font-semibold text-blue-600 hover:text-blue-800 transition">View All <i class="fas fa-chevron-right ml-1"></i></a>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <?php while($ship = $recent_shipments->fetch_assoc()): 
                            $status_class = 'bg-gray-100 text-gray-700';
                            $status_text = ucfirst(str_replace('_', ' ', $ship['admin_status']));
                            if($ship['admin_status'] == 'pending_approval') {
                                $status_class = 'bg-yellow-100 text-yellow-700';
                                $status_text = 'Pending Approval';
                            } elseif($ship['admin_status'] == 'approved') {
                                $status_class = 'bg-blue-100 text-blue-700';
                                $status_text = 'Approved';
                            } elseif($ship['admin_status'] == 'in_transit') {
                                $status_class = 'bg-indigo-100 text-indigo-700';
                                $status_text = 'In Transit';
                            } elseif($ship['admin_status'] == 'delivered') {
                                $status_class = 'bg-green-100 text-green-700';
                                $status_text = 'Delivered';
                            }
                        ?>
                            <div class="px-6 py-4 flex justify-between items-center hover:bg-blue-50/30 transition">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-box text-blue-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="font-mono font-bold text-blue-600 text-sm"><?php echo $ship['tracking_no']; ?></p>
                                        <p class="text-sm text-gray-600">To: <?php echo htmlspecialchars($ship['receiver_name']); ?></p>
                                        <p class="text-xs text-gray-400"><?php echo date('M d, Y', strtotime($ship['created_at'])); ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                    <?php if($ship['arrival_date'] && $ship['admin_status'] == 'in_transit'): ?>
                                        <p class="text-xs text-gray-400 mt-1">Est: <?php echo date('M d', strtotime($ship['arrival_date'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>

            <!-- ADMIN VIEW -->
            <?php elseif ($role == 'Admin'): 
                $pending_res = $conn->query("SELECT COUNT(*) as total FROM shipments WHERE admin_status = 'pending_approval'");
                $pending_count = $pending_res->fetch_assoc()['total'];
            ?>
                <h2 class="text-3xl font-bold text-white mb-2">Administrator Control Center</h2>
                <p class="text-blue-200 mb-8">Manage shipments, forwarders, and track deliveries</p>
                
                <div class="grid md:grid-cols-4 gap-6 mb-10">
                    <a href="admin_dashboard.php" class="bg-white/95 backdrop-blur-sm p-6 rounded-2xl shadow-xl card-hover group border border-white/20">
                        <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-indigo-600 transition-colors">
                            <i class="fas fa-tasks text-2xl text-indigo-600 group-hover:text-white transition-colors"></i>
                        </div>
                        <h4 class="font-bold text-gray-800">Admin Approval</h4>
                        <p class="text-xs text-gray-500 mt-1">Review pending boxes</p>
                        <?php if($pending_count > 0): ?>
                            <span class="inline-block mt-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full"><?php echo $pending_count; ?> pending</span>
                        <?php endif; ?>
                    </a>

                    <a href="view_all_shipments.php" class="bg-white/95 backdrop-blur-sm p-6 rounded-2xl shadow-xl card-hover group border border-white/20">
                        <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-blue-600 transition-colors">
                            <i class="fas fa-truck text-2xl text-blue-600 group-hover:text-white transition-colors"></i>
                        </div>
                        <h4 class="font-bold text-gray-800">All Shipments</h4>
                        <p class="text-xs text-gray-500 mt-1">Manage every box in system</p>
                    </a>

                    <a href="manage_forwarders.php" class="bg-white/95 backdrop-blur-sm p-6 rounded-2xl shadow-xl card-hover group border border-white/20">
                        <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-green-600 transition-colors">
                            <i class="fas fa-building text-2xl text-green-600 group-hover:text-white transition-colors"></i>
                        </div>
                        <h4 class="font-bold text-gray-800">Logistics Partners</h4>
                        <p class="text-xs text-gray-500 mt-1">Manage courier list</p>
                    </a>

                    <a href="admin_tracking.php" class="bg-white/95 backdrop-blur-sm p-6 rounded-2xl shadow-xl card-hover group border border-white/20">
                        <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-purple-600 transition-colors">
                            <i class="fas fa-calendar-check text-2xl text-purple-600 group-hover:text-white transition-colors"></i>
                        </div>
                        <h4 class="font-bold text-gray-800">Manage Deliveries</h4>
                        <p class="text-xs text-gray-500 mt-1">Update ETA & statuses</p>
                    </a>
                </div>

                <!-- Admin Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/10">
                        <p class="text-2xl font-bold text-white"><?php echo $pending_count; ?></p>
                        <p class="text-xs text-blue-200 uppercase tracking-wide">Pending Approval</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/10">
                        <p class="text-2xl font-bold text-white"><?php 
                            $in_transit = $conn->query("SELECT COUNT(*) as t FROM shipments WHERE admin_status = 'in_transit'")->fetch_assoc()['t'];
                            echo $in_transit;
                        ?></p>
                        <p class="text-xs text-blue-200 uppercase tracking-wide">In Transit</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/10">
                        <p class="text-2xl font-bold text-white"><?php 
                            $delivered = $conn->query("SELECT COUNT(*) as t FROM shipments WHERE admin_status = 'delivered'")->fetch_assoc()['t'];
                            echo $delivered;
                        ?></p>
                        <p class="text-xs text-blue-200 uppercase tracking-wide">Delivered</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center border border-white/10">
                        <p class="text-2xl font-bold text-white"><?php 
                            $users = $conn->query("SELECT COUNT(*) as t FROM users")->fetch_assoc()['t'];
                            echo $users;
                        ?></p>
                        <p class="text-xs text-blue-200 uppercase tracking-wide">Total Users</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>