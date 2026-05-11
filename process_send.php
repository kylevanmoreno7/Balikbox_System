<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
include('config.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sender_id = $_SESSION['user_id'];
    $sender_full_name = $_POST['sender_name'];
    $sender_email = $_POST['sender_email'];
    $receiver_name = $_POST['receiver_name'];
    $receiver_email = $_POST['receiver_email'];
    $sender_country = $_POST['s_country'];
    $currency = $_POST['currency'];
    $sender_phone = $_POST['s_country_code'] . $_POST['sender_phone'];
    $receiver_phone = $_POST['r_country_code'] . $_POST['receiver_phone'];

    // Build complete destination address with all fields from send.php
    $destination_parts = [];
    
    // Add house/building details first (most specific)
    if (isset($_POST['r_house_details']) && !empty($_POST['r_house_details'])) {
        $destination_parts[] = $_POST['r_house_details'];
    }
    
    // Add street
    if (isset($_POST['r_street']) && !empty($_POST['r_street'])) {
        $destination_parts[] = $_POST['r_street'];
    }
    
    // Add locality/barangay if exists
    if (isset($_POST['r_locality']) && !empty($_POST['r_locality'])) {
        $destination_parts[] = $_POST['r_locality'];
    }
    
    // Add city
    if (isset($_POST['r_city']) && !empty($_POST['r_city'])) {
        $destination_parts[] = $_POST['r_city'];
    }
    
    // Add province/state
    if (isset($_POST['r_province']) && !empty($_POST['r_province'])) {
        $destination_parts[] = $_POST['r_province'];
    }
    
    // Add zip code
    if (isset($_POST['r_zip']) && !empty($_POST['r_zip'])) {
        $destination_parts[] = $_POST['r_zip'];
    }
    
    // Add country
    if (isset($_POST['r_country']) && !empty($_POST['r_country'])) {
        $destination_parts[] = $_POST['r_country'];
    }
    
    $destination = implode(", ", $destination_parts);
    
    $forwarder_id = $_POST['forwarder_id'];
    $box_size = $_POST['box_size'];
    $price = $_POST['price'];
    $tracking_no = "BBOX-" . date("Y") . "-" . rand(1000, 9999);

    $stmt = $conn->prepare("INSERT INTO shipments (tracking_no, sender_id, sender_full_name, sender_email, sender_country, sender_phone, receiver_name, receiver_email, receiver_phone, forwarder_id, destination, box_size, price, currency, status, admin_status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'pending_approval')");

    $stmt->bind_param("sisssssssisssd", 
        $tracking_no, $sender_id, $sender_full_name, $sender_email, $sender_country, 
        $sender_phone, $receiver_name, $receiver_email, $receiver_phone, $forwarder_id, 
        $destination, $box_size, $price, $currency
    );

    if ($stmt->execute()) {
        $last_id = $conn->insert_id;
        header("Location: confirm_shipment.php?id=" . $last_id);
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>