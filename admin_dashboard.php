<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Admin') { 
    header("Location: index.php"); 
    exit(); 
}
include('config.php'); 
include('includes/notifications.php'); // Required for sidebar getUnreadCount

$pending_count = $conn->query("SELECT COUNT(*) as count FROM shipments WHERE admin_status = 'pending_approval'")->fetch_assoc()['count'];
$in_transit_count = $conn->query("SELECT COUNT(*) as count FROM shipments WHERE admin_status = 'in_transit'")->fetch_assoc()['count'];
$ready_count = $conn->query("SELECT COUNT(*) as count FROM shipments WHERE admin_status = 'ready_for_pickup'")->fetch_assoc()['count'];
$delivered_count = $conn->query("SELECT COUNT(*) as count FROM shipments WHERE admin_status = 'delivered'")->fetch_assoc()['count'];

// Pagination Logic
$p_limit = 5;
$p_page = isset($_GET['p_page']) ? (int)$_GET['p_page'] : 1;
$p_offset = ($p_page - 1) * $p_limit;
$p_total_pages = ceil($pending_count / $p_limit);

$pending_shipments = $conn->query("SELECT s.*, u.username as sender_username FROM shipments s 
LEFT JOIN users u ON s.sender_id = u.user_id WHERE s.admin_status = 'pending_approval' 
ORDER BY s.created_at DESC LIMIT $p_limit OFFSET $p_offset");

$t_limit = 5;
$t_page = isset($_GET['t_page']) ? (int)$_GET['t_page'] : 1;
$t_offset = ($t_page - 1) * $t_limit;
$t_total_pages = ceil($in_transit_count / $t_limit);

$transit_shipments = $conn->query("SELECT s.*, u.username as sender_username, f.company_name FROM shipments s 
LEFT JOIN users u ON s.sender_id = u.user_id LEFT JOIN forwarders f ON s.forwarder_id = f.forwarder_id WHERE s.admin_status = 'in_transit' 
ORDER BY s.approved_at DESC LIMIT $t_limit OFFSET $t_offset");

// Currency symbol map — reused for every shipment row
$currency_symbols = [
    'PHP' => '₱',
    'USD' => '$',
    'GBP' => '£',
    'AED' => 'د.إ',
    'JPY' => '¥'
];
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
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; }
        .bg-mesh {
            background-image: 
                radial-gradient(at 0% 0%, rgba(30, 58, 138, 0.4) 0, transparent 50%), 
                radial-gradient(at 100% 100%, rgba(88, 28, 135, 0.15) 0, transparent 50%);
        }
    </style>
</head>
<body class="min-h-screen bg-mesh text-slate-100 antialiased flex">
    
    <?php include('sidebar.php'); ?>

    <main class="flex-1 ml-72 p-8 lg:p-12">
        <div class="max-w-6xl mx-auto">
            
            <header class="flex justify-between items-center mb-10">
                <div>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight">Admin Overview</h1>
                    <p class="text-slate-400 mt-1">System status and logistics management</p>
                </div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
                <div class="bg-slate-800/40 backdrop-blur-md border border-white/10 p-6 rounded-3xl shadow-xl">
                    <i class="fas fa-clock text-yellow-500 mb-3 text-xl"></i>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Pending</p>
                    <p class="text-3xl font-bold text-white mt-1"><?php echo $pending_count; ?></p>
                </div>
                <div class="bg-slate-800/40 backdrop-blur-md border border-white/10 p-6 rounded-3xl shadow-xl">
                    <i class="fas fa-truck-fast text-blue-500 mb-3 text-xl"></i>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">In Transit</p>
                    <p class="text-3xl font-bold text-white mt-1"><?php echo $in_transit_count; ?></p>
                </div>
                <div class="bg-slate-800/40 backdrop-blur-md border border-white/10 p-6 rounded-3xl shadow-xl">
                    <i class="fas fa-warehouse text-emerald-500 mb-3 text-xl"></i>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Arrived</p>
                    <p class="text-3xl font-bold text-white mt-1"><?php echo $ready_count; ?></p>
                </div>
                <div class="bg-slate-800/40 backdrop-blur-md border border-white/10 p-6 rounded-3xl shadow-xl">
                    <i class="fas fa-check-circle text-slate-500 mb-3 text-xl"></i>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Completed</p>
                    <p class="text-3xl font-bold text-white mt-1"><?php echo $delivered_count; ?></p>
                </div>
            </div>

            <div class="mb-12">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 bg-yellow-500/20 rounded-lg flex items-center justify-center text-yellow-500">
                        <i class="fas fa-hourglass-half text-sm"></i>
                    </div>
                    <h2 class="text-xl font-bold text-white">Pending Approval</h2>
                </div>

                <div class="space-y-6">
                    <?php if($pending_count > 0): ?>
                        <?php while($row = $pending_shipments->fetch_assoc()): ?>
                            <?php $symbol = $currency_symbols[$row['currency']] ?? '₱'; ?>
                            <div class="bg-slate-800/30 backdrop-blur-md rounded-3xl border border-white/10 overflow-hidden shadow-lg hover:border-yellow-500/30 transition-all">
                                <div class="px-8 py-6 flex flex-wrap justify-between items-center gap-4">
                                    <div class="flex items-center gap-6">
                                        <div>
                                            <span class="text-[10px] font-bold text-yellow-500 uppercase tracking-widest">Tracking</span>
                                            <h3 class="text-lg font-mono font-bold text-white">#<?php echo $row['tracking_no']; ?></h3>
                                        </div>
                                        <div class="h-10 w-px bg-white/10 hidden md:block"></div>
                                        <div>
                                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Sender</span>
                                            <p class="text-sm font-semibold text-slate-200"><?php echo htmlspecialchars($row['sender_full_name']); ?></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-8">
                                        <div class="text-right">
                                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Amount</span>
                                            <p class="text-sm font-bold text-emerald-400"><?php echo $symbol . number_format($row['price'], 2); ?></p>
                                        </div>
                                        <a href="admin_approve_shipment.php?id=<?php echo $row['shipment_id']; ?>" 
                                           class="bg-white text-slate-900 px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-yellow-400 transition-all shadow-lg">
                                            Review Request
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        
                        <div class="flex justify-center gap-2 mt-4">
                            <?php for($i = 1; $i <= $p_total_pages; $i++): ?>
                                <a href="?p_page=<?php echo $i; ?>&t_page=<?php echo $t_page; ?>" 
                                   class="w-8 h-8 flex items-center justify-center rounded-lg transition-all text-xs font-bold <?php echo $p_page == $i ? 'bg-yellow-500 text-slate-900' : 'bg-slate-800 text-slate-400 hover:bg-slate-700'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-slate-800/20 border-2 border-dashed border-white/5 rounded-3xl p-10 text-center text-slate-500">
                            No shipments waiting for approval.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center text-blue-500">
                        <i class="fas fa-ship text-sm"></i>
                    </div>
                    <h2 class="text-xl font-bold text-white">Active Shipments (In Transit)</h2>
                </div>

                <div class="space-y-6">
                    <?php if($in_transit_count > 0): ?>
                        <?php while($row = $transit_shipments->fetch_assoc()): ?>
                            <div class="bg-slate-800/30 backdrop-blur-md rounded-3xl border border-white/10 overflow-hidden shadow-lg hover:border-blue-500/30 transition-all">
                                <div class="px-8 py-6">
                                    <div class="flex justify-between items-start mb-6">
                                        <div>
                                            <span class="text-[10px] font-bold text-blue-400 uppercase tracking-widest">Active Route</span>
                                            <h3 class="text-lg font-mono font-bold text-white">#<?php echo $row['tracking_no']; ?></h3>
                                        </div>
                                        <a href="admin_tracking.php?id=<?php echo $row['shipment_id']; ?>" class="text-blue-400 hover:text-white transition-colors">
                                            <i class="fas fa-edit mr-1"></i> Update Status
                                        </a>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-white/5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-slate-400">
                                                <i class="fas fa-user-tag text-xs"></i>
                                            </div>
                                            <div>
                                                <p class="text-[9px] text-slate-500 font-bold uppercase">Receiver</p>
                                                <p class="text-sm font-semibold text-slate-200"><?php echo htmlspecialchars($row['receiver_name']); ?></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-slate-400">
                                                <i class="fas fa-truck-moving text-xs"></i>
                                            </div>
                                            <div>
                                                <p class="text-[9px] text-slate-500 font-bold uppercase">Forwarder</p>
                                                <p class="text-sm font-semibold text-slate-200"><?php echo $row['company_name'] ?? 'Unassigned'; ?></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-slate-400">
                                                <i class="fas fa-map-pin text-xs"></i>
                                            </div>
                                            <div class="truncate">
                                                <p class="text-[9px] text-slate-500 font-bold uppercase">Destination</p>
                                                <p class="text-sm font-semibold text-slate-200 truncate"><?php echo htmlspecialchars($row['destination']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>

                        <div class="flex justify-center gap-2 mt-4">
                            <?php for($i = 1; $i <= $t_total_pages; $i++): ?>
                                <a href="?p_page=<?php echo $p_page; ?>&t_page=<?php echo $i; ?>" 
                                   class="w-8 h-8 flex items-center justify-center rounded-lg transition-all text-xs font-bold <?php echo $t_page == $i ? 'bg-blue-500 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-slate-800/20 border-2 border-dashed border-white/5 rounded-3xl p-10 text-center text-slate-500">
                            No shipments currently in transit.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

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
</body>
</html>
