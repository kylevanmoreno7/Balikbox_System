<?php
// sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
$role = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'Customer';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
?>

<aside class="w-72 bg-slate-800/50 backdrop-blur-xl border-r border-white/10 flex flex-col fixed h-full z-50">
    <div class="p-6 flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20">
            <i class="fas fa-box-open text-white text-xl"></i>
        </div>
        <div>
            <span class="font-extrabold text-xl text-white block tracking-tight">BALIKBOX</span>
            <span class="text-[10px] uppercase tracking-widest text-blue-400 font-bold"><?php echo $role; ?> Panel</span>
        </div>
    </div>

    <nav class="flex-1 px-4 py-6 space-y-1">
        <p class="text-[10px] font-bold text-slate-500 uppercase px-4 mb-2 tracking-widest">Navigation</p>
        
        <a href="dashboard.php" class="flex items-center gap-3 p-3 rounded-xl transition group <?php echo ($current_page == 'dashboard.php') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white'; ?>">
            <i class="fas fa-th-large w-5 text-slate-500 group-hover:text-blue-400"></i> 
            <span class="font-medium">Home</span>
        </a>

        <?php if ($role == 'Customer'): ?>
            <a href="send.php" class="flex items-center gap-3 p-3 rounded-xl transition group <?php echo ($current_page == 'send.php') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white'; ?>">
                <i class="fas fa-paper-plane w-5 text-slate-500 group-hover:text-blue-400"></i> 
                <span class="font-medium">Send a Box</span>
            </a>
            <a href="receive.php" class="flex items-center gap-3 p-3 rounded-xl transition group <?php echo ($current_page == 'receive.php') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white'; ?>">
                <i class="fas fa-search-location w-5 text-slate-500 group-hover:text-blue-400"></i> 
                <span class="font-medium">Track Package</span>
            </a>
            <a href="my_shipments.php" class="flex items-center gap-3 p-3 rounded-xl transition group <?php echo ($current_page == 'my_shipments.php') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white'; ?>">
                <i class="fas fa-boxes w-5 text-slate-500 group-hover:text-blue-400"></i> 
                <span class="font-medium">My Shipments</span>
            </a>
            <a href="all_notifications.php" class="flex items-center gap-3 p-3 rounded-xl transition group <?php echo ($current_page == 'all_notifications.php') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white'; ?>">
                <div class="relative">
                    <i class="fas fa-bell w-5 text-slate-500 group-hover:text-blue-400"></i>
                    <?php 
                    $unread = getUnreadCount($_SESSION['user_id']);
                    if($unread > 0): 
                    ?>
                        <span class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full border border-slate-800"></span>
                    <?php endif; ?>
                </div>
                <span class="font-medium">Notifications</span>
            </a>
        <?php else: ?>
            <a href="admin_dashboard.php" class="flex items-center gap-3 p-3 rounded-xl transition group <?php echo ($current_page == 'admin_dashboard.php') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white'; ?>">
                <i class="fas fa-clipboard-check w-5 text-slate-500 group-hover:text-purple-400"></i> 
                <span class="font-medium">Admin Approval</span>
            </a>
            <a href="view_all_shipments.php" class="flex items-center gap-3 p-3 rounded-xl transition group <?php echo ($current_page == 'view_all_shipments.php') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white'; ?>">
                <i class="fas fa-list-ul w-5 text-slate-500 group-hover:text-purple-400"></i> 
                <span class="font-medium">All Shipments</span>
            </a>
            <a href="manage_forwarders.php" class="flex items-center gap-3 p-3 rounded-xl transition group <?php echo ($current_page == 'manage_forwarders.php') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white'; ?>">
                <i class="fas fa-handshake w-5 text-slate-500 group-hover:text-purple-400"></i> 
                <span class="font-medium">Logistics Partners</span>
            </a>
            <a href="admin_tracking.php" class="flex items-center gap-3 p-3 rounded-xl transition group <?php echo ($current_page == 'admin_tracking.php') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white'; ?>">
                <i class="fas fa-shipping-fast w-5 text-slate-500 group-hover:text-purple-400"></i> 
                <span class="font-medium">Manage Deliveries</span>
            </a>
            <a href="all_notifications.php" class="flex items-center gap-3 p-3 rounded-xl transition group <?php echo ($current_page == 'all_notifications.php') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white'; ?>">
            <div class="relative">
                <i class="fas fa-bell w-5 text-slate-500 group-hover:text-blue-400"></i>
                <?php 
                $unread = getUnreadCount($_SESSION['user_id']);
                if($unread > 0): 
                ?>
                    <span class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full border border-slate-800"></span>
                <?php endif; ?>
            </div>
            <span class="font-medium">Notifications</span>
        </a>
        <?php endif; ?>
    </nav>

    <div class="p-4 border-t border-white/5 bg-slate-900/50">
        <div class="flex items-center gap-3 px-3 py-2 mb-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                <?php echo strtoupper(substr($username, 0, 1)); ?>
            </div>
            <div class="truncate">
                <p class="text-sm font-semibold text-white truncate"><?php echo $username; ?></p>
                <p class="text-[10px] text-slate-500 truncate">Online</p>
            </div>
        </div>
        <a href="logout.php" class="flex items-center gap-3 text-red-400 hover:bg-red-500/10 p-3 rounded-xl transition font-bold text-sm">
            <i class="fas fa-sign-out-alt w-5"></i> Logout
        </a>
    </div>
</aside>