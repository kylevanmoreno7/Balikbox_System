<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
include('config.php');

$user_id = $_SESSION['user_id'];
$notifications = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$notifications->bind_param("i", $user_id);
$notifications->execute();
$result = $notifications->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Notifications | Balikbox</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-md p-4 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <a href="dashboard.php" class="text-gray-500 hover:text-blue-900">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-xl font-bold text-blue-900">All Notifications</h1>
        </div>
        <div class="flex items-center gap-4">
            <?php include('includes/notification_bell.php'); ?>
            <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm">Logout</a>
        </div>
    </nav>

    <div class="container mx-auto py-10 px-4 max-w-3xl">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-blue-600 p-4 text-white flex justify-between items-center">
                <h2 class="font-bold">Notification History</h2>
                <a href="mark_all_read.php?redirect=all_notifications.php" class="text-sm underline">Mark all as read</a>
            </div>
            
            <div class="divide-y">
                <?php if($result->num_rows > 0): ?>
                    <?php while($notif = $result->fetch_assoc()): ?>
                        <div class="p-4 <?php echo !$notif['is_read'] ? 'bg-blue-50' : ''; ?>">
                            <div class="flex items-start gap-3">
                                <div class="text-2xl">
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
                                    <div class="flex justify-between items-start">
                                        <h3 class="font-bold <?php echo !$notif['is_read'] ? 'text-gray-900' : 'text-gray-600'; ?>">
                                            <?php echo htmlspecialchars($notif['title']); ?>
                                        </h3>
                                        <span class="text-xs text-gray-400">
                                            <?php echo date('M j, Y g:i A', strtotime($notif['created_at'])); ?>
                                        </span>
                                    </div>
                                    <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($notif['message']); ?></p>
                                    <?php if($notif['link']): ?>
                                        <a href="<?php echo $notif['link']; ?>" class="text-blue-600 text-sm mt-2 inline-block hover:underline">
                                            View Details →
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="p-12 text-center text-gray-400">
                        <i class="fas fa-bell-slash text-5xl mb-3"></i>
                        <p>No notifications yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>