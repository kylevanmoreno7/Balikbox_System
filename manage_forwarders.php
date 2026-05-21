<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Admin') { 
    header("Location: index.php"); 
    exit(); 
}

include('config.php'); 
include('includes/notifications.php');

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
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; }
        
        /* Matching Mesh Background from view_all_shipments.php */
        .bg-mesh {
            background-image: 
                radial-gradient(at 0% 0%, rgba(30, 58, 138, 0.4) 0, transparent 50%), 
                radial-gradient(at 100% 100%, rgba(88, 28, 135, 0.15) 0, transparent 50%);
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
    </style>
</head>
<body class="min-h-screen bg-mesh text-slate-100 antialiased flex">
    
    <?php include('sidebar.php'); ?>

    <main class="flex-1 ml-72 p-8 lg:p-12">
        <div class="max-w-6xl mx-auto">
            
            <header class="mb-10">
                <div class="flex items-center gap-4 mb-2">
                    <div class="w-12 h-12 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/5">
                        <i class="fas fa-truck-loading text-emerald-400 text-xl"></i>
                    </div>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight">Logistics Partners</h1>
                </div>
                <p class="text-slate-400">Add and manage courier service providers for BALIKBOX operations.</p>
            </header>

            <?php if($message): ?>
                <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-4 rounded-2xl mb-8 flex items-center gap-3 animate-pulse">
                    <i class="fas fa-check-circle text-lg"></i>
                    <span class="font-bold text-sm uppercase tracking-wide"><?php echo $message; ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-slate-800/30 backdrop-blur-md border border-white/10 rounded-3xl p-8 mb-10 shadow-2xl">
                <h2 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-6">Partner Registration</h2>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-blue-400 uppercase ml-1">Company Name</label>
                        <input type="text" name="company_name" placeholder="e.g. LBC Express" required 
                               class="w-full p-4 rounded-2xl bg-slate-900/50 border border-white/5 text-white placeholder-slate-600 focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 outline-none transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-blue-400 uppercase ml-1">Contact Number</label>
                        <input type="text" name="contact_number" placeholder="09XX-XXX-XXXX" 
                               class="w-full p-4 rounded-2xl bg-slate-900/50 border border-white/5 text-white placeholder-slate-600 focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 outline-none transition-all">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" name="add_forwarder" 
                                class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs uppercase tracking-widest py-4 rounded-2xl shadow-xl shadow-emerald-900/20 transition-all active:scale-95 flex items-center justify-center gap-2">
                            <i class="fas fa-plus-circle"></i> Register Partner
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-slate-800/30 backdrop-blur-md border border-white/10 rounded-3xl shadow-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white/5 border-b border-white/5 text-slate-500 uppercase text-[10px] font-black tracking-widest">
                                <th class="px-8 py-5">Partner ID</th>
                                <th class="px-8 py-5">Company Info</th>
                                <th class="px-8 py-5">Contact</th>
                                <th class="px-8 py-5 text-center">Status</th>
                                <th class="px-8 py-5 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-white/5 transition-all group">
                                <td class="px-8 py-6 text-slate-500 font-mono text-xs">#<?php echo str_pad($row['forwarder_id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-purple-400">
                                            <i class="fas fa-building text-xs"></i>
                                        </div>
                                        <span class="font-bold text-white text-sm tracking-tight"><?php echo htmlspecialchars($row['company_name']); ?></span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="text-xs text-slate-400 font-medium tracking-wide">
                                        <i class="fas fa-phone text-[10px] mr-2 opacity-50"></i><?php echo htmlspecialchars($row['contact_number']) ?: 'No Contact'; ?>
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <?php if($row['status'] == 'Active'): ?>
                                        <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-tighter">Active</span>
                                    <?php else: ?>
                                        <span class="bg-red-500/10 text-red-400 border border-red-500/20 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-tighter">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <a href="manage_forwarders.php?toggle_id=<?php echo $row['forwarder_id']; ?>&current_status=<?php echo $row['status']; ?>" 
                                       class="inline-flex items-center gap-2 text-[10px] font-black py-2 px-5 rounded-xl border transition-all <?php echo $row['status'] == 'Active' ? 'text-red-400 border-red-500/20 hover:bg-red-500/10' : 'text-emerald-400 border-emerald-500/20 hover:bg-emerald-500/10'; ?>">
                                        <i class="fas <?php echo $row['status'] == 'Active' ? 'fa-toggle-on' : 'fa-toggle-off'; ?>"></i>
                                        <?php echo $row['status'] == 'Active' ? 'DEACTIVATE' : 'ACTIVATE'; ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-10 flex justify-center items-center gap-3">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" 
                       class="w-10 h-10 flex items-center justify-center rounded-xl font-bold text-xs transition-all <?php echo $page == $i ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-slate-800/50 text-slate-500 border border-white/5 hover:bg-slate-700'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>

        </div>
    </main>
</body>
</html>
