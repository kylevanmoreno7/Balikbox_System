<?php
include('config.php'); 

// Check if ID is set in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Prepare to fetch shipment details safely
    $stmt = $conn->prepare("SELECT * FROM shipments WHERE shipment_id = ?");
    $stmt->bind_param("i", $id); // "i" because ID is an integer
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex justify-center py-10">
    <div class="bg-white w-[450px] rounded-lg shadow-lg p-6 border-t-8 border-blue-600">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">Confirm Shipment</h2>
        <p class="text-sm text-center text-gray-500 mb-6">Please review the details before proceeding to payment.</p>

        <div class="space-y-4">
            <div class="flex justify-between border-b pb-2">
                <span class="text-gray-500">Sender:</span>
              <span class="font-semibold"><?php echo $row['sender_full_name']; ?></span>
            </div>
            <div class="flex justify-between border-b pb-2">
                <span class="text-gray-500">Receiver:</span>
                <span class="font-semibold"><?php echo $row['receiver_name']; ?></span>
            </div>
            <div class="flex justify-between border-b pb-2">
                <span class="text-gray-500">Box Size:</span>
                <span class="font-semibold text-blue-600"><?php echo $row['box_size']; ?></span>
            </div>
            <div class="flex justify-between pt-4">
                <span class="text-xl font-bold">Total Amount:</span>
                <span class="text-xl font-bold text-green-600">₱<?php echo number_format($row['price'], 2); ?></span>
            </div>
        </div>

        <form action="payment.php" method="POST" class="mt-8 space-y-3">
    <input type="hidden" name="shipment_id" value="<?php echo $row['shipment_id']; ?>">
    
    <button type="submit" name="confirm_payment" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition">
        Confirm & Pay Now
    </button>
    
    <a href="send.php?id=<?php echo $id; ?>" class="block text-center w-full border border-gray-300 text-gray-600 py-3 rounded-lg hover:bg-gray-50">
        Go Back / Edit
    </a>
</form>
    </div>
</body>
</html>