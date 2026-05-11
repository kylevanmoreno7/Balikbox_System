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
</head>
<body class="bg-gray-100 py-10">
    <div class="max-w-xl mx-auto bg-white p-10 border-2 border-dashed border-gray-300 shadow-sm relative" id="printable-area">
        
        <div class="text-center mb-8">
            <p class="text-gray-400 italic text-sm">Official Transaction Receipt</p>
            <div class="h-px bg-gray-200 mt-4"></div>
        </div>

        <div class="flex justify-between items-start mb-10">
            <div>
                <p class="text-gray-500 font-bold text-xs uppercase tracking-wider">Transaction ID</p>
                <p class="text-blue-600 font-bold text-xl font-mono"><?php echo $data['tracking_no']; ?></p>
            </div>
            <div class="text-right">
                <p class="text-gray-500 font-bold text-xs uppercase tracking-wider">Date/Time</p>
                <p class="text-gray-700 text-sm font-medium"><?php echo date("F j, Y, g:i a", strtotime($data['created_at'])); ?></p>
            </div>
        </div>

        <div class="flex justify-between gap-8 mb-10">
            <div class="flex-1">
                <p class="text-blue-600 font-bold text-[11px] uppercase mb-1">Sender</p>
                <p class="font-black text-gray-800 text-xl leading-tight"><?php echo htmlspecialchars($sender_display); ?></p>
                <p class="text-xs text-gray-400 mt-1 italic">Confirmed Address</p>
            </div>
            <div class="flex-1 text-right">
                <p class="text-yellow-600 font-bold text-[11px] uppercase mb-1">Receiver</p>
                <p class="font-black text-gray-800 text-xl leading-tight"><?php echo htmlspecialchars($data['receiver_name']); ?></p>
                <p class="text-xs text-gray-400 mt-1 italic"><?php echo htmlspecialchars($data['destination']); ?></p>
            </div>
        </div>

        <div class="border-t border-gray-100 pt-4 mb-4">
            <p class="text-gray-500 font-bold text-[10px] uppercase">Logistics Partner</p>
            <p class="font-bold text-gray-700 uppercase tracking-wide"><i class="fas fa-truck-fast mr-2 text-blue-600"></i><?php echo $data['company_name']; ?></p>
        </div>

        <div class="bg-gray-50 rounded-xl p-6 flex justify-between items-center mb-6">
    <div>
        <p class="font-black text-gray-800 text-xl leading-none">Box Size</p>
        <p class="text-gray-400 text-sm mt-1"><?php echo $data['box_size']; ?></p>
    </div>
    <div class="text-green-700 font-black text-3xl">
        <?php echo $symbol . number_format($data['price'], 2); ?>
    </div>
</div>

<div class="flex justify-between items-center border-t-2 border-gray-100 pt-6 mb-10">
    <h2 class="font-black text-blue-900 text-2xl tracking-tight uppercase">Total Paid</h2>
    <p class="text-blue-900 font-black text-3xl tracking-tighter">
        <?php echo $symbol . number_format($data['price'], 2); ?>
    </p>
</div>

        <div class="space-y-4 no-print">
            <button onclick="window.print()" class="w-full bg-[#1e293b] text-white py-4 rounded-xl font-bold flex justify-center items-center hover:bg-black transition text-lg">
                <i class="fas fa-print mr-2"></i> Print Receipt
            </button>
            <a href="dashboard.php" class="w-full bg-[#2563eb] text-white py-4 rounded-xl font-bold flex justify-center items-center hover:bg-blue-700 transition text-lg">
                Return to Dashboard
            </a>
        </div>
    </div>

    <style>
        @media print {
            .no-print { display: none; }
            body { background: white; padding: 0; }
            .border-dashed { border-style: dashed !important; }
        }
    </style>
</body>
</html>