<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAuth(['nestle']);

// Top 10 Ordered Products
$topProducts = $pdo->query("
    SELECT p.name, SUM(oi.quantity) as total_qty, SUM(oi.subtotal) as total_revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status = 'distributor_confirmed'
    GROUP BY p.id
    ORDER BY total_qty DESC
    LIMIT 10
")->fetchAll();

// Order Trends by Region
$regionTrends = $pdo->query("
    SELECT region, COUNT(*) as order_count, SUM(total_amount) as total_value
    FROM orders o
    JOIN users u ON o.retailer_id = u.id
    WHERE o.status = 'distributor_confirmed'
    GROUP BY region
    ORDER BY total_value DESC
")->fetchAll();

include '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">R&D Insights & Analytics</h2>
        <p class="mt-1 text-sm text-gray-500">Simple data visualization for supply chain optimization</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Top Products -->
        <div class="bg-white shadow rounded-2xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Top 10 Products by Volume</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <?php if (empty($topProducts)): ?>
                        <p class="text-center text-gray-400 py-10 italic">No confirmed order data available yet.</p>
                    <?php else: ?>
                        <?php foreach ($topProducts as $index => $p): ?>
                            <div class="relative">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-bold text-gray-700"><?php echo h($p['name']); ?></span>
                                    <span class="text-xs font-medium text-gray-500"><?php echo number_format($p['total_qty']); ?> units</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <?php 
                                    $max = $topProducts[0]['total_qty'];
                                    $width = ($p['total_qty'] / $max) * 100;
                                    ?>
                                    <div class="bg-nestle-blue h-2 rounded-full" style="width: <?php echo $width; ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Region Trends -->
        <div class="bg-white shadow rounded-2xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Order Trends by Region</h3>
            </div>
            <div class="p-6">
                <div class="space-y-6">
                    <?php if (empty($regionTrends)): ?>
                        <p class="text-center text-gray-400 py-10 italic">No regional data available yet.</p>
                    <?php else: ?>
                        <?php foreach ($regionTrends as $r): ?>
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm font-bold text-gray-900"><?php echo h($r['region'] ?: 'Other'); ?></span>
                                        <span class="text-xs font-bold text-nestle-brown"><?php echo formatCurrency($r['total_value']); ?></span>
                                    </div>
                                    <div class="text-xs text-gray-400"><?php echo $r['order_count']; ?> confirmed orders</div>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    <div class="h-10 w-10 rounded-full bg-nestle-bg flex items-center justify-center text-xl">
                                        📍
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
