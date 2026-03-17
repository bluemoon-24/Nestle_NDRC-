<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAuth(['retailer']);

// Get products
$products = $pdo->query("SELECT * FROM products ORDER BY category, name")->fetchAll();

// Get the user's distributor and wholesaler details
$user_id = $_SESSION['user_id'];
$user = $pdo->prepare("SELECT wholesaler_id, region, territory, order_direct FROM users WHERE id = ?");
$user->execute([$user_id]);
$userData = $user->fetch();

// If direct, we need to pick a distributor for their territory/region. 
// For MVP simplicity, we'll assign them to Distributor 1 if not specified, 
// or fetch the first distributor in their territory.
$distributor_stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'distributor' AND (territory = ? OR region = ?) LIMIT 1");
$distributor_stmt->execute([$userData['territory'], $userData['region']]);
$distributor = $distributor_stmt->fetch();
$distributor_id = $distributor['id'] ?? 1; // Fallback to 1

include '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="/retailer/dashboard.php" class="text-sm font-semibold text-nestle-blue mb-2 inline-block">← Back to Dashboard</a>
        <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Place New Order</h2>
        <p class="text-sm text-gray-500">Select products from the Nestlé catalog to restock your shop.</p>
    </div>

    <form id="orderForm" action="/api/retailer/place-order.php" method="POST">
        <input type="hidden" name="distributor_id" value="<?php echo $distributor_id; ?>">
        <input type="hidden" name="wholesaler_id" value="<?php echo $userData['wholesaler_id']; ?>">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Product Selection -->
            <div class="lg:col-span-2 space-y-6">
                <?php 
                $current_cat = '';
                foreach ($products as $p): 
                    if ($current_cat !== $p['category']):
                        $current_cat = $p['category'];
                ?>
                    <h3 class="text-lg font-bold text-nestle-brown border-b-2 border-nestle-blue w-fit pb-1 mt-8"><?php echo h($current_cat); ?></h3>
                <?php endif; ?>

                <div class="bg-white rounded-xl border border-gray-100 p-4 flex items-center shadow-sm hover:shadow-md transition-shadow">
                    <div class="h-16 w-16 bg-nestle-bg rounded-lg flex-shrink-0 flex items-center justify-center text-2xl">
                        <?php 
                        switch($p['category']) {
                            case 'Dairy': echo '🥛'; break;
                            case 'Beverages': echo '☕'; break;
                            case 'Noodles': echo '🍜'; break;
                            case 'Confectionery': echo '🍫'; break;
                            default: echo '📦';
                        }
                        ?>
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="text-sm font-bold text-gray-900"><?php echo h($p['name']); ?></div>
                        <div class="text-xs text-gray-500"><?php echo formatCurrency($p['price']); ?> / <?php echo h($p['unit']); ?></div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button type="button" onclick="updateQty(<?php echo $p['id']; ?>, -1)" class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center hover:bg-gray-50">-</button>
                        <input type="number" name="items[<?php echo $p['id']; ?>][quantity]" id="qty_<?php echo $p['id']; ?>" value="0" min="0" onchange="calculateTotal()" class="w-12 text-center border-0 p-0 text-sm font-bold text-gray-900 focus:ring-0">
                        <input type="hidden" name="items[<?php echo $p['id']; ?>][price]" value="<?php echo $p['price']; ?>">
                        <input type="hidden" name="items[<?php echo $p['id']; ?>][name]" value="<?php echo h($p['name']); ?>">
                        <button type="button" onclick="updateQty(<?php echo $p['id']; ?>, 1)" class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center hover:bg-gray-50">+</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-xl sticky top-24">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Order Summary</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div id="cartItems" class="space-y-3 max-h-64 overflow-y-auto">
                            <p class="text-sm text-gray-400 italic">Your cart is empty</p>
                        </div>
                        <div class="border-t border-gray-100 pt-4 space-y-2">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Subtotal</span>
                                <span id="subtotal">Rs 0.00</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Estimated Delivery</span>
                                <span class="text-nestle-success font-medium">3-5 Days</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t border-gray-50">
                                <span>Total Amount</span>
                                <span id="totalAmount">Rs 0.00</span>
                            </div>
                        </div>
                        <div class="pt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                            <textarea name="notes" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-nestle-blue focus:border-nestle-blue sm:text-sm"></textarea>
                        </div>
                        <button type="submit" id="submitBtn" disabled class="w-full bg-gray-300 text-white font-bold py-4 rounded-xl cursor-not-allowed transition-all">
                            Complete Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function updateQty(id, delta) {
    const input = document.getElementById('qty_' + id);
    let val = parseInt(input.value) + delta;
    if (val < 0) val = 0;
    input.value = val;
    calculateTotal();
}

function calculateTotal() {
    const form = document.getElementById('orderForm');
    const formData = new FormData(form);
    let subtotal = 0;
    let hasItems = false;
    let cartHtml = '';

    // Iterate through items
    const quantities = form.querySelectorAll('input[type="number"]');
    quantities.forEach(input => {
        const qty = parseInt(input.value);
        if (qty > 0) {
            hasItems = true;
            const idMatch = input.name.match(/items\[(\d+)\]/);
            const id = idMatch[1];
            const price = parseFloat(form.querySelector(`input[name="items[${id}][price]"]`).value);
            const name = form.querySelector(`input[name="items[${id}][name]"]`).value;
            
            subtotal += qty * price;
            cartHtml += `
                <div class="flex justify-between text-xs">
                    <span class="text-gray-600">${qty}x ${name}</span>
                    <span class="font-medium text-gray-900">Rs ${(qty * price).toLocaleString()}</span>
                </div>
            `;
        }
    });

    if (!hasItems) {
        cartHtml = '<p class="text-sm text-gray-400 italic">Your cart is empty</p>';
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').classList.replace('bg-nestle-brown', 'bg-gray-300');
        document.getElementById('submitBtn').classList.add('cursor-not-allowed');
    } else {
        document.getElementById('submitBtn').disabled = false;
        document.getElementById('submitBtn').classList.replace('bg-gray-300', 'bg-nestle-brown');
        document.getElementById('submitBtn').classList.remove('cursor-not-allowed');
        document.getElementById('submitBtn').classList.add('hover:bg-nestle-brown/90', 'shadow-lg', 'shadow-nestle-brown/20');
    }

    document.getElementById('cartItems').innerHTML = cartHtml;
    document.getElementById('subtotal').textContent = 'Rs ' + subtotal.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('totalAmount').textContent = 'Rs ' + subtotal.toLocaleString(undefined, {minimumFractionDigits: 2});
}
</script>

<?php include '../includes/footer.php'; ?>
