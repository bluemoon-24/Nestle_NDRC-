<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAuth(['distributor']);

// Fetch products for ordering
$products = $pdo->query("SELECT * FROM products ORDER BY category, name")->fetchAll();

include '../includes/header.php';
?>

<div class="min-h-screen bg-gray-50/50 pb-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10">
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <a href="<?php echo BASE_URL; ?>distributor/dashboard.php" class="text-[10px] font-black text-nestle-blue uppercase tracking-[0.2em] mb-4 inline-block hover:opacity-70">← Return to Control Panel</a>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight leading-none mb-3">Factory Restock</h1>
                <p class="text-gray-400 font-bold uppercase tracking-widest text-xs">Direct replenishment from Nestlé Central Warehouse</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="px-6 py-4 bg-white rounded-3xl border border-gray-100 shadow-xl shadow-gray-200/50">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Batch Value</p>
                    <p id="totalAmount" class="text-xl font-black text-gray-900">Rs 0.00</p>
                </div>
            </div>
        </div>

        <form id="factoryOrderForm" action="<?php echo BASE_URL; ?>api/distributor/place-factory-order.php" method="POST">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                <!-- Product List -->
                <div class="lg:col-span-2 space-y-12">
                    <?php 
                    $current_cat = '';
                    foreach ($products as $p): 
                        if ($current_cat !== $p['category']):
                            $current_cat = $p['category'];
                    ?>
                        <div class="pt-8 first:pt-0">
                            <h2 class="text-xs font-black text-nestle-blue uppercase tracking-[0.3em] mb-6 flex items-center gap-4">
                                <?php echo h($current_cat); ?>
                                <span class="h-px flex-1 bg-nestle-blue/10"></span>
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php endif; ?>
                                
                                <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-xl shadow-gray-200/20 hover:border-nestle-blue/20 transition-all group">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1">
                                            <p class="text-sm font-black text-gray-900 leading-tight group-hover:text-nestle-blue transition-colors"><?php echo h($p['name']); ?></p>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter mt-1"><?php echo h($p['sku']); ?> • <?php echo h($p['unit']); ?></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-black text-gray-900"><?php echo formatCurrency($p['price']); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-between gap-4 mt-6 pt-6 border-t border-gray-50">
                                        <div class="flex items-center bg-gray-50 rounded-2xl p-1 border border-gray-100">
                                            <button type="button" onclick="updateQty(<?php echo $p['id']; ?>, -1)" class="w-10 h-10 flex items-center justify-center font-black text-gray-400 hover:text-gray-900">－</button>
                                            <input type="number" 
                                                   id="qty_<?php echo $p['id']; ?>" 
                                                   name="items[<?php echo $p['id']; ?>][qty]" 
                                                   value="0" min="0" 
                                                   class="w-16 bg-transparent border-none text-center font-black text-sm focus:ring-0"
                                                   onchange="calculateTotal()">
                                            <input type="hidden" name="items[<?php echo $p['id']; ?>][price]" value="<?php echo $p['price']; ?>">
                                            <input type="hidden" name="items[<?php echo $p['id']; ?>][name]" value="<?php echo $p['name']; ?>">
                                            <button type="button" onclick="updateQty(<?php echo $p['id']; ?>, 1)" class="w-10 h-10 flex items-center justify-center font-black text-gray-400 hover:text-gray-900">＋</button>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest hidden group-focus-within:block">Line Total</p>
                                            <p id="line_total_<?php echo $p['id']; ?>" class="text-xs font-black text-nestle-blue">Rs 0.00</p>
                                        </div>
                                    </div>
                                </div>

                                <?php 
                                // Peek next product to see if category changes
                                $next = next($products);
                                if (!$next || $next['category'] !== $current_cat):
                                ?>
                            </div>
                        </div>
                    <?php 
                        endif;
                        prev($products); // Step back after peek
                        next($products); // Step forward for loop
                    endforeach; 
                    ?>
                </div>

                <!-- Summary Sidebar -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24 space-y-6">
                        <div class="bg-gray-900 rounded-[3rem] p-10 text-white shadow-2xl shadow-gray-900/20">
                            <h3 class="text-xl font-black uppercase tracking-widest mb-8">Order Summary</h3>
                            <div id="cartSummary" class="space-y-4 mb-10 min-h-[100px]">
                                <p class="text-center py-10 opacity-30 select-none text-[10px] font-black uppercase tracking-[0.2em]">No Items Selected</p>
                            </div>
                            
                            <div class="pt-8 border-t border-white/10 flex justify-between items-end mb-10">
                                <div>
                                    <p class="text-[10px] font-black text-white/40 uppercase tracking-widest mb-1">Total Payable</p>
                                    <p id="summaryTotal" class="text-3xl font-black">Rs 0.00</p>
                                </div>
                            </div>

                            <button type="submit" id="submitBtn" disabled class="w-full py-6 bg-nestle-blue text-white rounded-[1.5rem] font-black uppercase tracking-[0.2em] shadow-xl shadow-nestle-blue/20 disabled:opacity-30 disabled:grayscale transition-all hover:scale-[1.02] active:scale-95">Submit to Nestlé</button>
                        </div>

                        <div class="bg-white rounded-[2rem] p-8 border border-gray-100 text-center">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-relaxed">Replenishment requests are reviewed by central warehouse administrators before dispatch.</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
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
    const form = document.getElementById('factoryOrderForm');
    let total = 0;
    let hasItems = false;
    let summaryHtml = '';

    const inputs = form.querySelectorAll('input[type="number"]');
    inputs.forEach(input => {
        const qty = parseInt(input.value);
        const idMatch = input.id.match(/qty_(\d+)/);
        const id = idMatch[1];
        const price = parseFloat(form.querySelector(`input[name="items[${id}][price]"]`).value);
        const name = form.querySelector(`input[name="items[${id}][name]"]`).value;
        const lineTotal = qty * price;

        document.getElementById('line_total_' + id).textContent = 'Rs ' + lineTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
        
        if (qty > 0) {
            hasItems = true;
            total += lineTotal;
            summaryHtml += `
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <p class="text-xs font-black leading-tight">${name}</p>
                        <p class="text-[9px] font-bold opacity-40 mt-1">${qty} x Rs ${price.toFixed(2)}</p>
                    </div>
                    <p class="text-xs font-black">Rs ${lineTotal.toFixed(2).toLocaleString()}</p>
                </div>
            `;
        }
    });

    const formatted = 'Rs ' + total.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('totalAmount').textContent = formatted;
    document.getElementById('summaryTotal').textContent = formatted;
    document.getElementById('cartSummary').innerHTML = hasItems ? summaryHtml : '<p class="text-center py-10 opacity-30 select-none text-[10px] font-black uppercase tracking-[0.2em]">No Items Selected</p>';
    
    document.getElementById('submitBtn').disabled = !hasItems;
}
</script>

<?php include '../includes/footer.php'; ?>
