<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAuth(['retailer']);

$user_id = $_SESSION['user_id'];

// Fetch all orders for this retailer
$stmt = $pdo->prepare("
    SELECT o.*, u.name as distributor_name 
    FROM orders o
    JOIN users u ON o.distributor_id = u.id
    WHERE o.retailer_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="/retailer/dashboard.php" class="text-sm font-semibold text-nestle-blue mb-2 inline-block">← Back to Dashboard</a>
        <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Order History</h2>
        <p class="mt-1 text-sm text-gray-500">Track and manage all your past and active orders</p>
    </div>

    <div class="bg-white shadow rounded-2xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Order ID</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Distributor</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Total Value</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Scheduled Dispatch</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400 italic">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($orders as $o): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                #<?php echo h($o['order_number']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M d, Y', strtotime($o['order_date'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <?php echo h($o['distributor_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-nestle-brown">
                                <?php echo formatCurrency($o['total_amount']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?php echo getStatusBadgeClass($o['status']); ?>">
                                    <?php echo str_replace('_', ' ', $o['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                <?php echo $o['scheduled_dispatch_date'] ? date('M d, Y', strtotime($o['scheduled_dispatch_date'])) : 'TBD'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
