<?php
if (isset($_SESSION['user_id'])) {
    $unread_count = getUnreadCount($_SESSION['user_id']);
    $notifications = getNotifications($_SESSION['user_id'], 5);
?>
<div class="relative" id="notification-dropdown">
    <button onclick="toggleNotification()" class="relative focus:outline-none">
        <i class="fas fa-bell text-white text-xl hover:text-blue-600 transition"></i>
        <?php if($unread_count > 0): ?>
            <span id="notification-badge" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5 min-w-[18px] text-center">
                <?php echo $unread_count > 9 ? '9+' : $unread_count; ?>
            </span>
        <?php else: ?>
            <span id="notification-badge" class="hidden"></span>
        <?php endif; ?>
    </button>
    
    <div id="notification-panel" class="absolute right-0 mt-3 w-80 bg-white rounded-lg shadow-xl border z-50 hidden">
        <div class="p-3 border-b bg-gray-50 rounded-t-lg flex justify-between items-center">
            <h3 class="font-bold text-gray-700">Notifications</h3>
            <?php if($unread_count > 0): ?>
                <a href="mark_all_read.php" class="text-xs text-blue-600 hover:underline">Mark all as read</a>
            <?php endif; ?>
        </div>
        
        <div class="max-h-96 overflow-y-auto">
            <?php if($notifications && $notifications->num_rows > 0): ?>
                <?php while($notif = $notifications->fetch_assoc()): ?>
                    <a href="<?php echo $notif['link'] ?? '#'; ?>" 
                       class="block p-3 border-b hover:bg-gray-50 transition <?php echo !$notif['is_read'] ? 'bg-blue-50' : ''; ?>"
                       onclick="markNotificationRead(<?php echo $notif['notification_id']; ?>)">
                        <div class="flex items-start gap-3">
                            <div class="mt-1">
                                <?php 
                                    $icons = [
                                        'shipment' => '📦',
                                        'payment' => '💰',
                                        'approval' => '✅',
                                        'delivery' => '🚚',
                                        'system' => '🔔'
                                    ];
                                    echo $icons[$notif['type']] ?? '📬';
                                ?>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-sm <?php echo !$notif['is_read'] ? 'text-gray-900' : 'text-gray-600'; ?>">
                                    <?php echo htmlspecialchars($notif['title']); ?>
                                </p>
                                <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($notif['message']); ?></p>
                                <p class="text-[10px] text-gray-400 mt-1">
                                    <?php echo time_ago($notif['created_at']); ?>
                                </p>
                            </div>
                            <?php if(!$notif['is_read']): ?>
                                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="p-8 text-center text-gray-400">
                    <i class="fas fa-bell-slash text-3xl mb-2"></i>
                    <p class="text-sm">No notifications yet</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="p-2 border-t text-center">
            <a href="all_notifications.php" class="text-xs text-blue-600 hover:underline">View all notifications</a>
        </div>
    </div>
</div>

<script>
function toggleNotification() {
    var panel = document.getElementById('notification-panel');
    if (panel.classList.contains('hidden')) {
        panel.classList.remove('hidden');
    } else {
        panel.classList.add('hidden');
    }
}

document.addEventListener('click', function(event) {
    var dropdown = document.getElementById('notification-dropdown');
    var panel = document.getElementById('notification-panel');
    
    if (dropdown && !dropdown.contains(event.target)) {
        if (panel && !panel.classList.contains('hidden')) {
            panel.classList.add('hidden');
        }
    }
});

function markNotificationRead(id) {
    fetch('mark_notification_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ notification_id: id })
    }).then(function() {
        location.reload();
    });
}
</script>
<?php } ?>