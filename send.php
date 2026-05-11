<?php
session_start();

if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
include('config.php'); 

if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT full_name, email FROM users WHERE user_id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_data = $user_query->get_result()->fetch_assoc();

$sender_name = $user_data['full_name'];
$sender_email = $user_data['email'];

// Fetch all active forwarders
$forwarder_query = $conn->query("SELECT * FROM forwarders WHERE status='Active'");
$all_forwarders = [];
while($f = $forwarder_query->fetch_assoc()) {
    $all_forwarders[] = $f;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send a Box | Balikbox</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-3px); }
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
                    <span class="text-xs text-blue-200 ml-2">Send Shipment</span>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <?php include('includes/notification_bell.php'); ?>
                <div class="flex items-center gap-3 bg-white/10 rounded-full px-4 py-2">
                    <i class="fas fa-user-circle text-blue-200 text-lg"></i>
                    <span class="text-sm font-medium text-white"><?php echo $_SESSION['username']; ?></span>
                </div>
                <a href="logout.php" class="bg-red-500/80 hover:bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-bold transition">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto pt-28 px-6 pb-12">
        <div class="max-w-5xl mx-auto">
            
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl">
                    <i class="fas fa-paper-plane text-white text-3xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white">Send a Balikbayan Box</h1>
                <p class="text-blue-200 mt-2">Fill out the form below to ship your box to the Philippines</p>
            </div>

            <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-5 text-white">
                    <h2 class="text-xl font-bold"><i class="fas fa-shipping-fast mr-2"></i> Standard Shipping Manifest</h2>
                    <p class="text-blue-100 text-sm mt-1">Please ensure all contact details and addresses are accurate</p>
                </div>

                <form action="process_send.php" method="POST" class="p-8">
                    <div class="grid md:grid-cols-2 gap-8">
                        
                        <div class="space-y-4">
                            <h3 class="text-lg font-bold text-blue-700 border-b pb-2 flex items-center gap-2">
                                <i class="fas fa-user"></i> Sender Information
                            </h3>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Full Name</label>
                                <input type="text" name="sender_name" value="<?php echo htmlspecialchars($sender_name); ?>" 
                                       class="w-full p-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Country Code</label>
                                    <select name="s_country_code" id="s_country_code" onchange="updatePlaceholder('s')" 
                                            class="w-full p-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-blue-500 outline-none">
                                        <option value="+63" data-pattern="^9\d{9}$" data-hint="9XX XXX XXXX">PH (+63)</option>
                                        <option value="+1" data-pattern="^\d{10}$" data-hint="XXX XXX XXXX">US (+1)</option>
                                        <option value="+44" data-pattern="^7\d{9}$" data-hint="7XXX XXXXXX">UK (+44)</option>
                                        <option value="+971" data-pattern="^5\d{8}$" data-hint="5X XXX XXXX">UAE (+971)</option>
                                        <option value="+81" data-pattern="^\d{10}$" data-hint="XX XXXX XXXX">JP (+81)</option>
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Phone Number</label>
                                    <input type="text" name="sender_phone" id="s_phone" required 
                                           class="w-full p-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-blue-500 outline-none">
                                    <p id="s_hint" class="text-[10px] text-gray-400 mt-1"></p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email Address</label>
                                <input type="email" name="sender_email" value="<?php echo htmlspecialchars($sender_email); ?>" required 
                                       class="w-full p-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Country of Origin</label>
                                <select name="s_country" id="s_country" onchange="updateSenderUI()" 
                                        class="w-full p-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-blue-500 outline-none">
                                    <option value="Philippines">Philippines</option>
                                    <option value="USA">USA</option>
                                    <option value="UK">UK</option>
                                    <option value="UAE">UAE</option>
                                    <option value="Japan">Japan</option>
                                </select>
                            </div>
                            <div id="s_dynamic_area" class="space-y-3"></div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Street / Unit / Building</label>
                                <input type="text" name="s_street" required 
                                       class="w-full p-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h3 class="text-lg font-bold text-yellow-600 border-b pb-2 flex items-center gap-2">
                                <i class="fas fa-user-friends"></i> Receiver Information
                            </h3>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Recipient Name</label>
                                <input type="text" name="receiver_name" required 
                                       class="w-full p-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-yellow-500 outline-none">
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Country Code</label>
                                    <select name="r_country_code" id="r_country_code" onchange="updatePlaceholder('r')" 
                                            class="w-full p-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-yellow-500 outline-none">
                                        <option value="+63" data-pattern="^9\d{9}$" data-hint="9XX XXX XXXX">PH (+63)</option>
                                        <option value="+1" data-pattern="^\d{10}$" data-hint="XXX XXX XXXX">US (+1)</option>
                                        <option value="+44" data-pattern="^7\d{9}$" data-hint="7XXX XXXXXX">UK (+44)</option>
                                        <option value="+971" data-pattern="^5\d{8}$" data-hint="5X XXX XXXX">UAE (+971)</option>
                                        <option value="+81" data-pattern="^\d{10}$" data-hint="XX XXXX XXXX">JP (+81)</option>
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Phone Number</label>
                                    <input type="text" name="receiver_phone" id="r_phone" required 
                                           class="w-full p-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-yellow-500 outline-none">
                                    <p id="r_hint" class="text-[10px] text-gray-400 mt-1"></p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Recipient Email (Optional)</label>
                                <input type="email" name="receiver_email" 
                                       class="w-full p-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-yellow-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Destination Country</label>
                                <select name="r_country" id="r_country" onchange="renderAddressFields('r')" 
                                        class="w-full p-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-yellow-500 outline-none">
                                    <option value="Philippines">Philippines</option>
                                    <option value="USA">USA</option>
                                    <option value="UK">UK</option>
                                    <option value="UAE">UAE</option>
                                    <option value="Japan">Japan</option>
                                </select>
                            </div>
                            <div id="r_dynamic_area" class="space-y-3"></div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">House / Building Details</label>
                                <input type="text" name="r_house_details" placeholder="e.g. House No. 123, Villa Maria Subdivision" 
                                       class="w-full p-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-yellow-500 outline-none">
                                <p class="text-[10px] text-gray-400 mt-1">House number, building name, subdivision, landmark</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Street / Unit / Building</label>
                                <input type="text" name="r_street" required 
                                       class="w-full p-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-yellow-500 outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 p-6 bg-gray-50 rounded-xl grid md:grid-cols-3 gap-6 border">
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase mb-2 block">Available Courier</label>
                            <select name="forwarder_id" id="forwarder_select" required 
                                    class="w-full p-3 border rounded-xl bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase mb-2 block">Box Size</label>
                            <select name="box_size" id="box_size" onchange="updatePrice()" required 
                                    class="w-full p-3 border rounded-xl bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="Small">Small (24"x18"x9")</option>
                                <option value="Medium">Medium (23"x20"x17")</option>
                                <option value="Large">Large (28"x18"x17")</option>
                                <option value="Extra Large">Extra Large (24"x18"x24")</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase mb-2 block">Estimated Total</label>
                            <div class="flex items-center">
                                <span id="currency_symbol" class="p-3 bg-gray-200 border border-r-0 rounded-l-xl font-bold text-blue-700 text-xl">₱</span>
                                <input type="text" name="price" id="price_display" readonly 
                                       class="w-full p-3 border rounded-r-xl bg-gray-100 font-bold text-blue-700 text-xl">
                                <input type="hidden" name="currency" id="currency_input" value="PHP">
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-4 rounded-xl font-bold text-lg hover:shadow-xl transition-all flex items-center justify-center gap-3">
                            <i class="fas fa-file-invoice"></i> CONFIRM SHIPMENT
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    const allCouriers = <?php echo json_encode($all_forwarders); ?>;
    
    const addressConfigs = {
        'Philippines': [
            { id: 'province', label: 'Province', placeholder: 'e.g. Southern Leyte' },
            { id: 'city', label: 'City / Municipality', placeholder: 'e.g. Sogod' },
            { id: 'locality', label: 'Barangay', placeholder: 'Enter Barangay' },
            { id: 'zip', label: 'Zip Code', placeholder: '6606' }
        ],
        'USA': [
            { id: 'province', label: 'State', placeholder: 'e.g. California' },
            { id: 'city', label: 'City', placeholder: 'e.g. Los Angeles' },
            { id: 'zip', label: 'Zip Code', placeholder: '90001' }
        ],
        'UK': [
            { id: 'city', label: 'Town/City', placeholder: 'e.g. London' },
            { id: 'zip', label: 'Postcode', placeholder: 'e.g. SW1A 1AA' }
        ],
        'UAE': [
            { id: 'province', label: 'Emirate', placeholder: 'e.g. Dubai' },
            { id: 'locality', label: 'Area/Community', placeholder: 'e.g. Al Barsha' },
            { id: 'zip', label: 'PO Box (Optional)', placeholder: '00000' }
        ],
        'Japan': [
            { id: 'province', label: 'Prefecture', placeholder: 'e.g. Tokyo' },
            { id: 'city', label: 'City/Ward', placeholder: 'e.g. Shinjuku' },
            { id: 'zip', label: 'Postal Code', placeholder: '160-0023' }
        ]
    };

    function renderAddressFields(prefix) {
        const country = document.getElementById(prefix + '_country').value;
        const container = document.getElementById(prefix + '_dynamic_area');
        const fields = addressConfigs[country] || addressConfigs['Philippines'];
        const ringColor = (prefix === 's') ? 'blue' : 'yellow';

        container.innerHTML = '';
        fields.forEach(f => {
            const div = document.createElement('div');
            div.innerHTML = `
                <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">${f.label}</label>
                <input type="text" name="${prefix}_${f.id}" placeholder="${f.placeholder}" required 
                       class="w-full p-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-${ringColor}-500 outline-none">
            `;
            container.appendChild(div);
        });
    }

    function filterCouriers() {
        const country = document.getElementById('s_country').value;
        const select = document.getElementById('forwarder_select');
        select.innerHTML = '';
        const filtered = allCouriers.filter(c => c.country_origin === country || c.country_origin === 'Global');
        filtered.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.forwarder_id;
            opt.text = c.company_name;
            select.add(opt);
        });
    }

    function updateSenderUI() {
        renderAddressFields('s');
        filterCouriers();
        updatePrice();
    }

    function updatePrice() {
        const size = document.getElementById('box_size').value;
        const country = document.getElementById('s_country').value;
        const priceDisplay = document.getElementById('price_display');
        const currencySymbol = document.getElementById('currency_symbol');
        const currencyInput = document.getElementById('currency_input');

        const basePrices = { 'Small': 99, 'Medium': 199, 'Large': 299, 'Extra Large': 499 };
        const config = {
            'Philippines': { symbol: '₱', rate: 1, code: 'PHP' },
            'USA': { symbol: '$', rate: 0.018, code: 'USD' },
            'UK': { symbol: '£', rate: 0.014, code: 'GBP' },
            'UAE': { symbol: 'د.إ', rate: 0.066, code: 'AED' },
            'Japan': { symbol: '¥', rate: 2.72, code: 'JPY' }
        }[country] || { symbol: '₱', rate: 1, code: 'PHP' };

        priceDisplay.value = (basePrices[size] * config.rate).toFixed(2);
        currencySymbol.innerText = config.symbol;
        currencyInput.value = config.code;
    }

    function updatePlaceholder(prefix) {
        const select = document.getElementById(prefix + '_country_code');
        const input = document.getElementById(prefix + '_phone');
        const hint = document.getElementById(prefix + '_hint');
        const opt = select.options[select.selectedIndex];
        input.placeholder = opt.getAttribute('data-hint');
        hint.innerText = "Format: " + opt.getAttribute('data-hint');
    }

    window.onload = () => {
        updateSenderUI();
        renderAddressFields('r');
        updatePlaceholder('s');
        updatePlaceholder('r');
    };
    </script>
</body>
</html>