<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAuth(['distributor']);

$user_id = $_SESSION['user_id'];

// Get pending orders assigned to this distributor (either from wholesaler or direct retailer)
$stmt = $pdo->prepare("
    SELECT o.*, 
           r.name as retailer_name,
           w.name as wholesaler_name
    FROM orders o
    JOIN users r ON o.retailer_id = r.id
    LEFT JOIN users w ON o.wholesaler_id = w.id
    WHERE o.distributor_id = ? AND (o.status = 'distributor_pending' OR o.status = 'distributor_confirmed')
    ORDER BY o.status ASC, o.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">Distributor Portal</h2>
            <p class="text-sm text-gray-500 mt-1">Review and confirm orders to trigger Nestlé warehouse reservation</p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
             <span class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300">
                <?php echo count($orders); ?> Orders in Pipeline
            </span>
        </div>
    </div>

    <div class="bg-white shadow rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Incoming Supply Chain Orders</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Order Details</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Channel</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Retailer</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Total Value</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400 italic">No orders available for review.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($orders as $o): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">#<?php echo h($o['order_number']); ?></div>
                                <div class="text-xs text-gray-400">Placed: <?php echo date('M d, H:i', strtotime($o['order_date'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-700">
                                    <?php echo $o['wholesaler_name'] ? '<span class="text-xs font-bold text-nestle-brown uppercase bg-nestle-brown/5 px-2 py-0.5 rounded mr-1">WS</span> ' . h($o['wholesaler_name']) : '<span class="text-xs font-bold text-nestle-blue uppercase bg-nestle-blue/5 px-2 py-0.5 rounded mr-1">Direct</span>'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo h($o['retailer_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900"><?php echo formatCurrency($o['total_amount']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?php echo getStatusBadgeClass($o['status']); ?>">
                                    <?php echo str_replace('_', ' ', $o['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <?php if ($o['status'] === 'distributor_pending'): ?>
                                    <form action="/api/distributor/confirm-order.php" method="POST" class="inline">
                                        <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                                        <button type="submit" class="bg-nestle-blue text-white font-bold px-4 py-2 rounded-lg shadow hover:bg-nestle-blue/90 transition-all">Confirm Order</button>
                                    </form>
                                <?php else: ?>
                                    <button class="text-gray-400 font-bold px-4 py-2 rounded-lg border border-gray-100 flex items-center ml-auto" disabled>
                                        Confirmed <span>✅</span>
                                    </button>
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
