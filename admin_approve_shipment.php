<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Admin') { 
    header("Location: index.php"); 
    exit(); 
}
include('config.php');

$shipment_id = isset($_GET['id']) ? $_GET['id'] : 0;
$message = "";
$error = "";

$ship_query = $conn->prepare("SELECT s.*, u.username as sender_username, f.company_name 
                              FROM shipments s 
                              LEFT JOIN users u ON s.sender_id = u.user_id 
                              LEFT JOIN forwarders f ON s.forwarder_id = f.forwarder_id 
                              WHERE s.shipment_id = ?");
$ship_query->bind_param("i", $shipment_id);
$ship_query->execute();
$shipment = $ship_query->get_result()->fetch_assoc();

if (!$shipment) {
    die("Shipment not found.");
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($action == 'approve') {
        $arrival_date = $_POST['arrival_date'] ?? date('Y-m-d', strtotime('+30 days'));
        
        $update = $conn->prepare("UPDATE shipments SET admin_status = 'approved', approved_at = NOW(), arrival_date = ? WHERE shipment_id = ?");
        $update->bind_param("si", $arrival_date, $shipment_id);
        
        if ($update->execute()) {
            $sender_query = $conn->prepare("SELECT sender_id, sender_full_name, tracking_no FROM shipments WHERE shipment_id = ?");
            $sender_query->bind_param("i", $shipment_id);
            $sender_query->execute();
            $ship_data = $sender_query->get_result()->fetch_assoc();
            
            notifyUser($ship_data['sender_id'],
                '✅ Shipment Approved!',
                'Your shipment ' . $ship_data['tracking_no'] . ' has been approved. Expected arrival: ' . date('F j, Y', strtotime($arrival_date)),
                'approval',
                'receive.php?tracking_no=' . $ship_data['tracking_no']
            );
            
            notifyAllAdmins(
                '✅ Shipment Approved',
                'Shipment ' . $ship_data['tracking_no'] . ' has been approved by ' . $_SESSION['username'] . '. Expected arrival: ' . date('F j, Y', strtotime($arrival_date)),
                'approval',
                'admin_dashboard.php'
            );
            
            $message = "Shipment approved successfully! Expected arrival: " . date('F j, Y', strtotime($arrival_date));
            header("Location: admin_dashboard.php?msg=" . urlencode($message));
            exit();
        } else {
            $error = "Failed to approve shipment.";
        }
    }
    elseif ($action == 'reject') {
        $admin_notes = $_POST['admin_notes'] ?? '';
        
        $update = $conn->prepare("UPDATE shipments SET admin_status = 'rejected', admin_notes = ? WHERE shipment_id = ?");
        $update->bind_param("si", $admin_notes, $shipment_id);
        
        if ($update->execute()) {
            $sender_query = $conn->prepare("SELECT sender_id, tracking_no FROM shipments WHERE shipment_id = ?");
            $sender_query->bind_param("i", $shipment_id);
            $sender_query->execute();
            $ship_data = $sender_query->get_result()->fetch_assoc();
            
            notifyUser($ship_data['sender_id'],
                '❌ Shipment Rejected',
                'Your shipment ' . $ship_data['tracking_no'] . ' has been rejected. Reason: ' . $admin_notes,
                'shipment',
                'send.php'
            );
            
            $message = "Shipment rejected.";
            header("Location: admin_dashboard.php?msg=" . urlencode($message));
            exit();
        } else {
            $error = "Failed to reject shipment.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review Shipment | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1e40af 100%);">
    
    <nav class="bg-white/10 backdrop-blur-md shadow-lg px-6 py-4 fixed w-full top-0 z-50 border-b border-white/10">
        <div class="container mx-auto">
            <div class="flex items-center gap-4">
                <a href="admin_dashboard.php" class="bg-gray-500/80 hover:bg-gray-600 text-white px-4 py-2 rounded-xl text-sm font-bold transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-box-open text-white text-xl"></i>
                    </div>
                    <span class="font-bold text-2xl text-white tracking-tight">BALIKBOX</span>
                    <span class="text-xs text-blue-200 ml-2">Review Shipment</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto pt-28 px-6 pb-12">
        <div class="max-w-4xl mx-auto">
            
            <?php if($message): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-xl mb-6"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl mb-6"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-yellow-500 to-orange-500 p-6 text-white">
                    <h2 class="text-2xl font-bold">Review Shipment</h2>
                    <p class="text-yellow-100">Tracking #: <?php echo $shipment['tracking_no']; ?></p>
                </div>
                
                <div class="p-6">
                    <div class="grid md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <h3 class="font-bold text-gray-700 border-b pb-2">Sender Information</h3>
                            <p class="mt-2"><strong>Name:</strong> <?php echo htmlspecialchars($shipment['sender_full_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($shipment['sender_email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($shipment['sender_phone']); ?></p>
                            <p><strong>Country:</strong> <?php echo htmlspecialchars($shipment['sender_country']); ?></p>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-700 border-b pb-2">Receiver Information</h3>
                            <p class="mt-2"><strong>Name:</strong> <?php echo htmlspecialchars($shipment['receiver_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($shipment['receiver_email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($shipment['receiver_phone']); ?></p>
                            <p><strong>Destination:</strong> <?php echo htmlspecialchars($shipment['destination']); ?></p>
                        </div>
                    </div>
                    
                    <div class="grid md:grid-cols-3 gap-4 mb-8 p-4 bg-gray-50 rounded-xl">
                        <div><strong>Box Size:</strong> <?php echo $shipment['box_size']; ?></div>
                        <div><strong>Amount:</strong> ₱<?php echo number_format($shipment['price'], 2); ?></div>
                        <div><strong>Forwarder:</strong> <?php echo $shipment['company_name'] ?? 'Not assigned'; ?></div>
                    </div>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="shipment_id" value="<?php echo $shipment_id; ?>">
                        
                        <div>
                            <label class="block font-bold text-gray-700 mb-2">Set Expected Arrival Date</label>
                            <input type="date" name="arrival_date" 
                                   value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" 
                                   class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                            <p class="text-xs text-gray-400 mt-1">Customer will see this as estimated delivery date</p>
                        </div>
                        
                        <div class="flex gap-4">
                            <button type="submit" name="action" value="approve" 
                                    class="bg-gradient-to-r from-green-500 to-green-600 hover:shadow-lg text-white font-bold py-3 px-6 rounded-xl transition">
                                <i class="fas fa-check mr-2"></i> Approve Shipment
                            </button>
                            <button type="button" onclick="showRejectModal()" 
                                    class="bg-gradient-to-r from-red-500 to-red-600 hover:shadow-lg text-white font-bold py-3 px-6 rounded-xl transition">
                                <i class="fas fa-times mr-2"></i> Reject Shipment
                            </button>
                        </div>
                        
                        <div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                            <div class="bg-white rounded-2xl p-6 w-96">
                                <h3 class="text-xl font-bold mb-4">Reject Shipment</h3>
                                <p class="mb-3">Please provide a reason for rejection:</p>
                                <textarea name="admin_notes" id="reject_reason" rows="3" class="w-full p-3 border rounded-xl mb-4 focus:ring-2 focus:ring-red-500 outline-none" placeholder="Enter reason..."></textarea>
                                <div class="flex gap-3">
                                    <button type="submit" name="action" value="reject" class="bg-red-500 text-white px-4 py-2 rounded-xl hover:bg-red-600 transition font-bold">Confirm Reject</button>
                                    <button type="button" onclick="hideRejectModal()" class="bg-gray-300 px-4 py-2 rounded-xl hover:bg-gray-400 transition">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showRejectModal() {
            document.getElementById('rejectModal').classList.remove('hidden');
        }
        function hideRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }
    </script>
</body>
</html>