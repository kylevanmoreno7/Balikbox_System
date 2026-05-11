<?php
session_start();
include('config.php'); 

// 1. Safety check: Ensure user is logged in
if (!isset($_SESSION['user_id'])) { 
    header("Location: index.php"); 
    exit(); 
}

$user_id = $_SESSION['user_id'];
$shipment_id = $_GET['id'] ?? null;

if (!$shipment_id) {
    die("No shipment ID provided.");
}

// 2. Fetch REAL data from database and verify ownership
// This prevents users from editing someone else's shipment via the URL
$stmt = $conn->prepare("SELECT * FROM shipments WHERE shipment_id = ? AND sender_id = ?");
$stmt->bind_param("ii", $shipment_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $shipment = $result->fetch_assoc();
} else {
    die("Shipment not found or you do not have permission to edit it.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Edit Shipment - B-Box</title>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="bg-white shadow-xl rounded-xl w-full max-w-2xl overflow-hidden">
        <div class="bg-blue-600 p-6 text-center">
            <h1 class="text-2xl font-bold text-white uppercase tracking-wide">Edit Shipment Details</h1>
            <p class="text-blue-100 text-sm">Update the information below for tracking #BBOX-12345</p>
        </div>

        <form action="update_process.php" method="POST" class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                
               <div class="space-y-4">
                    <label class="block">
                        <span class="text-gray-500 uppercase text-xs font-bold tracking-widest">Sender Name</span>
                        <input type="text" name="sender" 
                            value="<?php echo htmlspecialchars($shipment['sender_full_name']); ?>" 
                            class="mt-1 block w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-lg font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                    </label>
                </div>

                <div class="space-y-4">
                    <label class="block">
                        <span class="text-gray-500 uppercase text-xs font-bold tracking-widest">Receiver Name</span>
                        <input type="text" name="receiver" 
                            value="<?php echo htmlspecialchars($shipment['receiver_name']); ?>" 
                            class="mt-1 block w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-lg font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                    </label>
                </div>

                <div class="space-y-4">
                    <label class="block">
                        <span class="text-gray-500 uppercase text-xs font-bold tracking-widest">Box Size</span>
                        <select name="box_size" id="box_size" onchange="updatePrice()" class="mt-1 block w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-lg font-medium focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                            <option value="Small" <?php echo ($shipment['box_size'] == 'Small') ? 'selected' : ''; ?>>Small</option>
                            <option value="Medium" <?php echo ($shipment['box_size'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                            <option value="Large" <?php echo ($shipment['box_size'] == 'Large') ? 'selected' : ''; ?>>Large</option>
                            <option value="Extra Large" <?php echo ($shipment['box_size'] == 'Extra Large') ? 'selected' : ''; ?>>Extra Large</option>
                        </select>
                    </label>
                </div>

                <div class="space-y-4">
                    <label class="block">
                        <span class="text-gray-500 uppercase text-xs font-bold tracking-widest">Total Amount (₱)</span>
                        <input type="number" name="price" id="price_display" value="<?php echo $shipment['price']; ?>" readonly
                                class="mt-1 block w-full px-4 py-3 bg-blue-50 border border-blue-200 rounded-lg text-xl font-bold text-blue-700 outline-none">
                    </label>
                </div>
            </div>

            <div class="flex flex-col gap-3">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-lg shadow-lg transition-transform active:scale-95 text-lg">
                    Update Shipment Details
                </button>
                <a href="send.php?id=<?php echo $shipment_id; ?>" 
                    class="w-full text-center py-3 text-gray-500 font-medium hover:text-gray-700 transition-colors">
                        Cancel and Go Back
                    </a>
            </div>
        </form>
    </div>
    <script>
function updatePrice() {
    const size = document.getElementById('box_size').value;
    const priceDisplay = document.getElementById('price_display');

    // These should match the base prices in your send.php
    const basePrices = { 
        'Small': 99, 
        'Medium': 199, 
        'Large': 299, 
        'Extra Large': 499 
    };

    // Since this is an edit page, we'll use the country from the database 
    // or default to PHP rate (1) if you want to keep it simple.
    const rate = 1; 

    if (basePrices[size]) {
        priceDisplay.value = (basePrices[size] * rate).toFixed(2);
    }
}
</script>

</body>
</html>