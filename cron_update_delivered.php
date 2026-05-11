<?php
// File: cron_update_delivered.php
// I-run ni kada adlaw (via cron job or manually)
include('config.php');
include('includes/notifications.php');

// Kuhaon ang tanang shipments nga ang arrival_date kay <= today pero wala pa na-deliver
$today = date('Y-m-d');
$update_stmt = $conn->prepare("
    SELECT shipment_id, tracking_no, sender_id, receiver_name 
    FROM shipments 
    WHERE arrival_date <= ? 
    AND admin_status = 'in_transit'
");
$update_stmt->bind_param("s", $today);
$update_stmt->execute();
$shipments = $update_stmt->get_result();

$count = 0;
while($ship = $shipments->fetch_assoc()) {
    // I-update ang status to delivered
    $update = $conn->prepare("UPDATE shipments SET admin_status = 'delivered' WHERE shipment_id = ?");
    $update->bind_param("i", $ship['shipment_id']);
    
    if ($update->execute()) {
        // Notify the customer
        notifyUser($ship['sender_id'],
            '📦 Shipment Delivered!',
            'Your shipment ' . $ship['tracking_no'] . ' has been delivered to ' . $ship['receiver_name'] . ' on ' . date('F j, Y'),
            'delivery',
            'receive.php?tracking_no=' . $ship['tracking_no']
        );
        $count++;
    }
}

echo "✅ $count shipment(s) marked as delivered.\n";
?>