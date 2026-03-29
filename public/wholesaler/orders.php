<?php
// public/wholesaler/orders.php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAuth(['wholesaler']);

$user_id = $_SESSION['user_id'];
$status_filter = $_GET['status'] ?? null;

// Base query
$query = "
    SELECT o.*, u.name as retailer_name 
    FROM orders o
    JOIN users u ON o.retailer_id = u.id
    WHERE o.wholesaler_id = ?
";

$params = [$user_id];

if ($status_filter) {
    $query .= " AND o.status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="min-h-screen bg-[#FDFCFB] py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
            <div>
                <nav class="flex mb-4" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-sm text-gray-400 font-bold uppercase tracking-widest">
                        <li><a href="<?php echo BASE_URL; ?>wholesaler/dashboard.php" class="hover:text-nestle-blue transition-colors">Dashboard</a></li>
                        <li><span class="px-2">/</span></li>
                        <li class="text-nestle-brown">Order History</li>
                    </ol>
                </nav>
                <h1 class="text-5xl font-black text-gray-900 tracking-tighter">Order Archive</h1>
                <p class="mt-3 text-lg text-gray-500 font-medium">Full visibility into all retailer fulfillment cycles.</p>
            </div>
            
            <!-- Filters -->
            <div class="flex flex-wrap gap-2">
                <a href="?" class="px-5 py-2.5 rounded-xl border <?php echo !$status_filter ? 'bg-nestle-blue text-white shadow-lg' : 'bg-white text-gray-600 hover:border-nestle-blue/30'; ?> text-xs font-black uppercase tracking-widest transition-all">All</a>
                <a href="?status=placed" class="px-5 py-2.5 rounded-xl border <?php echo $status_filter === 'placed' ? 'bg-orange-500 text-white shadow-lg' : 'bg-white text-gray-600 hover:border-orange-200'; ?> text-xs font-black uppercase tracking-widest transition-all">Pending</a>
                <a href="?status=wholesaler_accepted" class="px-5 py-2.5 rounded-xl border <?php echo $status_filter === 'wholesaler_accepted' ? 'bg-nestle-blue text-white shadow-lg' : 'bg-white text-gray-600 hover:border-nestle-blue/20'; ?> text-xs font-black uppercase tracking-widest transition-all">Accepted</a>
                <a href="?status=distributor_confirmed" class="px-5 py-2.5 rounded-xl border <?php echo $status_filter === 'distributor_confirmed' ? 'bg-green-600 text-white shadow-lg' : 'bg-white text-gray-600 hover:border-green-200'; ?> text-xs font-black uppercase tracking-widest transition-all">Fulfillment</a>
            </div>
        </div>

        <!-- Orders Grid -->
        <div class="grid gap-6">
            <?php if (empty($orders)): ?>
                <div class="bg-white rounded-[3rem] border border-gray-100 p-20 text-center shadow-sm">
                    <p class="text-5xl mb-4">📜</p>
                    <p class="text-xl font-bold text-gray-400 uppercase tracking-widest">No matching orders found</p>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $o): ?>
                    <div class="bg-white rounded-[2.5rem] border border-gray-100 p-8 shadow-sm hover:shadow-xl transition-all group relative overflow-hidden">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                            <div class="flex items-start gap-6">
                                <div class="w-16 h-16 rounded-2xl bg-gray-50 flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">📦</div>
                                <div>
                                    <div class="flex items-center gap-3">
                                        <h3 class="text-xl font-black text-gray-900">#<?php echo h($o['order_number']); ?></h3>
                                        <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase <?php echo getStatusBadgeClass($o['status']); ?> border border-current/10">
                                            <?php echo str_replace('_', ' ', $o['status']); ?>
                                        </span>
                                    </div>
                                    <p class="text-sm font-bold text-nestle-blue mt-1"><?php echo h($o['retailer_name']); ?></p>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-2">Ordered on <?php echo date('F d, Y @ H:i', strtotime($o['created_at'])); ?></p>
                                </div>
                            </div>

                            <div class="flex items-center gap-12 lg:border-l lg:pl-12 border-gray-100">
                                <div class="text-right">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Order Value</p>
                                    <p class="text-2xl font-black text-gray-900"><?php echo formatCurrency($o['total_amount']); ?></p>
                                </div>
                                <div class="flex gap-2">
                                    <?php if ($o['status'] === 'placed'): ?>
                                        <form action="<?php echo BASE_URL; ?>api/wholesaler/process-order.php" method="POST">
                                            <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                                            <button type="submit" name="action" value="accept" class="px-6 py-3 bg-green-600 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-green-600/20 hover:scale-105 active:scale-95 transition-all">Accept</button>
                                        </form>
                                    <?php elseif ($o['status'] === 'wholesaler_accepted'): ?>
                                        <form action="<?php echo BASE_URL; ?>api/wholesaler/submit-to-distributor.php" method="POST">
                                            <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                                            <button type="submit" class="px-6 py-3 bg-nestle-blue text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-nestle-blue/20 hover:scale-105 active:scale-95 transition-all">Submit Upward</button>
                                        </form>
                                    <?php else: ?>
                                        <button disabled class="px-6 py-3 bg-gray-50 text-gray-400 border border-gray-100 rounded-xl text-xs font-black uppercase tracking-widest cursor-not-allowed">Locked</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
