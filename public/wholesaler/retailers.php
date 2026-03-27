<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAuth(['wholesaler']);

$user_id = $_SESSION['user_id'];

// 1. Fetch pending requests
$pending_stmt = $pdo->prepare("
    SELECT nr.*, r.name, r.email, r.phone, r.region
    FROM network_requests nr
    JOIN users r ON nr.retailer_id = r.id
    WHERE nr.wholesaler_id = ? AND nr.status = 'pending'
    ORDER BY nr.created_at DESC
");
$pending_stmt->execute([$user_id]);
$pending_requests = $pending_stmt->fetchAll();

// 2. Fetch associated retailers
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

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Feedback Messages -->
    <?php if (isset($_GET['success'])): ?>
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-2xl flex items-center justify-between">
            <span class="font-bold"><?php echo h($_GET['success']); ?></span>
            <span class="text-xl">✅</span>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-2xl flex items-center justify-between">
            <span class="font-bold"><?php echo h($_GET['error']); ?></span>
            <span class="text-xl">🚨</span>
        </div>
    <?php endif; ?>

    <div class="mb-12 flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm text-gray-400">
                    <li><a href="<?php echo BASE_URL; ?>wholesaler/dashboard.php" class="hover:text-nestle-blue transition-colors">Dashboard</a></li>
                    <li><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 18l6-6-6-6"></path></svg></li>
                    <li class="font-black text-gray-900">Retailer Network</li>
                </ol>
            </nav>
            <h2 class="text-4xl font-black text-gray-900 tracking-tight">Network Management</h2>
            <p class="mt-2 text-gray-500 font-medium">Monitor activity and manage your associated retailers</p>
        </div>
        <div class="max-w-xs w-full">
            <div class="relative">
                <input type="text" id="retailerSearch" placeholder="Filter retailers..." 
                       class="w-full rounded-[1.5rem] border-gray-100 bg-white shadow-sm focus:ring-nestle-blue focus:border-nestle-blue py-4 px-6 text-sm font-bold" 
                       onkeyup="filterRetailers()">
                <span class="absolute right-6 top-1/2 -translate-y-1/2 opacity-20">🔍</span>
            </div>
        </div>
    </div>

    <!-- Pending Requests Section -->
    <?php if (!empty($pending_requests)): ?>
    <div class="mb-12">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-black text-nestle-blue uppercase tracking-widest">Pending Join Requests</h3>
            <span class="px-4 py-1.5 bg-nestle-blue text-white rounded-full text-xs font-black"><?php echo count($pending_requests); ?> Action Required</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($pending_requests as $req): ?>
                <div class="bg-white p-8 rounded-[2.5rem] border-t-8 border-t-nestle-blue shadow-xl shadow-nestle-blue/5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h4 class="text-xl font-black text-gray-900"><?php echo h($req['name']); ?></h4>
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1"><?php echo h($req['region']); ?></p>
                            </div>
                            <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center text-2xl">🗳️</div>
                        </div>
                        <div class="space-y-3 py-4 border-y border-gray-50">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-black text-gray-400 w-12 uppercase">Email:</span>
                                <span class="text-sm font-bold text-gray-700"><?php echo h($req['email']); ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-black text-gray-400 w-12 uppercase">Ph:</span>
                                <span class="text-sm font-bold text-gray-700"><?php echo h($req['phone']); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 flex gap-3">
                        <form action="<?php echo BASE_URL; ?>api/wholesaler/handle-network-request.php" method="POST" class="flex-1">
                            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="w-full py-4 bg-nestle-blue text-white rounded-2xl text-xs font-black hover:scale-95 transition-all">APPROVE</button>
                        </form>
                        <form action="<?php echo BASE_URL; ?>api/wholesaler/handle-network-request.php" method="POST">
                            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="p-4 bg-gray-50 text-gray-400 hover:text-red-500 rounded-2xl text-xs font-black hover:bg-red-50 transition-all">REJECT</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="h-px bg-gray-100 mb-12"></div>
    <?php endif; ?>

    <!-- Current Network Table -->
    <div class="bg-white shadow-2xl rounded-[3rem] border border-gray-100 overflow-hidden">
        <div class="px-8 py-8 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="text-xl font-black text-gray-900 uppercase tracking-widest">Associated Retailers</h3>
            <span class="inline-flex items-center rounded-xl bg-gray-900 px-4 py-2 text-[10px] font-black text-white uppercase tracking-widest">
                <?php echo count($retailers); ?> Retailers
            </span>
        </div>
        <div class="overflow-x-auto no-scrollbar">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-white">
                    <tr>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Retailer Identity</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Coordinates</th>
                        <th class="px-8 py-5 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Order Vol.</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Last Sync</th>
                        <th class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Performance</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php if (empty($retailers)): ?>
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center">
                                <div class="opacity-10 text-6xl mb-4 text-nestle-blue">📦</div>
                                <p class="text-sm font-bold text-gray-400 uppercase tracking-[0.2em]">No retailers found in network</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($retailers as $r): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-8 py-6 whitespace-nowrap">
                                <div class="text-sm font-black text-gray-900"><?php echo h($r['name']); ?></div>
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter mt-1">ID: #RT-<?php echo str_pad($r['id'], 3, '0', STR_PAD_LEFT); ?></div>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap">
                                <div class="text-xs font-bold text-gray-700"><?php echo h($r['region']); ?></div>
                                <div class="text-[10px] text-gray-400 font-medium mt-1 uppercase tracking-tight"><?php echo h($r['phone']); ?></div>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap text-center">
                                <span class="inline-flex items-center rounded-xl bg-gray-50 px-3 py-1 text-sm font-black text-gray-900 border border-gray-100">
                                    <?php echo $r['total_orders']; ?>
                                </span>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap text-sm font-bold text-gray-500">
                                <?php echo $r['last_order_date'] ? date('M d, Y', strtotime($r['last_order_date'])) : '<span class="opacity-20">—</span>'; ?>
                            </td>
                            <td class="px-8 py-6 whitespace-nowrap text-right">
                                <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest <?php echo $r['status'] === 'active' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'; ?>">
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
        let nameField = tr[i].getElementsByTagName('td')[0];
        if (nameField) {
            let txtValue = nameField.textContent || nameField.innerText;
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
