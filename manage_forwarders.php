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

$message = "";

if (isset($_POST['add_forwarder'])) {
    $name = $conn->real_escape_string($_POST['company_name']);
    $contact = $conn->real_escape_string($_POST['contact_number']);
    
    $sql = "INSERT INTO forwarders (company_name, contact_number, status) VALUES ('$name', '$contact', 'Active')";
    if ($conn->query($sql)) {
        $message = "Forwarder added successfully!";
    }
}

if (isset($_GET['toggle_id'])) {
    $id = $_GET['toggle_id'];
    $current_status = $_GET['current_status'];
    $new_status = ($current_status == 'Active') ? 'Inactive' : 'Active';
    
    $stmt = $conn->prepare("UPDATE forwarders SET status = ? WHERE forwarder_id = ?");
    $stmt->bind_param("si", $new_status, $id);
    $stmt->execute();
    
    header("Location: manage_forwarders.php");
    exit();
}
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_res = $conn->query("SELECT COUNT(*) as total FROM forwarders");
$total_pages = ceil($count_res->fetch_assoc()['total'] / $limit);

$result = $conn->query("SELECT * FROM forwarders LIMIT $limit OFFSET $offset");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Forwarders | Admin</title>
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
                    <span class="text-xs text-blue-200 ml-2">Manage Partners</span>
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
        <div class="max-w-5xl mx-auto">
            
            <div class="mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mb-3 shadow-xl">
                    <i class="fas fa-building text-white text-2xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white">Logistics Partners</h1>
                <p class="text-blue-200 mt-1">Manage courier and logistics partners</p>
            </div>

            <?php if($message): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-xl mb-6"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl p-6 mb-8">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Add New Logistics Partner</h2>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="text" name="company_name" placeholder="Company Name (e.g. LBC)" required 
                           class="p-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-green-500 outline-none">
                    <input type="text" name="contact_number" placeholder="Contact Number" 
                           class="p-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-green-500 outline-none">
                    <button type="submit" name="add_forwarder" 
                            class="bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold py-3 rounded-xl hover:shadow-lg transition">
                        <i class="fas fa-plus mr-2"></i> Register Partner
                    </button>
                </form>
            </div>

            <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-800 to-gray-900 text-white uppercase text-xs">
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Company Name</th>
                            <th class="px-6 py-4">Contact</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-gray-400 font-mono"><?php echo $row['forwarder_id']; ?></td>
                            <td class="px-6 py-4 font-bold text-gray-800"><?php echo htmlspecialchars($row['company_name']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($row['contact_number']) ?: 'N/A'; ?></td>
                            <td class="px-6 py-4 text-center">
                                <?php if($row['status'] == 'Active'): ?>
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Active</span>
                                <?php else: ?>
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="manage_forwarders.php?toggle_id=<?php echo $row['forwarder_id']; ?>&current_status=<?php echo $row['status']; ?>" 
                                   class="text-xs font-bold py-2 px-4 rounded-xl border transition <?php echo $row['status'] == 'Active' ? 'text-red-500 border-red-200 hover:bg-red-50' : 'text-green-500 border-green-200 hover:bg-green-50'; ?>">
                                    <?php echo $row['status'] == 'Active' ? 'Deactivate' : 'Activate'; ?>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-4 flex justify-center gap-2">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="px-3 py-1 rounded border <?php echo $page == $i ? 'bg-green-500 text-white' : 'bg-white'; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</body>
</html>