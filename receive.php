<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
include('config.php'); 

$shipment = null;
$error = "";

if (isset($_GET['tracking_no'])) {
    $tracking = $_GET['tracking_no'];
    
    $stmt = $conn->prepare("SELECT s.*, f.company_name 
                            FROM shipments s 
                            JOIN forwarders f ON s.forwarder_id = f.forwarder_id 
                            WHERE s.tracking_no = ?");
    $stmt->bind_param("s", $tracking);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $shipment = $result->fetch_assoc();
    } else {
        $error = "No shipment found with that tracking number.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Track Package | Balikbox</title>
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
                    <span class="text-xs text-blue-200 ml-2">Track Package</span>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <?php include('includes/notification_bell.php'); ?>
                <div class="flex items-center gap-3 bg-white/10 rounded-full px-4 py-2">
                    <i class="fas fa-user-circle text-blue-200 text-lg"></i>
                    <span class="text-sm font-medium text-white"><?php echo $_SESSION['username']; ?></span>
                </div>
                <a href="logout.php" class="bg-red-500/80 hover:bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-bold transition">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto pt-28 px-6 pb-12">
        <div class="max-w-2xl mx-auto">
            
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl">
                    <i class="fas fa-search text-white text-3xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white">Track Your Package</h1>
                <p class="text-blue-200 mt-2">Enter your tracking number to check shipment status</p>
            </div>

            <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl p-6 mb-8">
                <form method="GET" class="flex gap-3">
                    <input type="text" name="tracking_no" placeholder="Enter Tracking Number (e.g., BBOX-2024-1234)" 
                           class="flex-1 p-4 rounded-xl border-2 border-gray-200 focus:border-blue-500 outline-none text-lg"
                           required>
                    <button type="submit" class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 rounded-xl font-bold hover:shadow-lg transition">
                        <i class="fas fa-truck mr-2"></i> TRACK
                    </button>
                </form>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($shipment): ?>
                <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-yellow-500 to-orange-500 p-6 text-white">
                        <div class="flex justify-between items-center flex-wrap gap-4">
                            <div>
                                <p class="text-sm opacity-80 uppercase font-bold">Tracking Number</p>
                                <h2 class="text-2xl font-black font-mono"><?php echo $shipment['tracking_no']; ?></h2>
                            </div>
                            <div class="text-right">
                                <p class="text-sm opacity-80 uppercase font-bold">Current Status</p>
                                <span class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full font-bold text-sm inline-block">
                                    <?php 
                                        $status_map = [
                                            'pending_approval' => 'Pending Approval',
                                            'approved' => 'Approved',
                                            'rejected' => 'Rejected',
                                            'in_transit' => 'In Transit',
                                            'ready_for_pickup' => 'Ready for Pickup',
                                            'delivered' => 'Delivered'
                                        ];
                                        echo $status_map[$shipment['admin_status']] ?? $shipment['status'];
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <p class="text-gray-500 text-xs font-bold uppercase">Receiver</p>
                                <p class="font-bold text-lg"><?php echo $shipment['receiver_name']; ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-xs font-bold uppercase">Forwarder</p>
                                <p class="font-bold text-lg"><?php echo $shipment['company_name']; ?></p>
                            </div>
                        </div>
                        
                        <div>
                            <p class="text-gray-500 text-xs font-bold uppercase">Complete Delivery Address</p>
                            <p class="text-gray-700 leading-relaxed mt-1"><?php echo nl2br(htmlspecialchars($shipment['destination'])); ?></p>
                        </div>
                        
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-4 rounded-xl flex justify-between items-center">
                            <span class="text-gray-600 font-medium">Estimated Arrival:</span>
                            <span class="font-bold text-blue-700 text-lg"><?php echo $shipment['arrival_date'] ?? 'Processing...'; ?></span>
                        </div>
                        
                        <div class="bg-blue-50 p-4 rounded-xl text-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            <span class="text-sm text-gray-600">For more details, visit your 
                                <a href="my_shipments.php" class="text-blue-600 font-bold hover:underline">My Shipments</a> page.
                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>