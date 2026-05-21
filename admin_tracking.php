<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Admin') { 
    header("Location: index.php"); 
    exit(); 
}
include('config.php');
include('includes/notifications.php'); // Added for getUnreadCount used in sidebar

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

// Fetch shipments
$sql = "SELECT s.*, f.company_name 
        FROM shipments s 
        LEFT JOIN forwarders f ON s.forwarder_id = f.forwarder_id 
        WHERE s.admin_status IN ('approved', 'in_transit', 'ready_for_pickup') 
        ORDER BY s.created_at DESC 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Fetch forwarders
$forwarders = $conn->query("SELECT * FROM forwarders WHERE status = 'Active'");
$forwarder_list = [];
while($f = $forwarders->fetch_assoc()) { $forwarder_list[] = $f; }

$status_hierarchy = [
    'approved' => 1,
    'in_transit' => 2,
    'ready_for_pickup' => 3,
    'delivered' => 4
];
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
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; }
        .bg-mesh {
            background-image: 
                radial-gradient(at 0% 0%, rgba(88, 28, 135, 0.2) 0, transparent 50%), 
                radial-gradient(at 100% 100%, rgba(30, 58, 138, 0.15) 0, transparent 50%);
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
        
        .compact-table td, .compact-table th { padding: 12px 10px; }

        /* Container for the scrolling text */
        .scroll-container {
            overflow: hidden;
            white-space: nowrap;
            position: relative;
            width: 100%;
        }

        /* The actual text that moves */
        .scroll-text {
            display: inline-block;
            transition: transform 2s linear;
        }

        /* Animation trigger on hover */
        .scroll-container:hover .scroll-text {
            transform: translateX(calc(-100% + 150px)); /* Adjust 150px based on column width */
        }
    </style>
</head>
<body class="min-h-screen bg-mesh text-slate-100 antialiased flex">
    
    <?php include('sidebar.php'); ?>

    <main class="flex-1 ml-72 p-8 lg:p-12">
        <div class="max-w-full mx-auto">
            
            <header class="mb-10">
                <div class="flex items-center gap-4 mb-2">
                    <div class="w-12 h-12 bg-blue-500/10 border border-blue-500/20 rounded-2xl flex items-center justify-center shadow-lg shadow-purple-500/5">
                        <i class="fas fa-shipping-fast text-blue-400 text-xl"></i>
                    </div>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight">Active Deliveries</h1>
                </div>
                <p class="text-slate-400">Update shipment status, assign logistics partners, and manage arrival timelines.</p>
            </header>

            <?php if($message): ?>
                <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-4 rounded-2xl mb-8 flex items-center gap-3">
                    <i class="fas fa-check-circle"></i>
                    <span class="font-bold text-xs uppercase tracking-wide"><?php echo $message; ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-slate-800/30 backdrop-blur-md border border-white/10 rounded-3xl shadow-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse compact-table">
                        <thead>
                            <tr class="bg-white/5 border-b border-white/5 text-slate-500 uppercase text-[10px] font-black tracking-widest">
                                <th class="px-6 py-5">Tracking Info</th>
                                <th class="px-6 py-5">Sender/Receiver</th>
                                <th class="px-6 py-5">Destination</th>
                                <th class="px-6 py-5">Logistics Partner</th>
                                <th class="px-6 py-5">Current Status</th>
                                <th class="px-6 py-5">ETA</th>
                                <th class="px-6 py-5 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php while($ship = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-white/5 transition-all group">
                                <td class="px-6 py-4">
                                    <div class="font-mono font-bold text-blue-400 text-xs tracking-tighter"><?php echo $ship['tracking_no']; ?></div>
                                    <div class="text-[10px] text-slate-500 mt-1">ID: #<?php echo str_pad($ship['shipment_id'], 4, '0', STR_PAD_LEFT); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1 w-40"> <div class="text-xs font-bold text-slate-200 scroll-container">
                                            <div class="scroll-text">
                                                <i class="fas fa-arrow-up text-[8px] text-emerald-400 mr-1"></i> 
                                                <?php echo htmlspecialchars($ship['sender_full_name']); ?>
                                            </div>
                                        </div>
                                        <div class="text-xs font-bold text-slate-400 scroll-container">
                                            <div class="scroll-text">
                                                <i class="fas fa-arrow-down text-[8px] text-blue-400 mr-1"></i> 
                                                <?php echo htmlspecialchars($ship['receiver_name']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                    <td class="px-6 py-4">
                                    <div class="text-[10px] text-slate-400 leading-relaxed w-36 scroll-container">
                                        <div class="scroll-text">
                                            <i class="fas fa-map-marker-alt text-purple-500/50 mr-1"></i>
                                            <?php echo htmlspecialchars($ship['destination']); ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <form id="form-<?php echo $ship['shipment_id']; ?>" method="POST" class="flex flex-col gap-1">
                                        <input type="hidden" name="shipment_id" value="<?php echo $ship['shipment_id']; ?>">
                                        <select name="forwarder_id" class="text-[10px] p-2 rounded-xl bg-slate-900/50 border border-white/10 text-slate-300 focus:ring-1 focus:ring-purple-500/50 outline-none w-32">
                                            <option value="">Select Partner</option>
                                            <?php foreach($forwarder_list as $f): ?>
                                                <option value="<?php echo $f['forwarder_id']; ?>" <?php echo ($ship['forwarder_id'] == $f['forwarder_id']) ? 'selected' : ''; ?>>
                                                    <?php echo $f['company_name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                </td>
                                <td class="px-6 py-4">
                                    <select name="admin_status" class="text-[10px] p-2 rounded-xl bg-slate-900/50 border border-white/10 text-purple-300 font-black uppercase tracking-tighter focus:ring-1 focus:ring-purple-500/50 outline-none w-32">
                                        <?php 
                                        $current_rank = isset($status_hierarchy[$ship['admin_status']]) ? $status_hierarchy[$ship['admin_status']] : 0;
                                        $options = ['approved' => 'Approved', 'in_transit' => 'In Transit', 'ready_for_pickup' => 'Ready for Pickup', 'delivered' => 'Delivered'];
                                        foreach ($options as $val => $label): 
                                            $opt_rank = $status_hierarchy[$val];
                                            $is_disabled = ($opt_rank < $current_rank) ? 'disabled' : '';
                                            $is_selected = ($ship['admin_status'] == $val) ? 'selected' : '';
                                        ?>
                                            <option value="<?php echo $val; ?>" <?php echo "$is_selected $is_disabled"; ?> class="bg-slate-800">
                                                <?php echo $label; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="px-6 py-4">
                                    <input type="date" name="arrival_date" value="<?php echo $ship['arrival_date']; ?>" class="text-[10px] p-2 rounded-xl bg-slate-900/50 border border-white/10 text-slate-300 focus:ring-1 focus:ring-purple-500/50 outline-none">
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button type="submit" name="update_shipment" class="bg-purple-600 hover:bg-purple-500 text-white font-black text-[10px] uppercase tracking-widest py-2 px-4 rounded-xl shadow-lg shadow-purple-900/20 transition-all active:scale-95">
                                        <i class="fas fa-save mr-1"></i> Update
                                    </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-10 flex justify-center items-center gap-3">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" 
                       class="w-10 h-10 flex items-center justify-center rounded-xl font-bold text-xs transition-all <?php echo $page == $i ? 'bg-purple-600 text-white shadow-lg shadow-purple-500/20' : 'bg-slate-800/50 text-slate-500 border border-white/5 hover:bg-slate-700'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </main>
</body>
</html>
