<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAuth(['retailer']);

$user_id = $_SESSION['user_id'];

// Get order history
$stmt = $pdo->prepare("
    SELECT o.*, u.name as distributor_name 
    FROM orders o
    JOIN users u ON o.distributor_id = u.id
    WHERE o.retailer_id = ?
    ORDER BY o.created_at DESC
    LIMIT 10
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">Retailer Dashboard</h2>
            <p class="text-sm text-gray-500 mt-1">Status of your recent orders and restocking</p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <a href="/retailer/place-order.php" class="inline-flex items-center rounded-xl bg-nestle-brown px-6 py-3 text-sm font-bold text-white shadow-lg shadow-nestle-brown/20 hover:bg-nestle-brown/90 transition-all">
                New Order 🛒
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Activity -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">Recent Orders</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    <?php if (empty($orders)): ?>
                        <div class="p-10 text-center">
                            <div class="text-4xl mb-4">🛒</div>
                            <p class="text-gray-500">You haven't placed any orders yet.</p>
                            <a href="/retailer/place-order.php" class="text-nestle-blue font-bold mt-2 inline-block">Place your first order</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($orders as $o): ?>
                            <div class="p-6 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-gray-400 tracking-widest uppercase">#<?php echo h($o['order_number']); ?></span>
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?php echo getStatusBadgeClass($o['status']); ?>">
                                        <?php echo str_replace('_', ' ', $o['status']); ?>
                                    </span>
                                </div>
                                <div class="flex justify-between items-end">
                                    <div>
                                        <div class="text-sm text-gray-500">Distributor: <?php echo h($o['distributor_name']); ?></div>
                                        <div class="text-xs text-gray-400 mt-1">Placed on <?php echo date('M d, Y', strtotime($o['order_date'])); ?></div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-gray-900"><?php echo formatCurrency($o['total_amount']); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="p-4 bg-gray-50 text-center">
                            <a href="/retailer/orders.php" class="text-sm font-bold text-nestle-blue hover:underline">View All Orders</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar / Promotions -->
        <div class="space-y-8">
            <div class="bg-nestle-blue rounded-2xl p-6 text-white shadow-lg shadow-nestle-blue/20">
                <h3 class="text-lg font-bold mb-2">Promotion 🎁</h3>
                <p class="text-sm opacity-90 mb-4">Get 10% off on all MAGGI products this month. Stock up for the festive season!</p>
                <a href="/retailer/place-order.php?category=Noodles" class="inline-block bg-white text-nestle-blue px-4 py-2 rounded-lg text-sm font-bold">Shop Now</a>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                <h3 class="font-bold text-gray-900 mb-4">Help Center</h3>
                <ul class="text-sm space-y-3">
                    <li><a href="#" class="text-gray-600 hover:text-nestle-blue flex justify-between">Order Status Guide <span>→</span></a></li>
                    <li><a href="#" class="text-gray-600 hover:text-nestle-blue flex justify-between">Payments & Invoices <span>→</span></a></li>
                    <li><a href="#" class="text-gray-600 hover:text-nestle-blue flex justify-between">Contact Distributor <span>→</span></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
