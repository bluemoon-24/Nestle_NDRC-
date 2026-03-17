<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAuth(['wholesaler']);

$user_id = $_SESSION['user_id'];

// Fetch retailers associated with this wholesaler
$stmt = $pdo->prepare("
    SELECT u.*, 
           (SELECT MAX(created_at) FROM orders WHERE retailer_id = u.id) as last_order_date,
           (SELECT COUNT(*) FROM orders WHERE retailer_id = u.id) as total_orders
    FROM users u
    WHERE u.wholesaler_id = ? AND u.role = 'retailer'
    ORDER BY last_order_date DESC
");
$stmt->execute([$user_id]);
$retailers = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <a href="/wholesaler/dashboard.php" class="text-sm font-semibold text-nestle-blue mb-2 inline-block">← Back to Dashboard</a>
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Retailer Network</h2>
            <p class="mt-1 text-sm text-gray-500">Monitor activity and manage your associated retailers</p>
        </div>
        <div class="max-w-xs w-full">
            <input type="text" id="retailerSearch" placeholder="Search retailers..." class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-nestle-blue focus:border-nestle-blue sm:text-sm py-2.5 px-4" onkeyup="filterRetailers()">
        </div>
    </div>

    <div class="bg-white shadow rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900">Associated Retailers</h3>
            <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800">
                <?php echo count($retailers); ?> Retailers
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Retailer Name</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Contact Info</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Region</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Total Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Last Activity</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php if (empty($retailers)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400 italic">No retailers found in your network.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($retailers as $r): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900"><?php echo h($r['name']); ?></div>
                                <div class="text-xs text-gray-500">Retailer ID: #RT-<?php echo str_pad($r['id'], 4, '0', STR_PAD_LEFT); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo h($r['email']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo h($r['phone']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo h($r['region']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-900">
                                <?php echo $r['total_orders']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $r['last_order_date'] ? date('M d, Y', strtotime($r['last_order_date'])) : 'Never'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium <?php echo $r['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo ucfirst($r['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function filterRetailers() {
    let input = document.getElementById('retailerSearch');
    let filter = input.value.toLowerCase();
    let table = document.querySelector('table');
    let tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        let name = tr[i].getElementsByTagName('td')[0];
        let id = tr[i].getElementsByTagName('td')[0];
        if (name || id) {
            let txtValue = name.textContent || name.innerText;
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}
</script>

<?php include '../includes/footer.php'; ?>
