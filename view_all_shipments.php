<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Admin') { 
    header("Location: index.php"); 
    exit(); 
}
include('config.php'); 

if ($conn->connect_error) { 
    die("Connection failed: " . $conn->connect_error); 
}

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_res = $conn->query("SELECT COUNT(*) as total FROM shipments");
$total_rows = $total_res->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

$sql = "SELECT s.*, u.username as sender_username, f.company_name 
        FROM shipments s
        LEFT JOIN users u ON s.sender_id = u.user_id
        LEFT JOIN forwarders f ON s.forwarder_id = f.forwarder_id
        ORDER BY s.created_at DESC 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Shipments | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1e40af 100%);">
    
    <nav class="bg-white/10 backdrop-blur-md shadow-lg px-6 py-4 fixed w-full top-0 z-50 border-b border-white/10">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="dashboard.php" class="bg-gray-500/80 hover:bg-gray-600 text-white px-4 py-2 rounded-xl text-sm font-bold transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-box-open text-white text-xl"></i>
                    </div>
                    <span class="font-bold text-2xl text-white tracking-tight">BALIKBOX</span>
                    <span class="text-xs text-blue-200 ml-2">All Shipments</span>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <?php include('includes/notification_bell.php'); ?>
                <div class="flex items-center gap-3 bg-white/10 rounded-full px-4 py-2">
                    <i class="fas fa-user-shield text-blue-200 text-lg"></i>
                    <span class="text-sm font-medium text-white">Admin</span>
                </div>
                <a href="logout.php" class="bg-red-500/80 hover:bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-bold transition">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto pt-28 px-6 pb-12">
        <div class="max-w-7xl mx-auto">
            
            <div class="mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-3 shadow-xl">
                    <i class="fas fa-list-alt text-white text-2xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white">Master Shipment List</h1>
                <p class="text-blue-200 mt-1">View and manage all shipments in the system</p>
            </div>

            <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white uppercase text-xs tracking-wider">
                                <th class="px-6 py-4">Tracking No</th>
                                <th class="px-6 py-4">Sender</th>
                                <th class="px-6 py-4">Receiver</th>
                                <th class="px-6 py-4">Forwarder</th>
                                <th class="px-6 py-4">Box Size</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 font-mono font-bold text-blue-600"><?php echo $row['tracking_no']; ?></td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['sender_full_name']); ?></div>
                                            <div class="text-xs text-gray-500"><?php echo $row['sender_username']; ?></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold"><?php echo htmlspecialchars($row['receiver_name']); ?></div>
                                            <div class="text-xs text-gray-400 truncate max-w-xs"><?php echo htmlspecialchars(substr($row['destination'], 0, 50)); ?></div>
                                        </td>
                                        <td class="px-6 py-4 text-sm"><?php echo $row['company_name'] ?? 'N/A'; ?></td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 bg-gray-100 rounded-lg text-xs font-semibold"><?php echo $row['box_size']; ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php 
                                                $statusColor = "bg-gray-100 text-gray-600";
                                                $statusMap = [
                                                    'In Transit' => 'bg-blue-100 text-blue-600',
                                                    'Delivered' => 'bg-green-100 text-green-600',
                                                    'Pending' => 'bg-yellow-100 text-yellow-600',
                                                    'Paid' => 'bg-purple-100 text-purple-600'
                                                ];
                                                $statusColor = $statusMap[$row['status']] ?? "bg-gray-100 text-gray-600";
                                            ?>
                                            <span class="<?php echo $statusColor; ?> px-3 py-1 rounded-full text-xs font-bold uppercase"><?php echo $row['status']; ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="receipt.php?id=<?php echo $row['shipment_id']; ?>" class="text-blue-500 hover:text-blue-800 text-sm font-medium inline-flex items-center gap-1">
                                                <i class="fas fa-file-invoice"></i> Receipt
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                        <i class="fas fa-box-open text-4xl mb-2"></i>
                                        <p>No shipments found in the database.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="p-3 flex justify-center gap-2 border-t bg-gray-50/50">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="px-4 py-2 bg-white border rounded-xl text-sm hover:bg-gray-100 transition">Previous</a>
                <?php endif; ?>

                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" 
                    class="px-4 py-2 rounded-xl border text-sm font-bold transition <?php echo $page == $i ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 hover:bg-gray-100'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="px-4 py-2 bg-white border rounded-xl text-sm hover:bg-gray-100 transition">Next</a>
                <?php endif; ?>
            </div>
            </div>
        </div>
    </div>
</body>
</html>