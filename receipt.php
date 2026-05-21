<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
include('config.php'); 

$id = $conn->real_escape_string($_GET['id']);
// Joins with forwarders to get the company name
$query = "SELECT s.*, f.company_name FROM shipments s 
          JOIN forwarders f ON s.forwarder_id = f.forwarder_id 
          WHERE s.shipment_id = '$id'";
$result = $conn->query($query);
$data = $result->fetch_assoc();
$sender_display = $data['sender_full_name'];
$currency_symbols = [
    'PHP' => '₱',
    'USD' => '$',
    'GBP' => '£',
    'AED' => 'د.إ',
    'JPY' => '¥'
];
$symbol = $currency_symbols[$data['currency']] ?? '₱';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction Receipt | <?php echo $data['tracking_no']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; padding: 0; }
            .receipt-card { background: white !important; border: 2px dashed #ccc !important; color: black !important; box-shadow: none !important; }
            .text-slate-400, .text-blue-400 { color: #666 !important; }
            .text-white { color: black !important; }
            .bg-slate-800\/50 { background: transparent !important; }
        }
    </style>
</head>
<body class="bg-slate-900 py-10 px-4 min-h-screen text-slate-100">
    <div class="max-w-xl mx-auto bg-slate-800/50 backdrop-blur-md p-10 border border-white/10 rounded-3xl shadow-2xl relative receipt-card" id="printable-area">
        
        <div class="text-center mb-8">
            <p class="text-slate-400 italic text-sm tracking-widest uppercase font-semibold">Official Transaction Receipt</p>
            <div class="h-px bg-gradient-to-r from-transparent via-white/20 to-transparent mt-4"></div>
        </div>

        <div class="flex justify-between items-start mb-10">
            <div>
                <p class="text-slate-400 font-bold text-xs uppercase tracking-wider">Transaction ID</p>
                <p class="text-blue-400 font-bold text-xl font-mono"><?php echo $data['tracking_no']; ?></p>
            </div>
            <div class="text-right">
                <p class="text-slate-400 font-bold text-xs uppercase tracking-wider">Date/Time</p>
                <p class="text-slate-200 text-sm font-medium"><?php echo date("F j, Y, g:i a", strtotime($data['created_at'])); ?></p>
            </div>
        </div>

        <div class="flex justify-between gap-8 mb-10">
            <div class="flex-1">
                <p class="text-blue-400 font-bold text-[11px] uppercase mb-1">Sender</p>
                <p class="font-black text-white text-xl leading-tight"><?php echo htmlspecialchars($sender_display); ?></p>
                <p class="text-xs text-gray-400 mt-1 italic">Confirmed Address</p>
            </div>
            <div class="flex-1 text-right">
                <p class="text-orange-400 font-bold text-[11px] uppercase mb-1">Receiver</p>
                <p class="font-black text-white text-xl leading-tight"><?php echo htmlspecialchars($data['receiver_name']); ?></p>
                <p class="text-xs text-slate-400 mt-1 italic"><?php echo htmlspecialchars($data['destination']); ?></p>
            </div>
        </div>

        <div class="border-t border-white/5 pt-4 mb-4">
            <p class="text-slate-500 font-bold text-[10px] uppercase">Logistics Partner</p>
            <p class="font-bold text-slate-200 uppercase tracking-wide"><i class="fas fa-truck-fast mr-2 text-blue-500"></i><?php echo $data['company_name']; ?></p>
        </div>

        <div class="bg-slate-900/50 rounded-2xl p-6 border border-white/5 flex justify-between items-center mb-6">
            <div>
                <p class="font-black text-white text-xl leading-none">Box Size</p>
                <p class="text-slate-400 text-sm mt-2 uppercase tracking-widest font-bold"><?php echo $data['box_size']; ?></p>
            </div>
            <div class="text-green-400 font-black text-3xl">
                <?php echo $symbol . number_format($data['price'], 2); ?>
            </div>
        </div>

        <div class="flex justify-between items-center border-t border-white/10 pt-6 mb-10">
            <h2 class="font-black text-white text-2xl tracking-tight uppercase">Total Paid</h2>
            <p class="text-blue-400 font-black text-3xl tracking-tighter">
                <?php echo $symbol . number_format($data['price'], 2); ?>
            </p>
        </div>

        <div class="space-y-4 no-print">
            <button onclick="window.print()" class="w-full bg-slate-700 hover:bg-slate-600 text-white py-4 rounded-xl font-bold flex justify-center items-center transition-all text-lg shadow-lg">
                <i class="fas fa-print mr-2"></i> Print Receipt
            </button>
            <a href="dashboard.php" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-4 rounded-xl font-bold flex justify-center items-center transition-all text-lg shadow-lg shadow-blue-600/20">
                Return to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
