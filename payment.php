<?php
include('config.php');

if (!isset($_POST['shipment_id'])) {
    header("Location: dashboard.php");
    exit();
}

$s_id = mysqli_real_escape_string($conn, $_POST['shipment_id']);

// Fetch shipment details for the Order Summary
$query = "SELECT * FROM shipments WHERE shipment_id = '$s_id'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

// Get currency symbol from receipt logic
$currency_symbols = ['PHP' => '₱', 'USD' => '$', 'GBP' => '£', 'AED' => 'د.إ', 'JPY' => '¥'];
$symbol = $currency_symbols[$row['currency']] ?? '₱';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Payment | B-Box Logistics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">

    <header class="bg-white border-b px-8 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-blue-800">B-Box <span class="text-gray-400 font-light">Logistics</span></h1>
        <div class="text-blue-600 font-semibold">Secure Checkout</div>
    </header>

    <main class="max-w-6xl mx-auto mt-10 px-4">
        <div class="flex justify-center items-center mb-10 space-x-4">
            <div class="flex items-center text-blue-600 font-medium">
                <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs mr-2">✓</span> Shipment
            </div>
            <div class="w-12 h-px bg-gray-300"></div>
            <div class="flex items-center text-blue-800 font-bold">
                <span class="w-6 h-6 rounded-full border-2 border-blue-800 flex items-center justify-center text-xs mr-2">2</span> Payment
            </div>
            <div class="w-12 h-px bg-gray-300"></div>
            <div class="flex items-center text-gray-400">
                <span class="w-6 h-6 rounded-full border-2 border-gray-300 flex items-center justify-center text-xs mr-2">3</span> Complete
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <div class="flex-grow space-y-6">
                <div class="bg-white p-8 rounded-lg shadow-sm border border-gray-200">
                    <h2 class="text-xl font-bold text-gray-700 mb-6">How would you like to pay?</h2>
                    
                    <form action="process_payment.php" method="POST">
                        <input type="hidden" name="shipment_id" value="<?php echo $s_id; ?>">
                        
                        <div class="grid grid-cols-2 gap-4 mb-8">
                            <label class="border-2 border-blue-100 rounded-xl p-4 flex items-center justify-between cursor-pointer hover:border-blue-500 transition active:bg-blue-50">
                                <input type="radio" name="pay_method" value="card" checked class="hidden peer">
                                <span class="font-bold text-gray-600"><i class="fa-solid fa-credit-card mr-2 text-blue-600"></i> Credit Card</span>
                                <div class="flex gap-1">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" class="h-4" alt="Visa">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" class="h-4" alt="MasterCard">
                                </div>
                            </label>
                            
                            <label class="border-2 border-gray-100 rounded-xl p-4 flex items-center justify-between cursor-pointer hover:border-blue-500 transition">
                                <input type="radio" name="pay_method" value="paypal" class="hidden peer">
                                <span class="font-bold text-gray-600"><i class="fa-brands fa-paypal mr-2 text-blue-500"></i> PayPal</span>
                                <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" class="h-4" alt="PayPal">
                            </label>
                        </div>

                        <div class="space-y-4">
                            <input type="text" placeholder="Card Number" class="w-full border p-3 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                            <div class="flex gap-4">
                                <input type="text" placeholder="MM/YY" class="w-1/2 border p-3 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                                <input type="text" placeholder="CVV" class="w-1/2 border p-3 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                        </div>

                        <button type="submit" name="execute_payment" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-lg mt-8 transition-all">
                            Continue to secure payment
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="confirm_shipment.php?id=<?php echo $s_id; ?>" class="text-gray-400 text-sm hover:underline">Cancel payment</a>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-96">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 p-4 border-b">
                        <h3 class="font-bold text-gray-700">Order Summary</h3>
                        <p class="text-xs text-gray-400 uppercase tracking-widest">Tracking: <?php echo $row['tracking_no']; ?></p>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center text-sm">
                            <div class="flex items-center">
                                <div class="bg-blue-50 p-2 rounded mr-3"><i class="fa-solid fa-box text-blue-600"></i></div>
                                <div>
                                    <p class="font-bold text-gray-800">Balikbayan Box</p>
                                    <p class="text-xs text-gray-500"><?php echo $row['box_size']; ?></p>
                                </div>
                            </div>
                            <span class="font-bold text-gray-700"><?php echo $symbol . number_format($row['price'], 2); ?></span>
                        </div>
                        
                        <div class="border-t pt-4 space-y-2">
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>Shipping</span>
                                <span>Free</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>Processing Fee</span>
                                <span><?php echo $symbol; ?>0.00</span>
                            </div>
                        </div>

                        <div class="border-t-2 border-dashed pt-4 flex justify-between items-center">
                            <span class="text-lg font-bold text-gray-800">Total</span>
                            <span class="text-2xl font-black text-blue-700"><?php echo $symbol . number_format($row['price'], 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="mt-20 py-10 border-t bg-white text-center">
        <p class="text-xs text-gray-400">Payment processed by B-Box Secure Gateway</p>
        <div class="flex justify-center gap-4 mt-2 text-[10px] text-gray-500 uppercase tracking-widest">
            <a href="#">Privacy Policy</a>
            <span>•</span>
            <a href="#">Security</a>
            <span>•</span>
            <a href="#">Terms</a>
        </div>
    </footer>
</body>
</html>