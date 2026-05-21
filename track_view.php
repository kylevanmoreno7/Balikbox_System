<?php
// track_view.php - PUBLIC TRACKING
include('config.php'); 

$shipment = null;
$error = "";
$tracking_no = "";

if (isset($_GET['tracking_no'])) {
    $tracking_no = trim($_GET['tracking_no']);
    
    $stmt = $conn->prepare("
        SELECT s.*, f.company_name 
        FROM shipments s 
        LEFT JOIN forwarders f ON s.forwarder_id = f.forwarder_id 
        WHERE s.tracking_no = ?
    ");
    $stmt->bind_param("s", $tracking_no);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $shipment = $result->fetch_assoc();
    } else {
        $error = "No shipment found with tracking number: " . htmlspecialchars($tracking_no);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Box | Balikbox</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
    html {
        scroll-behavior: smooth;
    }   

    @layer utilities {
        .nav-gap {
            gap: 5rem; 
        }
    }
    
    /* Automated Hover Logic */
    .group:hover .group-hover\:flex {
        display: flex !important;
    }

    /* "Stay Open" Logic when clicked */
    .menu-active {
        display: flex !important;
    }
    
    /* Rotate arrow when active */
    .rotate-180 {
        transform: rotate(180deg);
    }
</style>
</head>
<!-- Added flex and flex-col to keep footer at bottom -->
<body class="min-h-screen flex flex-col" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1e40af 100%);">

<nav class="bg-white/10 backdrop-blur-md shadow-lg px-6 py-4 fixed w-full top-0 z-50 border-b border-white/10">
    <div class="container mx-auto flex justify-between items-center">
        <div class="flex items-center gap-2">
            <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center shadow-lg">
                <a href="index.php" class="fas fa-box-open text-white text-xl"></a>
            </div>
            <a href="index.php" class="font-bold text-2xl text-white tracking-tight">BALIKBOX</a>
            <span class="text-xs text-blue-200 ml-2">Logistics</span>
        </div>
        
        <div class="hidden md:flex nav-gap">
            <a href="index.php" class="text-white hover:text-blue-300 font-medium transition">Home</a>
            <a href="track_view.php" class="text-white hover:text-blue-300 font-medium transition">Track</a>
            <a href="index.php#about" class="text-white hover:text-blue-300 font-medium transition">About Us</a>
            
             <div class="relative group">
                <button onclick="toggleHelpMenu(event)" class="text-white hover:text-blue-300 font-medium flex items-center gap-1.5 focus:outline-none">
                    Help
                    <i id="helpArrow" class="fas fa-chevron-down text-xs opacity-70 mt-0.5 transition-transform duration-200"></i>
                </button>
                <div id="helpDropdown" class="absolute left-0 top-full mt-2 w-48 bg-white/10 backdrop-blur-md rounded-xl shadow-xl border border-white/10 py-2 group-hover:flex hidden flex-col z-[110]">
                    <a href="faqs.php" class="px-5 py-2 text-white hover:bg-white/10 text-sm">FAQs</a>
                    <a href="contact.php" class="px-5 py-2 text-white hover:bg-white/10 text-sm">Contact Support</a>
                    <a href="guide.php" class="px-5 py-2 text-white hover:bg-white/10 text-sm">Shipping Guide</a>
                </div>
            </div>
        </div>
        
       <div class="flex items-center gap-1 text-sm font-bold text-white pr-10">
            <a href="register.php" class="hover:text-blue-300 transition">Sign Up</a>
            <span class="opacity-50">/</span>
            <button onclick="openLogin('Customer')" class="hover:text-blue-300 transition">Login</button>
        </div>
    </div>
</nav>

    <!-- Main Content: Added pt-32 to clear the fixed navbar -->
    <div class="container mx-auto pt-32 pb-12 px-4 flex-grow">
        <div class="max-w-3xl mx-auto">
            
            <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl p-8 mb-8">
                <div class="text-center mb-6">
                    <i class="fas fa-search text-5xl text-blue-600 mb-3"></i>
                    <h2 class="text-2xl font-bold text-gray-800">Track Your Balikbayan Box</h2>
                    <p class="text-gray-500">Enter your tracking number to get real-time updates</p>
                </div>
                
                <form method="GET" class="flex flex-col md:flex-row gap-3">
                    <input type="text" name="tracking_no" value="<?php echo htmlspecialchars($tracking_no); ?>"
                           placeholder="Enter Tracking Number" 
                           class="flex-1 p-4 rounded-xl border-2 border-gray-200 focus:border-blue-500 outline-none text-lg"
                           required>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-xl font-bold transition shadow-lg">
                        <i class="fas fa-truck mr-2"></i> TRACK NOW
                    </button>
                </form>
            </div>
            
            <?php if($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl mb-8">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if($shipment): ?>
                <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl overflow-hidden mb-12">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 p-6 text-white">
                        <div class="flex justify-between items-center flex-wrap gap-4">
                            <div>
                                <p class="text-sm opacity-80 uppercase font-bold mb-1">Tracking Number</p>
                                <h2 class="text-3xl font-black font-mono"><?php echo $shipment['tracking_no']; ?></h2>
                            </div>
                            <div class="text-right">
                                <p class="text-sm opacity-80 uppercase font-bold mb-2">Current Status</p>
                                <?php 
                                    $status_map = [
                                        'pending_approval' => ['label' => 'Pending Approval', 'color' => 'bg-yellow-500'],
                                        'approved' => ['label' => 'Approved', 'color' => 'bg-blue-500'],
                                        'rejected' => ['label' => 'Rejected', 'color' => 'bg-red-500'],
                                        'in_transit' => ['label' => 'In Transit', 'color' => 'bg-indigo-500'],
                                        'ready_for_pickup' => ['label' => 'Ready for Pickup', 'color' => 'bg-green-500'],
                                        'delivered' => ['label' => 'Delivered', 'color' => 'bg-gray-500']
                                    ];
                                    $status = $status_map[$shipment['admin_status']] ?? ['label' => $shipment['admin_status'], 'color' => 'bg-gray-500'];
                                ?>
                                <span class="<?php echo $status['color']; ?> text-white px-4 py-2 rounded-full font-bold text-sm shadow-md">
                                    <?php echo $status['label']; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-8 space-y-8">
                        <div>
                            <p class="text-gray-500 text-xs font-bold uppercase mb-6">Shipment Progress</p>
                            <div class="flex items-center justify-between relative">
                                <?php 
                                    $steps = ['pending_approval', 'approved', 'in_transit', 'ready_for_pickup', 'delivered'];
                                    $current_index = array_search($shipment['admin_status'], $steps);
                                    if ($current_index === false) $current_index = 0;
                                ?>
                                <?php foreach($steps as $index => $step): 
                                    $step_labels = [
                                        'pending_approval' => 'Placed',
                                        'approved' => 'Approved',
                                        'in_transit' => 'In Transit',
                                        'ready_for_pickup' => 'Ready',
                                        'delivered' => 'Delivered'
                                    ];
                                    $is_completed = $index <= $current_index;
                                ?>
                                    <div class="text-center flex-1 relative z-10">
                                        <div class="w-8 h-8 mx-auto rounded-full flex items-center justify-center transition-colors duration-500 <?php echo $is_completed ? 'bg-green-500 shadow-lg shadow-green-200' : 'bg-gray-300'; ?>">
                                            <i class="fas fa-check text-white text-xs"></i>
                                        </div>
                                        <p class="text-[10px] md:text-xs mt-2 font-bold <?php echo $is_completed ? 'text-gray-800' : 'text-gray-400'; ?>">
                                            <?php echo $step_labels[$step]; ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                                <!-- Background Line -->
                                <div class="absolute top-4 left-0 w-full h-1 bg-gray-200 -z-0"></div>
                                <!-- Progress Line -->
                                <div class="absolute top-4 left-0 h-1 bg-green-500 transition-all duration-500 -z-0" style="width: <?php echo ($current_index / (count($steps)-1)) * 100; ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-6 pt-6 border-t border-gray-100">
                            <div>
                                <p class="text-gray-400 text-xs font-bold uppercase">Receiver</p>
                                <p class="font-bold text-gray-800"><?php echo htmlspecialchars($shipment['receiver_name']); ?></p>
                                <p class="text-gray-500 text-sm"><?php echo htmlspecialchars($shipment['receiver_phone']); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-400 text-xs font-bold uppercase">Forwarder</p>
                                <p class="font-bold text-gray-800"><?php echo $shipment['company_name'] ?? 'Not Assigned'; ?></p>
                            </div>
                        </div>
                        
                        <div>
                            <p class="text-gray-400 text-xs font-bold uppercase">Destination</p>
                            <p class="text-gray-700 text-sm leading-relaxed"><?php echo nl2br(htmlspecialchars($shipment['destination'])); ?></p>
                        </div>
                        
                        <div class="bg-blue-50 p-4 rounded-xl flex justify-between items-center border border-blue-100">
                            <span class="text-blue-800 font-medium text-sm">Est. Delivery:</span>
                            <span class="font-bold text-blue-700">
                                <?php echo $shipment['arrival_date'] ? date('M j, Y', strtotime($shipment['arrival_date'])) : 'Processing...'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-6 text-center">
            <p class="text-sm">&copy; 2024 Balikbox Logistics. All rights reserved.</p>
            <p class="text-xs text-gray-400 mt-2">Delivering smiles to the Philippines since 2020</p>
        </div>
    </footer>

  <script>
    function toggleHelpMenu(event) {
    // Prevent the click from immediately bubbling up to the window
    event.stopPropagation();
    
    const dropdown = document.getElementById('helpDropdown');
    const arrow = document.getElementById('helpArrow');
    
    // Toggle the 'menu-active' class which overrides the 'hidden' state
    dropdown.classList.toggle('menu-active');
    arrow.classList.toggle('rotate-180');
}

// Close the menu if you click anywhere else on the screen
window.addEventListener('click', function() {
    const dropdown = document.getElementById('helpDropdown');
    const arrow = document.getElementById('helpArrow');
    
    if (dropdown.classList.contains('menu-active')) {
        dropdown.classList.remove('menu-active');
        arrow.classList.remove('rotate-180');
    }
}); 
</script>

</body>
</html>
