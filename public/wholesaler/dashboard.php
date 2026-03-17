<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAuth(['wholesaler']);

$user_id = $_SESSION['user_id'];

// Stats
$stats = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM orders WHERE wholesaler_id = ? AND status = 'placed') as pending_count,
        (SELECT COUNT(*) FROM users WHERE wholesaler_id = ?) as retailer_count,
        (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE wholesaler_id = ? AND status = 'distributor_confirmed') as confirmed_total
");
$stats->execute([$user_id, $user_id, $user_id]);
$statData = $stats->fetch();

// Recent orders
$stmt = $pdo->prepare("
    SELECT o.*, u.name as retailer_name 
    FROM orders o
    JOIN users u ON o.retailer_id = u.id
    WHERE o.wholesaler_id = ?
    ORDER BY o.created_at DESC
    LIMIT 10
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Wholesaler Hub</h2>
        <p class="mt-1 text-sm text-gray-500">Manage incoming orders from your retailers</p>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-xl border border-gray-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-orange-100 rounded-lg p-3">
                        <span class="text-2xl">📥</span>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Orders</dt>
                            <dd class="text-2xl font-semibold text-gray-900"><?php echo $statData['pending_count']; ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3 text-xs">
                <a href="/wholesaler/orders.php?status=placed" class="font-bold text-nestle-blue hover:underline">Review All Orders →</a>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-xl border border-gray-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-nestle-blue/10 rounded-lg p-3">
                        <span class="text-2xl">👥</span>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Retailer Network</dt>
                            <dd class="text-2xl font-semibold text-gray-900"><?php echo $statData['retailer_count']; ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3 text-xs">
                <a href="/wholesaler/retailers.php" class="font-bold text-nestle-blue hover:underline">Manage Network →</a>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-xl border border-gray-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                        <span class="text-2xl">📈</span>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Aggregated Confirmed</dt>
                            <dd class="text-2xl font-semibold text-gray-900"><?php echo formatCurrency($statData['confirmed_total']); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3 text-xs">
                <span class="text-gray-400">Lifetime value</span>
            </div>
        </div>
    </div>

    <!-- Incoming Orders -->
    <div class="bg-white shadow rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900">Incoming Retailer Orders</h3>
            <button class="text-sm font-bold text-nestle-blue hover:underline">Mark all as processed</button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Retailer</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php foreach ($orders as $o): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?php echo h($o['order_number']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo h($o['retailer_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M d', strtotime($o['order_date'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900"><?php echo formatCurrency($o['total_amount']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase <?php echo getStatusBadgeClass($o['status']); ?>">
                                    <?php echo str_replace('_', ' ', $o['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <?php if ($o['status'] === 'placed'): ?>
                                    <form action="/api/wholesaler/process-order.php" method="POST" class="inline">
                                        <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                                        <button type="submit" name="action" value="accept" class="text-green-600 font-bold hover:bg-green-50 px-2 py-1 rounded">Accept</button>
                                        <button type="submit" name="action" value="reject" class="text-red-600 font-bold hover:bg-red-50 px-2 py-1 rounded ml-2">Reject</button>
                                    </form>
                                <?php elseif ($o['status'] === 'wholesaler_accepted'): ?>
                                    <form action="/api/wholesaler/submit-to-distributor.php" method="POST" class="inline">
                                        <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                                        <button type="submit" class="text-nestle-blue font-bold border border-nestle-blue/20 bg-nestle-blue/5 px-3 py-1.5 rounded-lg hover:bg-nestle-blue/10">Submit to Distributor</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-gray-400 italic">No action</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
