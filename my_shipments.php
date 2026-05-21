<?php
session_start();
if (!isset($_SESSION['user_id'])) { 
    header("Location: index.php"); 
    exit(); 
}
include('config.php');
include('includes/notifications.php');

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Configuration for pagination
$limit = 3; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch total records for pagination
$total_res = $conn->query("SELECT COUNT(*) as total FROM shipments WHERE sender_id = $user_id");
$total_rows = $total_res->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);  

// Fetch shipments with forwarder details
$shipments_stmt = $conn->prepare("
    SELECT s.*, f.company_name 
    FROM shipments s 
    LEFT JOIN forwarders f ON s.forwarder_id = f.forwarder_id 
    WHERE s.sender_id = ? 
    ORDER BY s.created_at DESC 
    LIMIT ? OFFSET ?
");

$shipments_stmt->bind_param("iii", $user_id, $limit, $offset);
$shipments_stmt->execute();
$shipments = $shipments_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Shipments | BALIKBOX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; }
        
        /* Unique background aesthetic */
        .bg-mesh {
            background-image: 
                radial-gradient(at 0% 0%, rgba(30, 58, 138, 0.4) 0, transparent 50%), 
                radial-gradient(at 100% 100%, rgba(13, 148, 136, 0.15) 0, transparent 50%);
        }

        /* Custom scrollbar for a polished look */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
    </style>
</head>
<body class="min-h-screen bg-mesh text-slate-100 antialiased">

    <?php include('sidebar.php'); ?>

    <main class="ml-72 p-8 lg:p-12">
        <div class="max-w-5xl mx-auto">
            
            <div class="flex justify-between items-center mb-10 flex-wrap gap-4">
                <div>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight">My Shipments</h1>
                    <p class="text-slate-400 mt-1 text-sm">Real-time status of your Balikbayan boxes</p>
                </div>
                <a href="send.php" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg shadow-indigo-500/20 flex items-center gap-2">
                    <i class="fas fa-plus"></i> New Shipment
                </a>
            </div>

            <?php if($total_rows > 0): ?>
                <div class="space-y-8">
                    <?php while($ship = $shipments->fetch_assoc()): 
                        // Logic for Timeline progress
                        $status_order = ['pending_approval', 'approved', 'in_transit', 'ready_for_pickup', 'delivered'];
                        $current_status = $ship['admin_status'];
                        $current_index = array_search($current_status, $status_order);
                        if ($current_index === false) $current_index = 0;
                        
                        $timeline_steps = [
                            'pending_approval' => ['label' => 'Placed', 'icon' => 'fa-clipboard-list'],
                            'approved' => ['label' => 'Approved', 'icon' => 'fa-check-double'],
                            'in_transit' => ['label' => 'In Transit', 'icon' => 'fa-ship'],
                            'ready_for_pickup' => ['label' => 'Arrived', 'icon' => 'fa-box-open'],
                            'delivered' => ['label' => 'Delivered', 'icon' => 'fa-house-chimney']
                        ];
                    ?>
                        <div class="bg-slate-800/30 backdrop-blur-md rounded-3xl border border-white/10 overflow-hidden shadow-2xl transition-all hover:border-indigo-500/40">
                            <div class="bg-white/5 p-6 border-b border-white/5 flex justify-between items-center">
                                <div>
                                    <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-[0.2em]">Tracking No.</span>
                                    <h2 class="text-xl font-mono font-black text-white mt-0.5">#<?php echo $ship['tracking_no']; ?></h2>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-slate-500 block uppercase font-bold tracking-widest">Date Sent</span>
                                    <span class="text-sm font-semibold text-slate-300"><?php echo date('M d, Y', strtotime($ship['created_at'])); ?></span>
                                </div>
                            </div>
                            
                            <div class="p-8">
                                <div class="relative flex justify-between mb-10">
                                    <div class="absolute top-5 left-0 w-full h-1 bg-slate-700/50 rounded-full -z-0"></div>
                                    <div class="absolute top-5 left-0 h-1 bg-indigo-500 rounded-full transition-all duration-700 -z-0 shadow-[0_0_15px_rgba(99,102,241,0.5)]" 
                                         style="width: <?php echo ($current_index / 4) * 100; ?>%;"></div>

                                    <?php foreach($timeline_steps as $key => $step): 
                                        $step_idx = array_search($key, $status_order);
                                        $is_completed = $step_idx <= $current_index;
                                        $is_active = $key == $current_status;
                                    ?>
                                        <div class="relative z-10 flex flex-col items-center group">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-500 
                                                <?php echo $is_completed ? 'bg-indigo-600 text-white' : 'bg-slate-700 text-slate-500'; ?> 
                                                <?php echo $is_active ? 'ring-4 ring-indigo-500/20 scale-110 shadow-lg' : ''; ?>">
                                                <i class="fas <?php echo $step['icon']; ?> text-xs"></i>
                                            </div>
                                            <p class="text-[9px] mt-4 font-black uppercase tracking-wider <?php echo $is_completed ? 'text-indigo-300' : 'text-slate-600'; ?>">
                                                <?php echo $step['label']; ?>
                                            </p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-white/5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                                            <i class="fas fa-user-tag text-xs"></i>
                                        </div>
                                        <div>
                                            <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest">Receiver</p>
                                            <p class="text-sm font-semibold text-slate-200"><?php echo htmlspecialchars($ship['receiver_name']); ?></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                                            <i class="fas fa-expand-arrows-alt text-xs"></i>
                                        </div>
                                        <div>
                                            <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest">Size Category</p>
                                            <p class="text-sm font-semibold text-slate-200 uppercase"><?php echo $ship['box_size']; ?></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-400">
                                            <i class="fas fa-location-dot text-xs"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest">Destination</p>
                                            <p class="text-sm font-semibold text-slate-200 truncate"><?php echo htmlspecialchars($ship['destination']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="mt-10 flex justify-center gap-2">
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="w-10 h-10 flex items-center justify-center rounded-xl transition-all font-bold <?php echo $page == $i ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>

            <?php else: ?>
                <div class="bg-slate-800/30 border-2 border-dashed border-white/5 rounded-3xl p-16 text-center">
                    <div class="w-20 h-20 bg-slate-800 rounded-3xl flex items-center justify-center mx-auto mb-6 text-slate-600 text-3xl">
                        <i class="fas fa-parachute-box"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white">No shipments active</h3>
                    <p class="text-slate-500 mt-2 max-w-xs mx-auto">Once you send a Balikbayan box, it will appear here for tracking.</p>
                    <a href="send.php" class="inline-block mt-8 bg-white text-slate-900 px-8 py-3 rounded-xl font-bold hover:bg-indigo-50 transition-all">
                        Start Shipping
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
