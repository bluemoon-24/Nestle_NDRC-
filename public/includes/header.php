<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NDRC Platform - Nestlé</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#63513D">
    <script src="https://cdn.tailwindcss.com"></script>
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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-nestle-bg min-h-screen">
<?php if (isset($_SESSION['user_id'])): ?>
    <!-- App Navigation -->
    <nav class="bg-nestle-brown text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex-shrink-0 flex items-center">
                        <img class="h-10 w-auto" src="/assets/images/logo.png" alt="Nestlé NDRC">
                        <span class="ml-2 text-2xl font-bold tracking-tighter hidden sm:block">NESTLÉ <span class="text-nestle-blue font-light">NDRC</span></span>
                    </a>
                    
                    <!-- Role-Based Nav Links -->
                    <div class="hidden md:ml-10 md:flex md:space-x-8">
                        <?php if ($_SESSION['user_role'] === 'nestle'): ?>
                            <a href="/nestle/dashboard.php" class="text-white hover:text-nestle-blue px-3 py-2 text-sm font-medium">Command Center</a>
                            <a href="/nestle/warehouse.php" class="text-white hover:text-nestle-blue px-3 py-2 text-sm font-medium">Warehouse</a>
                            <a href="/nestle/analytics.php" class="text-white hover:text-nestle-blue px-3 py-2 text-sm font-medium">Analytics</a>
                        <?php elseif ($_SESSION['user_role'] === 'retailer'): ?>
                            <a href="/retailer/dashboard.php" class="text-white hover:text-nestle-blue px-3 py-2 text-sm font-medium">Dashboard</a>
                            <a href="/retailer/place-order.php" class="text-white hover:text-nestle-blue px-3 py-2 text-sm font-medium">New Order</a>
                            <a href="/retailer/orders.php" class="text-white hover:text-nestle-blue px-3 py-2 text-sm font-medium">History</a>
                        <?php elseif ($_SESSION['user_role'] === 'wholesaler'): ?>
                            <a href="/wholesaler/dashboard.php" class="text-white hover:text-nestle-blue px-3 py-2 text-sm font-medium">Incoming Orders</a>
                            <a href="/wholesaler/retailers.php" class="text-white hover:text-nestle-blue px-3 py-2 text-sm font-medium">Retailer Network</a>
                        <?php elseif ($_SESSION['user_role'] === 'distributor'): ?>
                            <a href="/distributor/dashboard.php" class="text-white hover:text-nestle-blue px-3 py-2 text-sm font-medium">Pending Confirmations</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Notification Bell -->
                    <div class="relative">
                        <button onclick="toggleNotifications()" class="p-2 rounded-full hover:bg-white/10 relative">
                            <span>🔔</span>
                            <span id="notifBadge" class="hidden absolute top-0 right-0 bg-nestle-danger text-white text-[10px] rounded-full w-4 h-4 flex items-center justify-center">0</span>
                        </button>
                        <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl z-50 py-2 border border-gray-100">
                            <div class="px-4 py-2 border-b border-gray-50 flex justify-between items-center">
                                <h3 class="font-bold text-gray-800">Notifications</h3>
                                <a href="/notifications.php?mark_all_read=1" class="text-xs text-nestle-blue hover:underline">Mark all as read</a>
                            </div>
                            <div id="notifContent" class="max-h-96 overflow-y-auto">
                                <p class="text-center py-4 text-gray-500 text-sm">No new notifications</p>
                            </div>
                            <div class="px-4 py-2 border-t border-gray-50 text-center">
                                <a href="/notifications.php" class="text-xs font-bold text-nestle-blue hover:underline">View All Notifications</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="flex items-center space-x-2">
                        <span class="hidden md:block text-sm font-medium"><?php echo h($_SESSION['user_name']); ?></span>
                        <div class="h-8 w-8 bg-nestle-blue rounded-full flex items-center justify-center font-bold">
                            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                        </div>
                        <a href="/api/auth/logout.php" class="text-sm bg-white/10 hover:bg-white/20 px-3 py-1 rounded-lg">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
<?php endif; ?>
<main>
