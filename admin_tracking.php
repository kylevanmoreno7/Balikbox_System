<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Admin') { 
    header("Location: index.php"); 
    exit(); 
}
include('config.php');

$message = "";
$error = "";
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Total records for pagination
$total_res = $conn->query("SELECT COUNT(*) as total FROM shipments WHERE admin_status IN ('approved', 'in_transit', 'ready_for_pickup')");
$total_rows = $total_res->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Handle status and forwarder updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_shipment'])) {
    $shipment_id = $_POST['shipment_id'];
    $new_status = $_POST['admin_status'];
    $forwarder_id = $_POST['forwarder_id'];
    $eta = $_POST['arrival_date'];

    $update = $conn->prepare("UPDATE shipments SET admin_status = ?, forwarder_id = ?, arrival_date = ? WHERE shipment_id = ?");
    $update->bind_param("sisi", $new_status, $forwarder_id, $eta, $shipment_id);
    
    if ($update->execute()) {
        $info = $conn->query("SELECT tracking_no, sender_id FROM shipments WHERE shipment_id = $shipment_id")->fetch_assoc();
        
        $status_text = str_replace('_', ' ', $new_status);
        notifyUser($info['sender_id'],
            "📦 Shipment Update: $status_text",
            "Your shipment {$info['tracking_no']} is now $status_text. Estimated arrival: " . date('M d, Y', strtotime($eta)),
            'delivery',
            "receive.php?tracking_no=" . $info['tracking_no']
        );
        $message = "Shipment " . $info['tracking_no'] . " updated successfully!";
    } else {
        $error = "Failed to update shipment.";
    }
}

// Fetch all approved/active shipments for management
$sql = "SELECT s.*, f.company_name 
        FROM shipments s 
        LEFT JOIN forwarders f ON s.forwarder_id = f.forwarder_id 
        WHERE s.admin_status IN ('approved', 'in_transit', 'ready_for_pickup') 
        ORDER BY s.created_at DESC 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Fetch active forwarders for the dropdown
$forwarders = $conn->query("SELECT * FROM forwarders WHERE status = 'Active'");
$forwarder_list = [];
while($f = $forwarders->fetch_assoc()) { $forwarder_list[] = $f; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Deliveries | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .compact-table td, .compact-table th {
            padding: 10px 8px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body class="min-h-screen" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1e40af 100%);">
    
    <!-- Navigation - Parehas sa admin_dashboard.php -->
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
                    <span class="text-xs text-blue-200 ml-2">Manage Deliveries</span>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <?php include('includes/notification_bell.php'); ?>
                <div class="flex items-center gap-3 bg-white/10 rounded-full px-4 py-2">
                    <i class="fas fa-user-shield text-blue-200 text-lg"></i>
                    <span class="text-sm font-medium text-white">Admin</span>
                </div>
                <a href="logout.php" class="bg-red-500/80 hover:bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-bold transition">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto pt-24 px-6 pb-12">
        <div class="max-w-full mx-auto">
            
            <div class="mb-6">
                <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center mb-3 shadow-xl">
                    <i class="fas fa-calendar-check text-white text-2xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white">Manage Deliveries</h1>
                <p class="text-blue-200 mt-1">Update shipment status, assign forwarders, and set arrival dates</p>
            </div>

            <?php if($message): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-xl mb-6"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl mb-6"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse compact-table">
                        <thead class="bg-gradient-to-r from-purple-600 to-pink-600 text-white">
                            <tr class="text-xs uppercase tracking-wider">
                                <th class="px-3 py-3">Tracking #</th>
                                <th class="px-3 py-3">Sender</th>
                                <th class="px-3 py-3">Receiver</th>
                                <th class="px-3 py-3">Destination</th>
                                <th class="px-3 py-3">Forwarder</th>
                                <th class="px-3 py-3">Status</th>
                                <th class="px-3 py-3">ETA</th>
                                <th class="px-3 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php while($ship = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition text-sm">
                                <td class="px-3 py-2">
                                    <div class="font-mono font-bold text-blue-600 text-xs"><?php echo $ship['tracking_no']; ?></div>
                                    <div class="text-[10px] text-gray-400">ID: <?php echo $ship['shipment_id']; ?></div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="font-medium text-gray-800 text-xs"><?php echo htmlspecialchars(substr($ship['sender_full_name'], 0, 15)); ?></div>
                                    <div class="text-[10px] text-gray-500"><?php echo htmlspecialchars($ship['sender_country']); ?></div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="font-medium text-gray-800 text-xs"><?php echo htmlspecialchars(substr($ship['receiver_name'], 0, 15)); ?></div>
                                    <div class="text-[10px] text-gray-500"><?php echo htmlspecialchars(substr($ship['receiver_phone'], 0, 12)); ?></div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="text-[11px] text-gray-600 truncate max-w-[180px]" title="<?php echo htmlspecialchars($ship['destination']); ?>">
                                        <?php echo htmlspecialchars(substr($ship['destination'], 0, 45)); ?>...
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <form id="form-<?php echo $ship['shipment_id']; ?>" method="POST" class="flex flex-col gap-1">
                                        <input type="hidden" name="shipment_id" value="<?php echo $ship['shipment_id']; ?>">
                                        <select name="forwarder_id" class="text-[11px] p-1.5 border rounded-lg bg-white focus:ring-1 focus:ring-purple-500 outline-none w-28">
                                            <option value="">Select</option>
                                            <?php foreach($forwarder_list as $f): ?>
                                                <option value="<?php echo $f['forwarder_id']; ?>" <?php echo ($ship['forwarder_id'] == $f['forwarder_id']) ? 'selected' : ''; ?>>
                                                    <?php echo $f['company_name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                </td>
                                <td class="px-3 py-2">
                                    <select name="admin_status" class="text-[11px] p-1.5 border rounded-lg font-bold focus:ring-1 focus:ring-purple-500 outline-none w-28">
                                        <option value="approved" <?php if($ship['admin_status'] == 'approved') echo 'selected'; ?>>Approved</option>
                                        <option value="in_transit" <?php if($ship['admin_status'] == 'in_transit') echo 'selected'; ?>>In Transit</option>
                                        <option value="ready_for_pickup" <?php if($ship['admin_status'] == 'ready_for_pickup') echo 'selected'; ?>>Ready for Pickup</option>
                                        <option value="delivered" <?php if($ship['admin_status'] == 'delivered') echo 'selected'; ?>>Delivered</option>
                                    </select>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="date" name="arrival_date" value="<?php echo $ship['arrival_date']; ?>" class="text-[11px] p-1.5 border rounded-lg focus:ring-1 focus:ring-purple-500 outline-none w-28">
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button type="submit" name="update_shipment" class="bg-gradient-to-r from-purple-500 to-pink-500 hover:shadow-lg text-white px-3 py-1.5 rounded-lg text-[11px] font-bold transition">
                                        <i class="fas fa-save mr-1 text-[10px]"></i> Update
                                    </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php if($result->num_rows == 0): ?>
                        <div class="p-12 text-center text-gray-400">
                            <i class="fas fa-truck-loading text-5xl mb-3"></i>
                            <p>No active deliveries found. Approve some shipments first!</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="p-4 flex justify-center gap-2 border-t">
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" 
                        class="px-3 py-1 rounded-lg border text-xs font-bold transition <?php echo $page == $i ? 'bg-purple-600 text-white border-purple-500' : 'bg-white text-gray-600 hover:bg-gray-50'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>