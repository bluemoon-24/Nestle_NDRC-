<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>NDRC Platform - Nestlé</title>
    
    <!-- PWA & Mobile Optimization -->
    <link rel="manifest" href="<?php echo BASE_URL; ?>manifest.json">
    <meta name="theme-color" content="#002B5C">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="<?php echo BASE_URL; ?>assets/images/icon-192.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'nestle-brown': '#63513D',
                        'nestle-blue': '#0085C3',
                        'nestle-success': '#4CAF50',
                        'nestle-warning': '#FF9800',
                        'nestle-danger': '#F44336',
                        'nestle-bg': '#F5F3F0',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    
    <script>
        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?php echo BASE_URL; ?>sw.js')
                    .then(reg => console.log('NDRC Service Worker registered'))
                    .catch(err => console.log('Registration failed:', err));
            });
        }
    </script>
</head>
<body class="bg-nestle-bg min-h-screen" x-data="{ mobileMenu: false }">

<?php if (isset($_SESSION['user_id'])): ?>
    <!-- Premium App Navigation -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-gray-100 shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <div class="flex items-center">
                    <a href="<?php echo BASE_URL; ?>" class="flex-shrink-0 flex items-center">
                        <img class="h-10 w-auto" src="<?php echo BASE_URL; ?>assets/images/logo.svg" alt="Icon">
                        <span class="ml-3 text-xl font-black tracking-tighter text-gray-900">NESTLÉ <span class="text-nestle-blue font-black">NDRC</span></span>
                    </a>
                    
                    <!-- Desktop Nav -->
                    <div class="hidden md:ml-10 md:flex md:space-x-4">
                        <?php 
                        $roleLinks = [
                            'nestle' => [
                                ['label' => 'Command Center', 'url' => 'nestle/dashboard.php'],
                                ['label' => 'Warehouse', 'url' => 'nestle/warehouse.php'],
                                ['label' => 'Analytics', 'url' => 'nestle/analytics.php']
                            ],
                            'retailer' => [
                                ['label' => 'Dashboard', 'url' => 'retailer/dashboard.php'],
                                ['label' => 'New Order', 'url' => 'retailer/place-order.php'],
                                ['label' => 'History', 'url' => 'retailer/orders.php']
                            ],
                            'wholesaler' => [
                                ['label' => 'Incoming Orders', 'url' => 'wholesaler/dashboard.php'],
                                ['label' => 'Retailer Network', 'url' => 'wholesaler/retailers.php']
                            ],
                            'distributor' => [
                                ['label' => 'Confirmations', 'url' => 'distributor/dashboard.php']
                            ]
                        ];
                        
                        $links = $roleLinks[$_SESSION['user_role']] ?? [];
                        foreach ($links as $link): ?>
                            <a href="<?php echo BASE_URL . $link['url']; ?>" class="px-4 py-2 text-sm font-bold text-gray-600 hover:text-nestle-blue hover:bg-nestle-blue/5 rounded-xl transition-all">
                                <?php echo $link['label']; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <!-- Notifications (Hidden on small mobile) -->
                    <button class="p-2.5 rounded-2xl bg-gray-50 text-gray-400 hover:bg-nestle-blue/5 hover:text-nestle-blue transition-all hidden sm:block">
                        <span class="text-xl">🔔</span>
                    </button>

                    <!-- User Meta -->
                    <div class="flex items-center gap-3 pl-3 border-l border-gray-100">
                        <div class="text-right hidden sm:block">
                            <p class="text-xs font-black text-gray-900 leading-none"><?php echo h($_SESSION['user_name']); ?></p>
                            <p class="text-[10px] font-bold text-nestle-blue uppercase tracking-widest mt-1 opacity-70"><?php echo $_SESSION['user_role']; ?></p>
                        </div>
                        <div class="h-10 w-10 bg-nestle-blue rounded-2xl flex items-center justify-center text-white font-black shadow-lg shadow-nestle-blue/20">
                            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                        </div>
                    </div>

                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2.5 rounded-2xl bg-gray-100 text-gray-800 transition-all">
                        <svg x-show="!mobileMenu" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                        <svg x-show="mobileMenu" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>

                    <a href="<?php echo BASE_URL; ?>api/auth/logout.php" class="hidden md:flex bg-gray-900 text-white px-5 py-2.5 rounded-2xl text-xs font-black hover:bg-nestle-brown transition-all items-center gap-2">
                        Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Panel -->
        <div x-show="mobileMenu" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="md:hidden absolute top-20 left-0 w-full bg-white border-b border-gray-100 shadow-2xl z-40 p-6 space-y-4">
            <?php foreach ($links as $link): ?>
                <a href="<?php echo BASE_URL . $link['url']; ?>" class="block px-6 py-4 bg-gray-50 rounded-2xl text-sm font-black text-gray-700 hover:bg-nestle-blue/10 hover:text-nestle-blue transition-all">
                    <?php echo $link['label']; ?>
                </a>
            <?php endforeach; ?>
            <div class="pt-4 border-t border-gray-100">
                <a href="<?php echo BASE_URL; ?>api/auth/logout.php" class="flex items-center justify-center gap-2 w-full bg-red-50 text-red-600 px-6 py-4 rounded-2xl text-sm font-black">
                    Sign Out
                </a>
            </div>
        </div>
    </nav>
<?php endif; ?>

<main class="relative">
