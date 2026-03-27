<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAuth(['distributor']);

$user_id = $_SESSION['user_id'];

// 1. Get incoming orders (Pending distribution)
$orders_stmt = $pdo->prepare("
    SELECT o.*, 
           r.name as retailer_name,
           w.name as wholesaler_name
    FROM orders o
    JOIN users r ON o.retailer_id = r.id
    LEFT JOIN users w ON o.wholesaler_id = w.id
    WHERE o.distributor_id = ? AND o.status IN ('distributor_pending', 'distributor_confirmed')
    ORDER BY o.status ASC, o.created_at DESC
");
$orders_stmt->execute([$user_id]);
$orders = $orders_stmt->fetchAll();

// 2. Get Wholesaler Network
$wholesalers_stmt = $pdo->prepare("
    SELECT w.*, 
           (SELECT COUNT(*) FROM users r WHERE r.wholesaler_id = w.id AND r.role = 'retailer') as retailer_count
    FROM users w
    WHERE w.distributor_id = ? AND w.role = 'wholesaler'
    ORDER BY w.name ASC
");
$wholesalers_stmt->execute([$user_id]);
$wholesalers = $wholesalers_stmt->fetchAll();

// 3. Get Direct Retailers
$direct_retailers_stmt = $pdo->prepare("
    SELECT * FROM users 
    WHERE distributor_id = ? AND role = 'retailer' AND order_direct = 1
    ORDER BY name ASC
");
$direct_retailers_stmt->execute([$user_id]);
$direct_retailers = $direct_retailers_stmt->fetchAll();

// 4. Summary Metrics
$metrics = [
    'pending_orders' => count($orders),
    'active_wholesalers' => count($wholesalers),
    'direct_retailers' => count($direct_retailers),
    'total_value' => array_sum(array_column($orders, 'total_amount'))
];

include '../includes/header.php';
?>

<div class="min-h-screen bg-[#F8F9FA] pb-20">
    <!-- Premium Header -->
    <div class="bg-white border-b border-gray-100 pt-12 pb-24 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-4xl font-black text-gray-900 tracking-tight">Distributor Portal</h1>
                    <p class="text-lg text-gray-500 mt-2 font-medium">Supply chain management & multi-tier network monitoring.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="px-4 py-2 bg-nestle-blue/5 rounded-2xl border border-nestle-blue/10">
                        <p class="text-[10px] font-black text-nestle-blue uppercase tracking-widest">System Status</p>
                        <p class="text-sm font-bold text-gray-900 flex items-center gap-2">
                           <span class="w-2 h-2 rounded-full bg-nestle-success"></span> Operational
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-3xl shadow-xl border border-gray-50">
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Pending Review</p>
                <p class="text-3xl font-black text-nestle-blue mt-2"><?php echo $metrics['pending_orders']; ?></p>
                <div class="mt-4 h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-nestle-blue w-2/3"></div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-xl border border-gray-50">
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Network Reach</p>
                <p class="text-3xl font-black text-nestle-brown mt-2"><?php echo $metrics['active_wholesalers'] + $metrics['direct_retailers']; ?></p>
                <p class="text-xs text-gray-400 font-bold mt-2 uppercase tracking-tighter">Wholesalers & Direct Retailers</p>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-xl border border-gray-50">
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Pipeline Value</p>
                <p class="text-3xl font-black text-gray-900 mt-2">Rs <?php echo number_format($metrics['total_value'] / 1000, 1); ?>k</p>
                <p class="text-xs text-nestle-success font-bold mt-2 uppercase tracking-tighter">Live calculation</p>
            </div>
            <div class="bg-nestle-brown p-6 rounded-3xl shadow-xl shadow-nestle-brown/20 text-white">
                <p class="text-[10px] font-black text-white/50 uppercase tracking-widest">Direct Control</p>
                <p class="text-3xl font-black mt-2"><?php echo $metrics['direct_retailers']; ?></p>
                <p class="text-xs font-bold mt-2 opacity-70 uppercase tracking-tighter">Retailers ordering direct</p>
            </div>
        </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12">
        <div x-data="{ tab: 'orders' }">
            <!-- Tab Navigation -->
            <div class="flex items-center gap-8 mb-8 border-b border-gray-200">
                <button @click="tab = 'orders'" :class="tab === 'orders' ? 'border-nestle-blue text-nestle-blue' : 'border-transparent text-gray-400 hover:text-gray-600'" class="pb-4 border-b-2 font-black text-sm uppercase tracking-widest transition-all">
                    Incoming Orders
                </button>
                <button @click="tab = 'network'" :class="tab === 'network' ? 'border-nestle-blue text-nestle-blue' : 'border-transparent text-gray-400 hover:text-gray-600'" class="pb-4 border-b-2 font-black text-sm uppercase tracking-widest transition-all">
                    Partner Network
                </button>
            </div>

            <!-- Orders Tab -->
            <div x-show="tab === 'orders'" class="space-y-6">
                <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto no-scrollbar">
                        <table class="min-w-full divide-y divide-gray-100 text-left">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Timestamp</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Chain / Origin</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Retailer</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Value</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right whitespace-nowrap">Operational Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="5" class="px-8 py-16 text-center">
                                            <div class="opacity-20 text-5xl mb-4">📦</div>
                                            <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">No pending confirmations</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php foreach ($orders as $o): ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-8 py-6 whitespace-nowrap">
                                            <p class="text-sm font-black text-gray-900">#<?php echo h($o['order_number']); ?></p>
                                            <p class="text-xs text-gray-400 mt-1"><?php echo date('d M, H:i', strtotime($o['order_date'])); ?></p>
                                        </td>
                                        <td class="px-8 py-6 whitespace-nowrap">
                                            <?php if ($o['wholesaler_name']): ?>
                                                <div class="flex items-center gap-2">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-nestle-brown"></span>
                                                    <div>
                                                        <p class="text-xs font-black text-nestle-brown uppercase tracking-widest">Wholesaler Chain</p>
                                                        <p class="text-sm font-bold text-gray-900"><?php echo h($o['wholesaler_name']); ?></p>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="flex items-center gap-2">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-nestle-blue"></span>
                                                    <div>
                                                        <p class="text-xs font-black text-nestle-blue uppercase tracking-widest">Direct Retail</p>
                                                        <p class="text-sm font-bold text-gray-900">Direct Delivery</p>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-8 py-6 whitespace-nowrap">
                                            <p class="text-sm font-bold text-gray-700"><?php echo h($o['retailer_name']); ?></p>
                                        </td>
                                        <td class="px-8 py-6 whitespace-nowrap">
                                            <p class="text-lg font-black text-gray-900">Rs <?php echo number_format($o['total_amount'], 2); ?></p>
                                        </td>
                                        <td class="px-8 py-6 text-right whitespace-nowrap">
                                            <?php if ($o['status'] === 'distributor_pending'): ?>
                                                <form action="<?php echo BASE_URL; ?>api/distributor/confirm-order.php" method="POST">
                                                    <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                                                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-nestle-blue text-white rounded-xl text-sm font-black hover:shadow-lg hover:shadow-nestle-blue/20 transition-all">
                                                        Confirm for Warehouse
                                                     </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-2 px-6 py-3 bg-nestle-success/10 text-nestle-success border border-nestle-success/20 rounded-xl text-xs font-black uppercase tracking-widest">
                                                    Warehouse Reserved ✅
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Partner Network Tab -->
            <div x-show="tab === 'network'" class="grid lg:grid-cols-2 gap-10">
                <!-- Wholesalers Section -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-black text-nestle-brown uppercase tracking-tight">Wholesaler Network</h3>
                        <span class="px-3 py-1 bg-nestle-brown/10 text-nestle-brown rounded-lg text-xs font-black"><?php echo count($wholesalers); ?> Active</span>
                    </div>
                    
                    <div class="grid gap-4">
                        <?php foreach ($wholesalers as $w): ?>
                            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-nestle-brown/5 flex items-center justify-center text-2xl">🏬</div>
                                        <div>
                                            <p class="text-lg font-black text-gray-900 leading-tight"><?php echo h($w['name']); ?></p>
                                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1"><?php echo h($w['territory']); ?></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-2xl font-black text-nestle-blue leading-tight"><?php echo $w['retailer_count']; ?></p>
                                        <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Retailers in Network</p>
                                    </div>
                                </div>
                                <!-- Sub-Retailers Quick View -->
                                <div class="mt-6 pt-6 border-t border-gray-50">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Sub-Retailer Network Details</p>
                                    <?php 
                                    // Get sub-retailers for this wholesaler
                                    $sub_stmt = $pdo->prepare("SELECT name, region FROM users WHERE wholesaler_id = ? AND role = 'retailer' LIMIT 5");
                                    $sub_stmt->execute([$w['id']]);
                                    $subs = $sub_stmt->fetchAll();
                                    
                                    if (empty($subs)): ?>
                                        <p class="text-xs text-gray-400 italic">No retailers onboarded yet.</p>
                                    <?php else: ?>
                                        <div class="flex flex-wrap gap-2">
                                            <?php foreach ($subs as $s): ?>
                                                <span class="px-3 py-1 bg-gray-50 rounded-lg text-[10px] font-bold text-gray-600 border border-gray-100"><?php echo h($s['name']); ?></span>
                                            <?php endforeach; ?>
                                            <?php if ($w['retailer_count'] > 5): ?>
                                                <span class="px-3 py-1 bg-nestle-blue/5 text-nestle-blue rounded-lg text-[10px] font-bold border border-nestle-blue/10">+<?php echo $w['retailer_count'] - 5; ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Direct Retailers Section -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-black text-nestle-blue uppercase tracking-tight">Direct Retailers</h3>
                        <span class="px-3 py-1 bg-nestle-blue/10 text-nestle-blue rounded-lg text-xs font-black"><?php echo count($direct_retailers); ?> Active</span>
                    </div>

                    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto no-scrollbar">
                            <table class="min-w-full divide-y divide-gray-100 text-left">
                                <thead class="bg-gray-50/50">
                                    <tr>
                                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Entity Name</th>
                                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">Region</th>
                                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right whitespace-nowrap">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <?php foreach ($direct_retailers as $r): ?>
                                        <tr class="hover:bg-gray-50/30 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <p class="text-sm font-bold text-gray-900"><?php echo h($r['name']); ?></p>
                                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter"><?php echo h($r['phone']); ?></p>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-xs font-bold text-gray-600"><?php echo h($r['region']); ?></span>
                                            </td>
                                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                                <span class="px-2 py-1 bg-nestle-success/10 text-nestle-success rounded text-[10px] font-black uppercase tracking-widest border border-nestle-success/20">Active</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts for the tab functionality (Alpine.js is used in headers usually, but we'll add a simple script if needed) -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<?php include '../includes/footer.php'; ?>
