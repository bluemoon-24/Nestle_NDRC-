<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAuth(['nestle']);

// Handle stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $product_id = $_POST['product_id'];
    $new_stock = $_POST['total_stock'];
    
    $stmt = $pdo->prepare("UPDATE warehouse_stock SET total_stock = ? WHERE product_id = ?");
    $stmt->execute([$new_stock, $product_id]);
    header('Location: /nestle/warehouse.php?success=1');
    exit();
}

// Fetch all products with stock
$products = $pdo->query("
    SELECT p.*, ws.total_stock, ws.reserved_stock, ws.available_stock, ws.reorder_point
    FROM products p
    JOIN warehouse_stock ws ON p.id = ws.product_id
    ORDER BY p.name ASC
")->fetchAll();

include '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 flex justify-between items-end">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Central Warehouse</h2>
            <p class="mt-1 text-sm text-gray-500">Monitor and update inventory levels for all 247 products</p>
        </div>
        <div>
            <span class="inline-flex items-center rounded-md bg-blue-50 px-3 py-1 text-sm font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                <?php echo count($products); ?> Products Tracked
            </span>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center">
            <span class="mr-2">✅</span> Stock updated successfully.
        </div>
    <?php endif; ?>

    <div class="bg-white shadow rounded-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Product / SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Category</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Total Stock</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Reserved</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Available</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php foreach ($products as $p): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900"><?php echo h($p['name']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo h($p['sku']); ?> • <?php echo h($p['unit']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                                    <?php echo h($p['category']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-900">
                                <?php echo number_format($p['total_stock']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-sm text-orange-600 font-medium"><?php echo number_format($p['reserved_stock']); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-3 py-1 rounded-lg text-sm font-bold <?php echo $p['available_stock'] < $p['reorder_point'] ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
                                    <?php echo number_format($p['available_stock']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="openModal(<?php echo $p['id']; ?>, '<?php echo addslashes($p['name']); ?>', <?php echo $p['total_stock']; ?>)" class="text-nestle-blue hover:text-nestle-blue/80 bg-nestle-blue/10 px-3 py-1.5 rounded-lg border border-nestle-blue/20">Update Stock</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Stock Modal -->
<div id="stockModal" class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900">Update Stock</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form action="" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="product_id" id="modalProductId">
            <div>
                <label class="block text-sm font-bold text-gray-500 uppercase tracking-widest mb-1">Product</label>
                <div id="modalProductName" class="text-lg font-bold text-gray-900"></div>
            </div>
            <div>
                <label for="total_stock" class="block text-sm font-bold text-gray-500 uppercase tracking-widest mb-1">New Total Stock Quantity</label>
                <input type="number" name="total_stock" id="modalTotalStock" required class="block w-full border-gray-200 rounded-xl focus:ring-2 focus:ring-nestle-blue focus:border-nestle-blue shadow-sm py-3 px-4">
            </div>
            <div class="pt-4 flex space-x-3">
                <button type="button" onclick="closeModal()" class="flex-1 px-4 py-3 rounded-xl border border-gray-200 text-gray-600 font-bold hover:bg-gray-50 transition-all">Cancel</button>
                <button type="submit" name="update_stock" class="flex-1 px-4 py-3 rounded-xl bg-nestle-brown text-white font-bold hover:bg-nestle-brown/90 shadow-lg shadow-nestle-brown/20 transition-all">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id, name, stock) {
    document.getElementById('modalProductId').value = id;
    document.getElementById('modalProductName').textContent = name;
    document.getElementById('modalTotalStock').value = stock;
    document.getElementById('stockModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('stockModal').classList.add('hidden');
}
</script>

<?php include '../includes/footer.php'; ?>
