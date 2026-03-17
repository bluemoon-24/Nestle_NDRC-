<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['user_role']) {
        case 'retailer': header('Location: /retailer/dashboard.php'); break;
        case 'wholesaler': header('Location: /wholesaler/dashboard.php'); break;
        case 'distributor': header('Location: /distributor/dashboard.php'); break;
        case 'nestle': header('Location: /nestle/dashboard.php'); break;
    }
    exit();
}

include 'includes/header.php';
?>

<div class="relative overflow-hidden bg-nestle-brown py-24 sm:py-32">
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(45rem_50rem_at_top,theme(colors.nestle-blue/20),theme(colors.nestle-brown))]"></div>
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h1 class="text-4xl font-bold tracking-tight text-white sm:text-6xl">NDRC Platform</h1>
            <p class="mt-6 text-lg leading-8 text-gray-300">Nestlé Direct Retail Channel. Real-time supply chain visibility across Sri Lanka's distribution network.</p>
            <div class="mt-10 flex items-center justify-center gap-x-6">
                <a href="/login.php" class="rounded-lg bg-white px-8 py-3.5 text-sm font-semibold text-nestle-brown shadow-sm hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white transition-all">Get Started</a>
                <a href="/register.php" class="text-sm font-semibold leading-6 text-white hover:text-nestle-blue transition-colors">Register Account <span aria-hidden="true">→</span></a>
            </div>
        </div>
    </div>
</div>

<div class="bg-white py-24 sm:py-32">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="mx-auto max-w-2xl lg:text-center">
            <h2 class="text-base font-semibold leading-7 text-nestle-blue">Transparency First</h2>
            <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Everything you need to visibility of distribution</p>
            <p class="mt-6 text-lg leading-8 text-gray-600">The NDRC platform bridges the gap between Nestlé Lanka and the multi-tier distribution network, providing real-time data insights without disrupting the physical flow.</p>
        </div>
        <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-4xl">
            <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-10 lg:max-w-none lg:grid-cols-2 lg:gap-y-16">
                <div class="relative pl-16">
                    <dt class="text-base font-semibold leading-7 text-gray-900">
                        <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-nestle-brown text-white text-xl">
                            📊
                        </div>
                        Real-Time Aggregation
                    </dt>
                    <dd class="mt-2 text-base leading-7 text-gray-600">Instantly view order totals across all distributors and wholesalers on a live dashboard.</dd>
                </div>
                <div class="relative pl-16">
                    <dt class="text-base font-semibold leading-7 text-gray-900">
                        <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-nestle-brown text-white text-xl">
                            📦
                        </div>
                        Smart Inventory
                    </dt>
                    <dd class="mt-2 text-base leading-7 text-gray-600">Automated reserved stock calculations based on confirmed orders to prevent over-promising.</dd>
                </div>
                <div class="relative pl-16">
                    <dt class="text-base font-semibold leading-7 text-gray-900">
                        <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-nestle-brown text-white text-xl">
                            📱
                        </div>
                        PWA Ready
                    </dt>
                    <dd class="mt-2 text-base leading-7 text-gray-600">Install on any mobile device for one-tap access and offline status monitoring.</dd>
                </div>
                <div class="relative pl-16">
                    <dt class="text-base font-semibold leading-7 text-gray-900">
                        <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-nestle-brown text-white text-xl">
                            🔔
                        </div>
                        Active Notifications
                    </dt>
                    <dd class="mt-2 text-base leading-7 text-gray-600">Stay updated with push-style notifications for order status changes and low stock alerts.</dd>
                </div>
            </dl>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
