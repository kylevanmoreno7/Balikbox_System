<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Admin') { 
    header("Location: index.php"); 
    exit(); 
}
include('config.php'); 

$pending_count = $conn->query("SELECT COUNT(*) as count FROM shipments WHERE admin_status = 'pending_approval'")->fetch_assoc()['count'];
$in_transit_count = $conn->query("SELECT COUNT(*) as count FROM shipments WHERE admin_status = 'in_transit'")->fetch_assoc()['count'];
$ready_count = $conn->query("SELECT COUNT(*) as count FROM shipments WHERE admin_status = 'ready_for_pickup'")->fetch_assoc()['count'];
$delivered_count = $conn->query("SELECT COUNT(*) as count FROM shipments WHERE admin_status = 'delivered'")->fetch_assoc()['count'];
$p_limit = 5;
$p_page = isset($_GET['p_page']) ? (int)$_GET['p_page'] : 1;
$p_offset = ($p_page - 1) * $p_limit;
$p_total_pages = ceil($pending_count / $p_limit);

$pending_shipments = $conn->query("SELECT s.*, u.username as sender_username FROM shipments s 
LEFT JOIN users u ON s.sender_id = u.user_id WHERE s.admin_status = 'pending_approval' 
ORDER BY s.created_at ASC LIMIT $p_limit OFFSET $p_offset");


$t_limit = 5;
$t_page = isset($_GET['t_page']) ? (int)$_GET['t_page'] : 1;
$t_offset = ($t_page - 1) * $t_limit;
$t_total_pages = ceil($in_transit_count / $t_limit);

$transit_shipments = $conn->query("SELECT s.*, u.username as sender_username, f.company_name FROM shipments s 
LEFT JOIN users u ON s.sender_id = u.user_id LEFT JOIN forwarders f ON s.forwarder_id = f.forwarder_id WHERE s.admin_status = 'in_transit' 
ORDER BY s.approved_at DESC LIMIT $t_limit OFFSET $t_offset");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Balikbox</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
    <script>
    function checkNewNotifications() {
        fetch('check_notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.unread_count > 0) {
                    const badge = document.getElementById('notification-badge');
                    if (badge) {
                        badge.innerHTML = data.unread_count > 9 ? '9+' : data.unread_count;
                        badge.classList.remove('hidden');
                    }
                }
            });
    }
    setInterval(checkNewNotifications, 30000);
    </script>
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
                    <span class="text-sm font-medium text-white">Admin</span>
                </div>
            </div>
            <div class="flex items-center gap-4">
                
    <?php include('includes/notification_bell.php'); ?>
                <div class="flex items-center gap-3 bg-white/10 rounded-full px-4 py-2">
                    <i class="fas fa-user-shield text-blue-200 text-lg"></i>
    <span class="text-sm font-medium text-white">Admin</span>
    </div>
    <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm">Logout</a>
