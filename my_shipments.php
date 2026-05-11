<?php
session_start();
if (!isset($_SESSION['user_id'])) { 
    header("Location: index.php"); 
    exit(); 
}
include('config.php');
include('includes/notifications.php');

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$limit = 2;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total for pagination
$total_res = $conn->query("SELECT COUNT(*) as total FROM shipments WHERE sender_id = $user_id");
$total_rows = $total_res->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);  

$shipments_stmt = $conn->prepare("
    SELECT s.*, f.company_name 
    FROM shipments s 
    LEFT JOIN forwarders f ON s.forwarder_id = f.forwarder_id 
    WHERE s.sender_id = ? 
    ORDER BY s.created_at DESC 
    LIMIT ? OFFSET ?
");

$shipments_stmt->bind_param("iii", $user_id, $limit, $offset);
$shipments_stmt->execute();
$shipments = $shipments_stmt->get_result();
$total_shipments = $shipments->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Shipments | Balikbox</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1e40af 100%);">
    
    <nav class="bg-white/10 backdrop-blur-md shadow-lg px-6 py-4 fixed w-full top-0 z-50 border-b border-white/10">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="dashboard.php" class="bg-gray-500/80 hover:bg-gray-600 text-white px-4 py-2 rounded-xl text-sm font-bold transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-box-open text-white text-xl"></i>
                    </div>
                    <span class="font-bold text-2xl text-white tracking-tight">BALIKBOX</span>
                    <span class="text-xs text-blue-200 ml-2">My Shipments</span>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <?php include('includes/notification_bell.php'); ?>
                <div class="flex items-center gap-3 bg-white/10 rounded-full px-4 py-2">
                    <i class="fas fa-user-circle text-blue-200 text-lg"></i>
                    <span class="text-sm font-medium text-white"><?php echo $username; ?></span>
                </div>
                <a href="logout.php" class="bg-red-500/80 hover:bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-bold transition">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto pt-28 px-6 pb-12">
        <div class="max-w-5xl mx-auto">
            
            <div class="flex justify-between items-center mb-8 flex-wrap gap-4">
                <div>
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mb-3 shadow-xl">
                        <i class="fas fa-boxes text-white text-2xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-white">My Shipments</h1>
                    <p class="text-blue-200 mt-1">Track and manage all your sent boxes</p>
                </div>
                <a href="send.php" class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-3 rounded-xl font-bold hover:shadow-xl transition flex items-center gap-2">
                    <i class="fas fa-plus"></i> Send New Box
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
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

            <?php if($total_shipments > 0): ?>
                <div class="space-y-5">
                    <?php while($ship = $shipments->fetch_assoc()): 
                        $status_order = ['pending_approval', 'approved', 'in_transit', 'ready_for_pickup', 'delivered'];
                        $current_status = $ship['admin_status'];
                        $current_index = array_search($current_status, $status_order);
                        if ($current_index === false) $current_index = 0;
                        
                        $timeline_steps = [
                            'pending_approval' => ['label' => 'Order placed', 'icon' => 'fa-file-invoice', 'description' => 'Your order has been received'],
                            'approved' => ['label' => 'Waiting for courier', 'icon' => 'fa-clock', 'description' => 'Admin approved, preparing for shipping'],
                            'in_transit' => ['label' => 'In transit', 'icon' => 'fa-truck', 'description' => 'Your box is on the way'],
                            'ready_for_pickup' => ['label' => 'Ready for pickup', 'icon' => 'fa-warehouse', 'description' => 'Box arrived at destination'],
                            'delivered' => ['label' => 'Order delivered', 'icon' => 'fa-home', 'description' => 'Successfully delivered to recipient']
                        ];
                        
                        if ($current_status == 'rejected') {
                            $timeline_steps = [
                                'pending_approval' => ['label' => 'Order placed', 'icon' => 'fa-file-invoice', 'description' => 'Your order has been received'],
                                'rejected' => ['label' => 'Order rejected', 'icon' => 'fa-times-circle', 'description' => $ship['admin_notes'] ?? 'Please contact support']
                            ];
                            $status_order = ['pending_approval', 'rejected'];
                            $current_index = 1;
                        }
                        
                        $order_date = date('M d, Y', strtotime($ship['created_at']));
                        $arrival_end = $ship['arrival_date'] ? date('M d, Y', strtotime($ship['arrival_date'])) : 'TBD';
                    ?>
                        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition-all">
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-5 border-b flex flex-wrap justify-between items-center">
                                <div>
                                    <span class="font-mono font-bold text-blue-600 text-lg"><?php echo $ship['tracking_no']; ?></span>
                                    <span class="ml-3 text-sm text-gray-500">Order placed <?php echo $order_date; ?></span>
                                </div>
                                <div>
                                   <?php if($current_status != 'rejected' && $current_status != 'delivered' && $ship['arrival_date']): ?>
                                    <span class="text-sm text-blue-600">
                                        <i class="fas fa-calendar-alt mr-1"></i> Estimated Delivery: <?php echo $arrival_end; ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="p-6">
                                <div class="relative">
                                    <div class="absolute left-6 top-8 bottom-8 w-0.5 bg-gray-200" style="height: calc(100% - 64px);"></div>
                                    <div class="space-y-8">
                                        <?php foreach($timeline_steps as $key => $step): 
                                            $is_completed = array_search($key, $status_order) <= $current_index;
                                            $is_current = $key == $current_status;
                                        ?>
                                            <div class="flex items-start relative">
                                                <div class="relative z-10">
                                                    <div class="w-12 h-12 rounded-full flex items-center justify-center <?php echo $is_completed ? 'bg-green-500' : 'bg-gray-300'; ?> shadow-md">
                                                        <i class="fas <?php echo $step['icon']; ?> text-white text-lg"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-5 flex-1">
                                                    <div class="flex items-center flex-wrap">
                                                        <h3 class="font-bold text-lg <?php echo $is_completed ? 'text-gray-800' : 'text-gray-400'; ?>">
                                                            <?php echo $step['label']; ?>
                                                        </h3>
                                                        <?php if($is_current && $current_status != 'rejected'): ?>
                                                            <span class="ml-3 bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full">Current</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <p class="text-sm text-gray-500 mt-1"><?php echo $step['description']; ?></p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="mt-6 pt-4 border-t grid md:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <p class="text-gray-500 text-xs uppercase font-bold">Receiver</p>
                                        <p class="font-medium"><?php echo htmlspecialchars($ship['receiver_name']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 text-xs uppercase font-bold">Box Size</p>
                                        <p class="font-medium"><?php echo $ship['box_size']; ?></p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 text-xs uppercase font-bold">Destination</p>
                                        <p class="font-medium truncate"><?php echo htmlspecialchars(substr($ship['destination'], 0, 60)); ?></p>
                                    </div>
                                </div>
                                
                                <div class="mt-4 pt-3 border-t flex gap-3">
                                    <a href="receive.php?tracking_no=<?php echo $ship['tracking_no']; ?>" 
                                       class="text-blue-600 text-sm hover:underline flex items-center gap-1">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <div class="mt-6 flex justify-center gap-2">
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="px-4 py-2 rounded-lg <?php echo $page == $i ? 'bg-blue-600 text-white' : 'bg-white/10 text-white'; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
                </div>
            <?php else: ?>
                <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl p-12 text-center">
                    <div class="w-24 h-24 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-box-open text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-600">No shipments yet</h3>
                    <p class="text-gray-400 mt-2">You haven't sent any boxes.</p>
                    <a href="send.php" class="inline-block mt-6 bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-xl font-bold hover:shadow-xl transition">
                        <i class="fas fa-paper-plane mr-2"></i> Send Your First Box
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>