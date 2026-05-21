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
 <title>Track Your Box | B-Box Premium Logistics</title>
 <script src="https://cdn.tailwindcss.com"></script>
 <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
 <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
 <script>
 tailwind.config = {
 theme: {
 extend: {
 colors: {
 'logi-navy': '#002147',
 'logi-gold': '#fecb00',
 'logi-blue': '#0056b3',
 'logi-gray': '#f8f9fa'
 },
 fontFamily: {
 'sans': ['Poppins', 'sans-serif'],
 'heading': ['Montserrat', 'sans-serif']
 }
 }
 }
 }
 </script>
 <style>
 html { scroll-behavior: smooth; }
 .nav-transition { transition: all 0.3s ease; }
 </style>
</head>
<body class="bg-logi-gray text-gray-800 min-h-screen flex flex-col justify-between">

<div class="hidden lg:block bg-logi-navy text-white py-2 text-sm border-b border-white/10 w-full">
 <div class="container mx-auto px-6 flex justify-between items-center">
 <div class="flex gap-6">
 <span><i class="fas fa-phone-alt text-logi-gold mr-2"></i> Support: +63 (02) 8000-0000</span>
 <span><i class="fas fa-envelope text-logi-gold mr-2"></i> info@bboxlogistics.com</span>
 </div>
 <div class="flex gap-4">
 <a href="#" class="hover:text-logi-gold transition"><i class="fab fa-facebook-f"></i></a>
 <a href="#" class="hover:text-logi-gold transition"><i class="fab fa-linkedin-in"></i></a>
 <a href="#" class="hover:text-logi-gold transition"><i class="fab fa-instagram"></i></a>
 </div>
 </div>
</div>

<nav class="sticky top-0 z-50 bg-white shadow-md nav-transition w-full">
 <div class="container mx-auto px-6 flex justify-between items-center h-20 md:h-24">
 <a href="index.php" class="flex items-center gap-3">
 <div class="w-12 h-12 bg-logi-navy flex items-center justify-center rounded shadow-inner">
 <i class="fas fa-boxes-packing text-logi-gold text-2xl"></i>
 </div>
 <div>
 <span class="block text-2xl font-heading font-800 text-logi-navy leading-none tracking-tighter">B-BOX</span>
 <span class="block text-[10px] font-bold text-logi-blue uppercase tracking-[0.2em]">Logistics System</span>
 </div>
 </a>

 <div class="hidden md:flex items-center gap-8 font-heading text-xs font-bold uppercase tracking-widest">
 <a href="index.php" class="text-logi-navy hover:text-logi-blue transition">Home</a>
 <a href="index.php#about" class="text-logi-navy hover:text-logi-blue transition">Our Story</a>
 <a href="index.php#services" class="text-logi-navy hover:text-logi-blue transition">Services</a>
 <a href="track_view.php" class="text-logi-blue border-b-2 border-logi-blue pb-1">Live Tracking</a>
 <div class="h-6 w-[1px] bg-gray-200"></div>
 <button onclick="openLogin('Customer')" class="text-logi-navy hover:text-logi-blue">Login</button>
 <a href="register.php" class="bg-logi-gold text-logi-navy px-6 py-3 rounded font-bold hover:bg-logi-navy hover:text-white transition shadow-sm">Get Started</a>
 </div>
 </div>
</nav>

