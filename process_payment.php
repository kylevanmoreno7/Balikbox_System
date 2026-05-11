<?php
session_start();
include('config.php'); 

if (!isset($_SESSION['user_id'])) { 
    header("Location: index.php"); 
    exit(); 
}

if (isset($_POST['execute_payment'])) {
    $s_id = mysqli_real_escape_string($conn, $_POST['shipment_id']);
    $user_id = $_SESSION['user_id'];
    
    // Get shipment details for notification
    $ship_query = $conn->prepare("SELECT tracking_no, sender_full_name, receiver_name, box_size, price FROM shipments WHERE shipment_id = ?");
    $ship_query->bind_param("i", $s_id);
    $ship_query->execute();
    $shipment = $ship_query->get_result()->fetch_assoc();
    
    // Update both status and admin_status
    $sql = "UPDATE shipments SET status = 'Paid', admin_status = 'pending_approval', payment_date = NOW() WHERE shipment_id = '$s_id'";

    if (mysqli_query($conn, $sql)) {
        // Create notification for the user (customer)
        notifyUser($user_id, 
            'Payment Successful!', 
            'Your payment of ₱' . number_format($shipment['price'], 2) . ' for shipment ' . $shipment['tracking_no'] . ' has been received. Your box is now waiting for admin approval.',
            'payment',
            'receive.php?tracking_no=' . $shipment['tracking_no']
        );
        
        // Notify ALL admins about new shipment
        notifyAllAdmins(
            '📦 New Shipment Pending Approval',
            'A new shipment (Tracking: ' . $shipment['tracking_no'] . ') from ' . $shipment['sender_full_name'] . ' needs your approval. Box size: ' . $shipment['box_size'] . ', Amount: ₱' . number_format($shipment['price'], 2),
            'shipment',
            'admin_approve_shipment.php?id=' . $s_id
        );
        
        header("Location: receipt.php?id=" . $s_id . "&success=1");
        exit();
    } else {
        die("Database Error: " . mysqli_error($conn));
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>