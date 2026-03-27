<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAuth(['nestle']);

// 1. Handle Bulk Stock Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_update'])) {
    $updates = $_POST['stock_data'] ?? [];
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE warehouse_stock SET total_stock = ?, last_updated = CURRENT_TIMESTAMP WHERE product_id = ?");
        foreach ($updates as $p_id => $qty) {
            $stmt->execute([(int)$qty, (int)$p_id]);
        }
        $pdo->commit();
        header('Location: ' . BASE_URL . 'nestle/warehouse.php?success=Inventory globally synchronized');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// 2. Fetch all products with stock
$products = $pdo->query("
    SELECT p.*, ws.total_stock, ws.reserved_stock, ws.available_stock, ws.reorder_point
    FROM products p
    JOIN warehouse_stock ws ON p.id = ws.product_id
    ORDER BY ws.available_stock ASC, p.name ASC
")->fetchAll();

// 3. Fetch distributor orders (Incoming)
$factory_orders_stmt = $pdo->prepare("
    SELECT fo.*, u.name as distributor_name 
    FROM factory_orders fo
    JOIN users u ON fo.distributor_id = u.id
    WHERE fo.status = 'pending'
    ORDER BY fo.created_at DESC
");
$factory_orders_stmt->execute();
$incoming_orders = $factory_orders_stmt->fetchAll();

include '../includes/header.php';
?>

<div class="min-h-screen bg-gray-50/50 pb-20" x-data="{ tab: 'inventory', bulkMode: false }">
    <!-- Premium Header -->
    <div class="bg-white border-b border-gray-100 pt-16 pb-24 shadow-sm relative overflow-hidden">
        <div class="absolute right-0 top-0 w-1/3 h-full bg-nestle-blue/5 blur-3xl rounded-full translate-x-1/2 -translate-y-1/2"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <h1 class="text-5xl font-black text-gray-900 tracking-tight leading-none mb-3">Warehouse Command</h1>
                    <p class="text-lg text-gray-400 font-medium">Production sync & last-mile fulfillment monitoring.</p>
                </div>
                <div class="flex items-center gap-4">
                    <button @click="bulkMode = !bulkMode" :class="bulkMode ? 'bg-orange-500 shadow-orange-500/20' : 'bg-gray-900 shadow-gray-900/10'" class="px-8 py-5 text-white rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest shadow-xl transition-all hover:scale-105">
                        <span x-text="bulkMode ? 'EXIT BULK UPDATE' : 'PRODUCTION BATCH SYNC'"></span>
                    </button>
                    <div class="px-6 py-4 bg-white rounded-[2rem] border border-gray-100 shadow-xl shadow-gray-200/50 hidden md:block">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Global Tracking</p>
                        <p class="text-xl font-black text-gray-900"><?php echo count($products); ?> Products</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-20">
        <!-- Tab Controls -->
        <div class="flex items-center gap-2 mb-10 p-2 bg-white/80 backdrop-blur-md rounded-[2rem] shadow-xl border border-white/50 w-fit">
            <button @click="tab = 'inventory'" :class="tab === 'inventory' ? 'bg-gray-900 text-white shadow-lg' : 'text-gray-400 hover:text-gray-900'" class="px-8 py-4 rounded-[1.5rem] text-xs font-black uppercase tracking-widest transition-all">Stock Levels</button>
            <button @click="tab = 'orders'" :class="tab === 'orders' ? 'bg-nestle-blue text-white shadow-lg' : 'text-gray-400 hover:text-nestle-blue'" class="px-8 py-4 rounded-[1.5rem] text-xs font-black uppercase tracking-widest transition-all relative">
                Incoming Supply Requests
                <?php if (!empty($incoming_orders)): ?>
                    <span class="absolute -top-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-[10px] font-black text-white ring-4 ring-gray-50"><?php echo count($incoming_orders); ?></span>
                <?php endif; ?>
            </button>
        </div>

        <!-- Inventory Tab -->
        <div x-show="tab === 'inventory'" class="space-y-8 animate-in fade-in slide-in-from-bottom-3 duration-500">
            <!-- Feedback Messages -->
            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-500 text-white px-10 py-6 rounded-[2.5rem] shadow-2xl shadow-green-500/20 font-black flex items-center justify-between">
                    <p class="flex items-center gap-4">✅ <span class="uppercase tracking-widest text-xs"><?php echo h($_GET['success']); ?></span></p>
                    <button class="opacity-50 hover:opacity-100" onclick="this.parentElement.style.display='none'">✕</button>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="bg-white shadow-2xl rounded-[3rem] border border-gray-100 overflow-hidden">
                    <div class="px-10 py-10 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-gray-50/30">
                        <div class="flex items-center gap-4">
                            <h3 class="text-xl font-black text-gray-900 uppercase tracking-widest">Global Catalog</h3>
                            <span x-show="bulkMode" class="px-4 py-1.5 bg-orange-100 text-orange-600 rounded-lg text-[10px] font-black uppercase animate-pulse">Bulk Mode Active</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="relative w-full md:w-80">
                                <input type="text" id="productSearch" onkeyup="filterProducts()" placeholder="Search catalog..." class="w-full bg-white border-gray-100 rounded-full py-4 px-8 text-sm font-bold shadow-sm focus:ring-2 focus:ring-nestle-blue focus:border-nestle-blue transition-all">
                                <span class="absolute right-6 top-1/2 -translate-y-1/2 opacity-20">🔍</span>
                            </div>
                            <button type="submit" name="bulk_update" x-show="bulkMode" class="px-10 py-4 bg-orange-600 text-white rounded-full text-xs font-black uppercase tracking-widest hover:scale-105 transition-all shadow-xl shadow-orange-600/20">COMMIT SYNC</button>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto no-scrollbar">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-white">
                                <tr>
                                    <th class="px-10 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Product / Sku Instance</th>
                                    <th class="px-10 py-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                                    <th class="px-10 py-6 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Reserved</th>
                                    <th class="px-10 py-6 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Available Net</th>
                                    <th class="px-10 py-6 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Sync Quantity</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-50">
                                <?php foreach ($products as $p): 
                                    $is_low = $p['available_stock'] <= $p['reorder_point'];
                                ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors product-row">
                                        <td class="px-10 py-8 whitespace-nowrap">
                                            <div class="flex items-center gap-4">
                                                <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-xl">📦</div>
                                                <div>
                                                    <p class="text-sm font-black text-gray-900"><?php echo h($p['name']); ?></p>
                                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter mt-1"><?php echo h($p['sku']); ?> • <?php echo h($p['unit']); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-10 py-8 whitespace-nowrap">
                                            <span class="px-3 py-1.5 rounded-xl bg-gray-50 border border-gray-100 text-[10px] font-black uppercase text-gray-600 tracking-tighter">
                                                <?php echo h($p['category']); ?>
                                            </span>
                                        </td>
                                        <td class="px-10 py-8 whitespace-nowrap text-center text-sm font-bold text-gray-400">
                                            <?php echo number_format($p['reserved_stock']); ?>
                                        </td>
                                        <td class="px-10 py-8 whitespace-nowrap text-center">
                                            <div class="inline-block px-4 py-2 rounded-2xl border <?php echo $is_low ? 'bg-red-50 border-red-100 text-red-600' : 'bg-green-50 border-green-100 text-green-600'; ?>">
                                                <p class="text-sm font-black leading-none"><?php echo number_format($p['available_stock']); ?></p>
                                                <p class="text-[8px] font-black uppercase mt-1 tracking-widest"><?php echo $is_low ? 'LOW' : 'STABLE'; ?></p>
                                            </div>
                                        </td>
                                        <td class="px-10 py-8 whitespace-nowrap text-center">
                                            <div x-show="!bulkMode" class="text-sm font-black text-gray-900"><?php echo number_format($p['total_stock']); ?></div>
                                            <div x-show="bulkMode" class="animate-in fade-in zoom-in-95 duration-200">
                                                <input type="number" 
                                                       name="stock_data[<?php echo $p['id']; ?>]" 
                                                       value="<?php echo $p['total_stock']; ?>" 
                                                       class="w-32 bg-orange-50/50 border-orange-100 rounded-xl py-3 px-4 text-center text-sm font-black focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>

        <!-- Orders Tab -->
        <div x-show="tab === 'orders'" class="space-y-8 animate-in fade-in slide-in-from-bottom-3 duration-500">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xl font-black text-nestle-blue uppercase tracking-[0.2em]">Replenishment Queue</h3>
            </div>

            <?php if (empty($incoming_orders)): ?>
                <div class="bg-white rounded-[3rem] p-32 text-center border-4 border-dashed border-gray-100 relative overflow-hidden">
                    <div class="absolute inset-0 bg-nestle-blue/5 opacity-20"></div>
                    <div class="relative z-10">
                        <div class="text-8xl mb-10 grayscale opacity-20">🏗️</div>
                        <h3 class="text-3xl font-black text-gray-200 uppercase tracking-[0.3em]">No Pending Requests</h3>
                        <p class="text-gray-400 mt-4 font-bold uppercase tracking-widest text-xs">Awaiting distributor restocking submissions</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid gap-6">
                    <?php foreach ($incoming_orders as $io): ?>
                        <div class="bg-white p-12 rounded-[3.5rem] border border-gray-100 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-12 group hover:border-nestle-blue/20 transition-all">
                            <div class="flex items-center gap-10">
                                <div class="w-24 h-24 bg-gray-50 rounded-[2.5rem] flex items-center justify-center text-5xl group-hover:bg-nestle-blue/5 transition-colors">📦</div>
                                <div>
                                    <div class="flex items-center gap-3 mb-3">
                                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Shipment: #<?php echo h($io['order_number']); ?></span>
                                        <span class="px-4 py-1 bg-nestle-blue text-white rounded-full text-[9px] font-black uppercase tracking-widest">Awaiting Sync</span>
                                    </div>
                                    <h4 class="text-3xl font-black text-gray-900"><?php echo h($io['distributor_name']); ?></h4>
                                    <p class="text-sm text-gray-400 font-bold mt-1 uppercase tracking-widest"><?php echo date('M d, Y • h:i A', strtotime($io['created_at'])); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex flex-col md:flex-row items-center gap-12 text-center md:text-right">
                                <div>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Batch Value</p>
                                    <p class="text-3xl font-black text-gray-900"><?php echo formatCurrency($io['total_amount']); ?></p>
                                </div>
                                <div class="flex gap-4">
                                    <form action="<?php echo BASE_URL; ?>api/nestle/handle-factory-order.php" method="POST">
                                        <input type="hidden" name="order_id" value="<?php echo $io['id']; ?>">
                                        <input type="hidden" name="action" value="accept">
                                        <button type="submit" class="px-10 py-6 bg-nestle-blue text-white rounded-[1.5rem] text-xs font-black uppercase tracking-widest shadow-xl shadow-nestle-blue/20 hover:scale-[1.05] active:scale-95 transition-all">Fulfill Order</button>
                                    </form>
                                    <form action="<?php echo BASE_URL; ?>api/nestle/handle-factory-order.php" method="POST">
                                        <input type="hidden" name="order_id" value="<?php echo $io['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="p-6 bg-gray-50 text-gray-400 hover:text-red-500 rounded-[1.5rem] text-xs font-black uppercase hover:bg-red-50 transition-all">Reject</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function filterProducts() {
    let input = document.getElementById('productSearch');
    let filter = input.value.toLowerCase();
    let rows = document.querySelectorAll('.product-row');

    rows.forEach(row => {
        let text = row.querySelector('p.font-black').textContent.toLowerCase();
        if (text.includes(filter)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