</div>
        </div>
    </nav>

    <div class="container mx-auto pt-28 px-6 pb-12">
        <div class="max-w-7xl mx-auto">
            
            <div class="mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center mb-3 shadow-xl">
                    <i class="fas fa-tachometer-alt text-white text-2xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white">Admin Control Panel</h1>
                <p class="text-blue-200 mt-1">Manage shipments, approve orders, and track deliveries</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/10 text-center">
                    <div class="w-14 h-14 bg-yellow-500/20 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-clock text-yellow-400 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-white"><?php echo $pending_count; ?></p>
                    <p class="text-xs text-blue-200 uppercase tracking-wide mt-1">Pending Approval</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/10 text-center">
                    <div class="w-14 h-14 bg-blue-500/20 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-truck text-blue-400 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-white"><?php echo $in_transit_count; ?></p>
                    <p class="text-xs text-blue-200 uppercase tracking-wide mt-1">In Transit</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/10 text-center">
                    <div class="w-14 h-14 bg-green-500/20 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-warehouse text-green-400 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-white"><?php echo $ready_count; ?></p>
                    <p class="text-xs text-blue-200 uppercase tracking-wide mt-1">Ready for Pickup</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/10 text-center">
                    <div class="w-14 h-14 bg-gray-500/20 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-check-circle text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-white"><?php echo $delivered_count; ?></p>
                    <p class="text-xs text-blue-200 uppercase tracking-wide mt-1">Delivered</p>
                </div>
            </div>

            <!-- Pending Shipments -->
            <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl overflow-hidden mb-10">
                <div class="bg-gradient-to-r from-yellow-500 to-orange-500 p-5 text-white">
                    <h2 class="text-xl font-bold"><i class="fas fa-clock mr-2"></i> Pending Approval (<?php echo $pending_count; ?>)</h2>
                    <p class="text-yellow-100 text-sm mt-1">Shipments waiting for admin review before transport</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-bold text-gray-500 uppercase">
                                <th class="px-6 py-3">Tracking #</th>
                                <th class="px-6 py-3">Sender</th>
                                <th class="px-6 py-3">Receiver</th>
                                <th class="px-6 py-3">Box Size</th>
                                <th class="px-6 py-3">Amount</th>
                                <th class="px-6 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php while($row = $pending_shipments->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-mono font-bold text-blue-600"><?php echo $row['tracking_no']; ?></td>
                                <td class="px-6 py-4">
                                    <div class="font-medium"><?php echo htmlspecialchars($row['sender_full_name']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo $row['sender_username']; ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div><?php echo htmlspecialchars($row['receiver_name']); ?></div>
                                    <div class="text-xs text-gray-500 truncate max-w-xs"><?php echo htmlspecialchars(substr($row['destination'], 0, 50)); ?></div>
                                </td>
                                <td class="px-6 py-4"><?php echo $row['box_size']; ?></td>
                                <td class="px-6 py-4 font-bold">₱<?php echo number_format($row['price'], 2); ?></td>
                                <td class="px-6 py-4">
                                    <a href="admin_approve_shipment.php?id=<?php echo $row['shipment_id']; ?>" 
                                       class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-xl text-sm font-bold transition inline-flex items-center gap-1">
                                        <i class="fas fa-check"></i> Review
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if($pending_count == 0): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                    <i class="fas fa-check-circle text-4xl mb-2"></i>
                                    <p>No pending shipments to approve</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 flex justify-center gap-2 pb-6">
                    <?php for($i = 1; $i <= $p_total_pages; $i++): ?>
                        <a href="?p_page=<?php echo $i; ?>&t_page=<?php echo $t_page; ?>" 
                           class="px-3 py-1 rounded-lg border text-sm transition <?php echo $p_page == $i ? 'bg-blue-600 text-white border-blue-500' : 'bg-white text-gray-600 hover:bg-gray-50'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
            </div>

            <!-- In-Transit Shipments -->
            <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-5 text-white">
                    <h2 class="text-xl font-bold"><i class="fas fa-truck mr-2"></i> In Transit (<?php echo $in_transit_count; ?>)</h2>
                    <p class="text-blue-100 text-sm mt-1">Shipments currently on their way to recipients</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-bold text-gray-500 uppercase">
                                <th class="px-6 py-3">Tracking #</th>
                                <th class="px-6 py-3">Receiver</th>
                                <th class="px-6 py-3">Forwarder</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php while($row = $transit_shipments->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-mono font-bold"><?php echo $row['tracking_no']; ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($row['receiver_name']); ?></td>
                                <td class="px-6 py-4"><?php echo $row['company_name'] ?? 'Not Assigned'; ?></td>
                                <td class="px-6 py-4">
                                    <span class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-xs font-bold">
                                        <?php echo ucfirst(str_replace('_', ' ', $row['admin_status'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="admin_update_transport.php?id=<?php echo $row['shipment_id']; ?>" 
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium inline-flex items-center gap-1">
                                        <i class="fas fa-edit"></i> Update Status
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if($in_transit_count == 0): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                    <i class="fas fa-truck text-4xl mb-2"></i>
                                    <p>No shipments in transit</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 flex justify-center gap-2 pb-6">
                    <?php for($i = 1; $i <= $t_total_pages; $i++): ?>
                        <a href="?p_page=<?php echo $p_page; ?>&t_page=<?php echo $i; ?>" 
                           class="px-3 py-1 rounded-lg border text-sm transition <?php echo $t_page == $i ? 'bg-blue-600 text-white border-blue-500' : 'bg-white text-gray-600 hover:bg-gray-50'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
            </div>
        </div>
    </div>
</body>
</html>