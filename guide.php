<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shipping Guide | Balikbox</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        html { scroll-behavior: smooth; }
        @layer utilities { .nav-gap { gap: 5rem; } }
        .menu-active { display: flex !important; }
        .rotate-180 { transform: rotate(180deg); }
    </style>
</head>
<body class="min-h-screen" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1e40af 100%);">
    <nav class="bg-white/10 backdrop-blur-md shadow-lg px-6 py-4 fixed w-full top-0 z-50 border-b border-white/10">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center shadow-lg">
                    <a href="index.php" class="fas fa-box-open text-white text-xl"></a>
                </div>
                <a href="index.php" class="font-bold text-2xl text-white tracking-tight">BALIKBOX</a>
            </div>
            <div class="hidden md:flex nav-gap">
                <a href="index.php" class="text-white hover:text-blue-300 font-medium">Home</a>
                <a href="track_view.php" class="text-white hover:text-blue-300 font-medium">Track</a>
                <a href="index.php#about" class="text-white hover:text-blue-300 font-medium">About Us</a>
                <div class="relative group">
                    <button onclick="toggleHelpMenu(event)" class="text-white hover:text-blue-300 font-medium flex items-center gap-1.5 focus:outline-none">
                        Help <i id="helpArrow" class="fas fa-chevron-down text-xs opacity-70 mt-0.5 transition-transform duration-200"></i>
                    </button>
                    <div id="helpDropdown" class="absolute left-0 top-full mt-2 w-48 bg-white/10 backdrop-blur-md rounded-xl shadow-xl border border-white/10 py-2 group-hover:flex hidden flex-col z-[110]">
                        <a href="faqs.php" class="px-5 py-2 text-white hover:bg-white/10 text-sm">FAQs</a>
                        <a href="contact.php" class="px-5 py-2 text-white hover:bg-white/10 text-sm">Contact Support</a>
                        <a href="guide.php" class="px-5 py-2 text-white hover:bg-white/10 text-sm">Shipping Guide</a>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-1 text-sm font-bold text-white pr-10">
                <a href="register.php" class="hover:text-blue-300 transition">Sign Up</a>
            </div>
        </div>
    </nav>

    <div class="pt-32 pb-20 container mx-auto px-6 max-w-5xl">
        <h1 class="text-4xl font-bold text-white mb-4 text-center">Shipping Guide</h1>
        <p class="text-blue-200 text-center mb-12">Professional doorstep delivery in 5 simple steps.</p>

        <div class="grid gap-6">
            <div class="flex gap-6 bg-white/5 p-6 rounded-2xl border border-white/10 text-white">
                <div class="text-4xl font-bold text-blue-400">01</div>
                <div>
                    <h3 class="text-xl font-bold mb-2">Book Your Shipment</h3>
                    <p class="opacity-70">Log in and use our simple interface to enter sender and receiver details.</p>
                </div>
            </div>
            <div class="flex gap-6 bg-white/5 p-6 rounded-2xl border border-white/10 text-white">
                <div class="text-4xl font-bold text-blue-400">02</div>
                <div>
                    <h3 class="text-xl font-bold mb-2">Review & Confirm</h3>
                    <p class="opacity-70">Check all shipment details before generating your invoice.</p>
                </div>
            </div>
            <div class="flex gap-6 bg-white/5 p-6 rounded-2xl border border-white/10 text-white">
                <div class="text-4xl font-bold text-blue-400">03</div>
                <div>
                    <h3 class="text-xl font-bold mb-2">Secure Payment</h3>
                    <p class="opacity-70">Complete your transaction through our integrated payment system.</p>
                </div>
            </div>
            <div class="flex gap-6 bg-white/5 p-6 rounded-2xl border border-white/10 text-white">
                <div class="text-4xl font-bold text-blue-400">04</div>
                <div>
                    <h3 class="text-xl font-bold mb-2">Admin Verification</h3>
                    <p class="opacity-70">Our team approves your shipment to begin the logistics process.</p>
                </div>
            </div>
            <div class="flex gap-6 bg-white/5 p-6 rounded-2xl border border-white/10 text-white">
                <div class="text-4xl font-bold text-blue-400">05</div>
                <div>
                    <h3 class="text-xl font-bold mb-2">Doorstep Delivery</h3>
                    <p class="opacity-70">Track your box in real-time until it is safely received.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleHelpMenu(event) {
            event.stopPropagation();
            document.getElementById('helpDropdown').classList.toggle('menu-active');
            document.getElementById('helpArrow').classList.toggle('rotate-180');
        }
        window.onclick = function() {
            document.getElementById('helpDropdown').classList.remove('menu-active');
            document.getElementById('helpArrow').classList.remove('rotate-180');
        }
    </script>
</body>
</html>