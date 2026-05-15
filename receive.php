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
        /* Custom scrollbar for dark theme */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    </style>
</head>
<body class="min-h-screen bg-slate-900 flex text-slate-100">

    <?php include('sidebar.php'); ?>

    <div class="flex-1 ml-72 pt-20 px-8 pb-12">
        <div class="max-w-4xl mx-auto">
            
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl">
                    <i class="fas fa-search text-white text-3xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white">Track Your Package</h1>
                <p class="text-blue-200 mt-2">Enter your tracking number to check shipment status</p>
            </div>

            <div class="bg-gray-900 backdrop-blur-md rounded-2xl shadow-2xl p-6 mb-8 border border-white/10">
                <form method="GET" class="flex gap-3">
                    <div class="relative flex-1">
                        <i class="fas fa-hashtag absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="text" name="tracking_no" 
                               placeholder="Enter Tracking Number (e.g., BBOX-2024-1234)" 
                               class="w-full pl-12 p-4 rounded-xl border border-white/10 bg-slate-900/50 text-white placeholder-slate-500 focus:ring-2 focus:ring-yellow-500 outline-none text-lg transition-all"
                               required>
                    </div>
                    <button type="submit" class="bg-gradient-to-r from-yellow-500 to-orange-600 text-white px-8 rounded-xl font-bold hover:shadow-[0_0_20px_rgba(245,158,11,0.3)] transition-all">
                        <i class="fas fa-truck mr-2"></i> TRACK
                    </button>
                </form>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-500/10 backdrop-blur-md border border-red-500/50 text-red-200 p-4 rounded-xl mb-8 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-xl"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($shipment): ?>
                <div class="bg-slate-800/60 backdrop-blur-md rounded-2xl shadow-2xl overflow-hidden border border-white/10">
                    <div class="bg-gradient-to-r from-yellow-500 to-orange-500 p-6 text-white">
                        <div class="flex justify-between items-center flex-wrap gap-4">
                            <div>
                                <p class="text-sm opacity-80 uppercase font-bold">Tracking Number</p>
                                <h2 class="text-2xl font-black font-mono"><?php echo $shipment['tracking_no']; ?></h2>
                            </div>
                            <div class="text-right">
                                <p class="text-sm opacity-80 uppercase font-bold mb-1">Current Status</p>
                                <span class="bg-black/20 backdrop-blur-sm px-4 py-2 rounded-full font-bold text-sm inline-block border border-white/10">
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
                    
                    <div class="p-8 space-y-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="bg-white/5 p-4 rounded-xl border border-white/5">
                                <p class="text-slate-500 text-xs font-bold uppercase mb-1">Receiver</p>
                                <p class="font-bold text-xl text-white"><?php echo $shipment['receiver_name']; ?></p>
                            </div>
                            <div class="bg-white/5 p-4 rounded-xl border border-white/5">
                                <p class="text-slate-500 text-xs font-bold uppercase mb-1">Forwarder</p>
                                <p class="font-bold text-xl text-white"><?php echo $shipment['company_name']; ?></p>
                            </div>
                        </div>
                        
                        <div class="bg-white/5 p-5 rounded-xl border border-white/5">
                            <p class="text-slate-500 text-xs font-bold uppercase mb-2">Complete Delivery Address</p>
                            <p class="text-slate-200 leading-relaxed"><?php echo nl2br(htmlspecialchars($shipment['destination'])); ?></p>
                        </div>
                        
                        <div class="bg-gradient-to-r from-blue-600/20 to-indigo-600/20 p-5 rounded-xl flex justify-between items-center border border-blue-500/30">
                            <span class="text-blue-300 font-medium">Estimated Arrival:</span>
                            <span class="font-bold text-blue-400 text-xl"><?php echo $shipment['arrival_date'] ?? 'Processing...'; ?></span>
                        </div>
                        
                        <div class="bg-slate-900/50 p-4 rounded-xl text-center border border-white/5">
                            <i class="fas fa-info-circle text-blue-400 mr-2"></i>
                            <span class="text-sm text-slate-400">For more details, visit your 
                                <a href="my_shipments.php" class="text-blue-400 font-bold hover:underline">My Shipments</a> page.
                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
