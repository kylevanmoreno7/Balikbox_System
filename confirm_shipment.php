<?php
include('config.php'); 

// Check if ID is set in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Prepare to fetch shipment details safely
    $stmt = $conn->prepare("SELECT * FROM shipments WHERE shipment_id = ?");
    $stmt->bind_param("i", $id); 
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        die("Shipment not found.");
    }
} else {
    die("No shipment ID provided.");
}
// Map the currency code to its symbol
$currency_symbols = [
    'PHP' => '₱',
    'USD' => '$',
    'GBP' => '£',
    'AED' => 'د.إ',
    'JPY' => '¥'
];
$symbol = $currency_symbols[$row['currency']] ?? '₱';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Shipment | Balikbox</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-900 flex items-center justify-center py-10 px-4 text-slate-100">
    
    <div class="bg-slate-800/50 border border-white/10 w-full max-w-[480px] rounded-3xl shadow-2xl p-8 backdrop-blur-sm">
        
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600/20 rounded-2xl mb-4">
                <i class="fas fa-file-invoice-dollar text-blue-500 text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Confirm Shipment</h2>
            <p class="text-slate-400 text-sm mt-2">Review your shipment details and proceed to payment.</p>
        </div>

        <div class="space-y-4">
            <div class="flex justify-between items-center border-b border-white/5 pb-3">
                <span class="text-slate-400 text-sm">Sender</span>
                <span class="font-medium text-white"><?php echo htmlspecialchars($row['sender_full_name']); ?></span>
            </div>
            <div class="flex justify-between items-center border-b border-white/5 pb-3">
                <span class="text-slate-400 text-sm">Receiver</span>
                <span class="font-medium text-white"><?php echo htmlspecialchars($row['receiver_name']); ?></span>
            </div>
            <div class="flex justify-between items-center border-b border-white/5 pb-3">
                <span class="text-slate-400 text-sm">Box Size</span>
                <span class="px-3 py-1 bg-blue-500/10 text-blue-400 rounded-full text-xs font-bold uppercase tracking-wider">
                    <?php echo htmlspecialchars($row['box_size']); ?>
                </span>
            </div>
            
            <div class="mt-6 bg-gradient-to-br from-blue-600/10 to-purple-600/10 rounded-2xl p-5 border border-white/5 flex justify-between items-center">
                <span class="text-slate-300 font-semibold">Total Amount</span>
                <span class="text-2xl font-extrabold text-green-400"><?php echo $symbol . number_format($row['price'], 2); ?></span>
            </div>
        </div>

        <form action="payment.php" method="POST" class="mt-8 space-y-4">
            <input type="hidden" name="shipment_id" value="<?php echo $row['shipment_id']; ?>">
            
            <button type="submit" name="confirm_payment" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-xl transition-all shadow-lg shadow-blue-600/20 active:scale-[0.98]">
                Confirm & Pay Now
            </button>
            
            <a href="send.php?id=<?php echo $id; ?>" class="block text-center w-full py-4 text-slate-400 hover:text-white transition-colors text-sm font-medium">
                <i class="fas fa-arrow-left mr-2"></i> Go Back / Edit
            </a>
        </form>
    </div>
</body>
</html>