<main class="flex-grow flex items-center justify-center py-12 md:py-20 w-full px-4">
 <div class="w-full max-w-3xl mx-auto my-auto">
 
 <div class="bg-white rounded-lg shadow-md border-t-4 border-logi-gold p-8 md:p-12 mb-8">
 <div class="text-center mb-8">
 <h5 class="text-logi-blue font-heading font-bold uppercase tracking-[0.2em] text-xs mb-2">Real-time Checkpoint Updates</h5>
 <h2 class="text-3xl font-heading font-800 text-logi-navy uppercase tracking-tight">Track Your Balikbayan Box</h2>
 <p class="text-gray-500 text-sm mt-2 max-w-md mx-auto">Enter your verified box tracking ID below to view current logistical dispatch milestones.</p>
 </div>
 
 <form method="GET" class="bg-logi-gray p-2 rounded-lg border border-gray-200/60 flex flex-col md:flex-row gap-2">
 <div class="flex-1 flex items-center px-4 py-3 md:py-0">
 <i class="fas fa-search text-gray-400 mr-3"></i>
 <input type="text" name="tracking_no" value="<?php echo htmlspecialchars($tracking_no); ?>"
 placeholder="Enter Box Tracking ID..." 
 class="w-full bg-transparent text-gray-800 font-medium outline-none text-base"
 required>
 </div>
 <button type="submit" class="bg-logi-navy text-white px-8 py-4 rounded font-heading font-bold uppercase text-xs tracking-widest hover:bg-logi-blue transition shadow-sm whitespace-nowrap">
 <i class="fas fa-truck-fast mr-2 text-logi-gold"></i> Track Now
 </button>
 </form>
 </div>
 
 <?php if($error): ?>
 <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-5 rounded shadow-sm mb-8 flex items-start gap-4">
 <i class="fas fa-exclamation-circle text-red-500 text-lg mt-0.5 flex-shrink-0"></i> 
 <div>
 <h4 class="font-heading font-bold text-sm uppercase tracking-wider text-red-900">Tracking Request Failed</h4>
 <p class="text-sm text-gray-600 mt-1"><?php echo $error; ?></p>
 </div>
 </div>
 <?php endif; ?>
 
 <?php if($shipment): ?>
 <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-100">
 
 <div class="bg-logi-navy p-6 md:p-8 text-white border-b-4 border-logi-gold">
 <div class="flex justify-between items-center flex-wrap gap-6">
 <div>
 <p class="text-xs text-logi-gold font-heading font-bold uppercase tracking-[0.2em] mb-1">Tracking Number</p>
 <h2 class="text-3xl font-heading font-800 tracking-tight text-white"><?php echo $shipment['tracking_no']; ?></h2>
 </div>
 <div class="md:text-right">
 <p class="text-xs text-gray-400 font-heading font-bold uppercase tracking-[0.2em] mb-2">Current Status</p>
 <?php 
 $status_map = [
 'pending_approval' => ['label' => 'Pending Approval', 'color' => 'bg-amber-500/10 text-amber-500 border border-amber-500/20'],
 'approved' => ['label' => 'Approved & Processed', 'color' => 'bg-logi-blue/10 text-logi-blue border border-logi-blue/20'],
 'rejected' => ['label' => 'Rejected', 'color' => 'bg-red-50/10 text-red-500 border border-red-500/20'],
 'in_transit' => ['label' => 'In Sea Transit', 'color' => 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20'],
 'ready_for_pickup' => ['label' => 'Ready For Pickup', 'color' => 'bg-logi-gold/10 text-logi-gold border border-logi-gold/20'],
 'delivered' => ['label' => 'Delivered Securely', 'color' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20']
 ];
 $status = $status_map[$shipment['admin_status']] ?? ['label' => $shipment['admin_status'], 'color' => 'bg-gray-500/10 text-gray-400 border border-gray-500/20'];
 ?>
 <span class="<?php echo $status['color']; ?> px-4 py-2 rounded font-heading text-xs font-bold uppercase tracking-wider block shadow-sm">
 <?php echo $status['label']; ?>
 </span>
 </div>
 </div>
 </div>
 
 <div class="p-8 md:p-10 space-y-10">
 <div>
 <h4 class="text-logi-navy font-heading font-bold uppercase tracking-widest text-xs mb-8 flex items-center gap-2">
 <i class="fas fa-route text-logi-blue"></i> Shipment Progress Status
 </h4>
 <div class="flex items-center justify-between relative px-2">
 <?php 
 $steps = ['pending_approval', 'approved', 'in_transit', 'ready_for_pickup', 'delivered'];
 $current_index = array_search($shipment['admin_status'], $steps);
 if ($current_index === false) $current_index = 0;
 ?>
 <?php foreach($steps as $index => $step): 
 $step_labels = [
 'pending_approval' => 'Booked',
 'approved' => 'Verified',
 'in_transit' => 'In Transit',
 'ready_for_pickup' => 'Arrival',
 'delivered' => 'Delivered'
 ];
 $is_completed = $index <= $current_index;
 ?>
 <div class="text-center flex-1 relative z-10">
 <div class="w-8 h-8 mx-auto rounded-full flex items-center justify-center transition-all duration-500 <?php echo $is_completed ? 'bg-logi-blue text-white shadow-md' : 'bg-gray-200 text-gray-400'; ?>">
 <i class="fas fa-check text-[10px]"></i>
 </div>
 <p class="text-[10px] md:text-xs font-heading font-bold uppercase mt-3 tracking-wider <?php echo $is_completed ? 'text-logi-navy' : 'text-gray-400'; ?>">
 <?php echo $step_labels[$step]; ?>
 </p>
 </div>
 <?php endforeach; ?>
 <div class="absolute top-4 left-0 w-full h-[3px] bg-gray-100 -z-0"></div>
 <div class="absolute top-4 left-0 h-[3px] bg-logi-blue transition-all duration-500 -z-0" style="width: <?php echo ($current_index / (count($steps)-1)) * 100; ?>%"></div>
 </div>
 </div>
 
 <div class="grid md:grid-cols-2 gap-8 pt-8 border-t border-gray-100">
 <div class="bg-logi-gray p-5 rounded border border-gray-100 flex items-start gap-4">
 <div class="w-10 h-10 bg-white shadow-sm flex items-center justify-center text-logi-blue rounded flex-shrink-0">
 <i class="fas fa-user-tag"></i>
 </div>
 <div>
 <p class="text-gray-400 text-[10px] font-heading font-bold uppercase tracking-widest mb-1">Consignee / Receiver</p>
 <p class="font-heading font-bold text-logi-navy text-base"><?php echo htmlspecialchars($shipment['receiver_name']); ?></p>
 <p class="text-gray-500 text-xs font-medium mt-0.5"><i class="fas fa-phone text-logi-gold/80 mr-1"></i> <?php echo htmlspecialchars($shipment['receiver_phone']); ?></p>
 </div>
 </div>
 
 <div class="bg-logi-gray p-5 rounded border border-gray-100 flex items-start gap-4">
 <div class="w-10 h-10 bg-white shadow-sm flex items-center justify-center text-logi-blue rounded flex-shrink-0">
 <i class="fas fa-ship"></i>
 </div>
 <div>
 <p class="text-gray-400 text-[10px] font-heading font-bold uppercase tracking-widest mb-1">Accredited Forwarder Partner</p>
 <p class="font-heading font-bold text-logi-navy text-base"><?php echo $shipment['company_name'] ?? 'Assignment Pending'; ?></p>
 <p class="text-gray-500 text-xs font-medium mt-0.5"><i class="fas fa-shield-halved text-emerald-600 mr-1"></i> Checked Security Corridor</p>
 </div>
 </div>
 </div>
 
 <div class="bg-logi-gray p-6 rounded border border-gray-100 flex gap-4 items-start">
 <div class="w-10 h-10 bg-white shadow-sm flex items-center justify-center text-logi-blue rounded flex-shrink-0">
 <i class="fas fa-map-location-dot"></i>
 </div>
 <div class="flex-1">
 <p class="text-gray-400 text-[10px] font-heading font-bold uppercase tracking-widest mb-1">Final Region Destination Drop-off</p>
 <p class="text-gray-700 text-sm font-medium leading-relaxed"><?php echo nl2br(htmlspecialchars($shipment['destination'])); ?></p>
 </div>
 </div>
 
 <div class="bg-logi-navy text-white px-6 py-4 rounded flex justify-between items-center border-l-4 border-logi-gold shadow-sm">
 <span class="font-heading text-xs font-bold uppercase tracking-wider text-gray-300 flex items-center gap-2">
 <i class="fas fa-calendar-check text-logi-gold"></i> Estimated Doorstep Arrival:
 </span>
 <span class="font-heading font-bold text-sm text-logi-gold tracking-wide">
 <?php echo $shipment['arrival_date'] ? date('M j, Y', strtotime($shipment['arrival_date'])) : 'In Operational Analysis'; ?>
 </span>
 </div>
 </div>
 </div>
 <?php endif; ?>
 </div>
</main>

<div class="w-full bg-white border-t border-gray-200 py-4 text-center text-[11px] font-bold text-gray-400 uppercase tracking-widest">
 &copy; 2026 B-Box Logistics Systems. Professional Solutions.
</div>

<div id="loginModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-logi-navy/90 backdrop-blur-sm px-4">
 <div class="bg-white rounded shadow-2xl w-full max-w-md overflow-hidden relative border-t-8 border-logi-gold">
 <button onclick="closeLogin()" class="absolute right-5 top-5 text-gray-400 hover:text-gray-600 transition">
 <i class="fas fa-times text-xl"></i>
 </button>
 <div id="loginContent" class="p-2"></div>
 </div>
</div>

<script>
 function openLogin(role, errorMsg = "") {
 const modal = document.getElementById('loginModal');
 const content = document.getElementById('loginContent');
 modal.classList.remove('hidden');
 modal.classList.add('flex');
 
 content.innerHTML = '<div class="p-12 text-center"><i class="fas fa-spinner fa-spin text-3xl text-logi-navy"></i></div>';
 
 fetch(`login.php?role=${role}&error=${encodeURIComponent(errorMsg)}`)
 .then(response => response.text())
 .then(data => { content.innerHTML = data; });
 }

 function closeLogin() {
 const modal = document.getElementById('loginModal');
 modal.classList.add('hidden');
 modal.classList.remove('flex');
 }

 window.onclick = function(event) {
 const modal = document.getElementById('loginModal');
 if (event.target == modal) closeLogin();
 }
</script>

</body>
</html>
