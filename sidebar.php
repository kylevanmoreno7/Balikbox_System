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
        <!-- Logout button now triggers modal instead of navigating away -->
        <button onclick="document.getElementById('logoutModal').style.display='flex'"
            class="flex items-center gap-3 text-red-400 hover:bg-red-500/10 p-3 rounded-xl transition font-bold text-sm w-full text-left cursor-pointer bg-transparent border-0">
            <i class="fas fa-sign-out-alt w-5"></i> Logout
        </button>
    </div>
</aside>

<!-- ===== LOGOUT CONFIRMATION MODAL ===== -->
<div id="logoutModal"
     style="display:none; position:fixed; inset:0; z-index:9999; justify-content:center; align-items:center; backdrop-filter:blur(4px); background:rgba(0,0,0,0.45);"
     onclick="if(event.target===this) this.style.display='none'">

    <div style="background:#1e293b; border:1px solid rgba(255,255,255,0.1); border-radius:16px; padding:36px 32px 28px; width:100%; max-width:360px; text-align:center; box-shadow:0 24px 64px rgba(0,0,0,0.5); animation:logoutSlideUp 0.25s ease;">

        <!-- Icon -->
        <div style="width:60px; height:60px; background:rgba(239,68,68,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 18px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#f87171" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
            </svg>
        </div>

        <!-- Text -->
        <h2 style="font-size:1.15rem; font-weight:700; color:#f1f5f9; margin:0 0 8px; font-family:'Segoe UI',sans-serif;">Log Out</h2>
        <p style="font-size:0.875rem; color:#94a3b8; margin:0 0 28px; line-height:1.6; font-family:'Segoe UI',sans-serif;">
            Are you sure you want to log out?<br>You will be returned to the login page.
        </p>

        <!-- Buttons -->
        <div style="display:flex; gap:12px;">
            <button onclick="document.getElementById('logoutModal').style.display='none'"
                style="flex:1; padding:11px 0; border:1px solid rgba(255,255,255,0.1); border-radius:10px; font-size:0.9rem; font-weight:600; cursor:pointer; background:rgba(255,255,255,0.05); color:#cbd5e1; font-family:'Segoe UI',sans-serif; transition:background 0.15s;"
                onmouseover="this.style.background='rgba(255,255,255,0.1)'"
                onmouseout="this.style.background='rgba(255,255,255,0.05)'">
                Cancel
            </button>
            <a href="logout.php"
                style="flex:1; padding:11px 0; border:none; border-radius:10px; font-size:0.9rem; font-weight:600; cursor:pointer; background:#ef4444; color:#fff; text-decoration:none; display:inline-block; font-family:'Segoe UI',sans-serif; transition:background 0.15s;"
                onmouseover="this.style.background='#dc2626'"
                onmouseout="this.style.background='#ef4444'">
                Log Out
            </a>
        </div>
    </div>
</div>

<style>
@keyframes logoutSlideUp {
    from { transform: translateY(16px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
</style>
