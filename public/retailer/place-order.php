<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAuth(['retailer', 'wholesaler']); // Allow both to order

// Get products
$products = $pdo->query("SELECT * FROM products ORDER BY category, name")->fetchAll();

// Get the user's distributor and wholesaler details (Retailers only)
$user_id = $_SESSION['user_id'];
$user_stmt = $pdo->prepare("SELECT wholesaler_id, distributor_id, region, territory, order_direct FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$userData = $user_stmt->fetch();

$distributor_id = $userData['distributor_id'];

// If still empty (legacy accounts or wholesaler ordering direct), find best match
if (!$distributor_id) {
    $distributor_stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'distributor' AND (territory = ? OR region = ?) LIMIT 1");
    $distributor_stmt->execute([$userData['territory'], $userData['region']]);
    $dist = $distributor_stmt->fetch();
    $distributor_id = $dist['id'] ?? null;
}

include '../includes/header.php';
?>

<div class="min-h-screen bg-[#F9FAFB] py-12" x-data="{ cartOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-32 lg:pb-0">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
            <div>
                <nav class="flex mb-4" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-sm text-gray-500">
                        <li><a href="<?php echo BASE_URL; ?>retailer/dashboard.php" class="hover:text-nestle-blue font-medium transition-colors">Dashboard</a></li>
                        <li><svg class="h-5 w-5 text-gray-300" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg></li>
                        <li class="text-nestle-brown font-bold">New Inventory Order</li>
                    </ol>
                </nav>
                <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">Product Catalogue</h1>
            </div>
            
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="h-12 w-12 bg-nestle-blue/10 rounded-xl flex items-center justify-center text-nestle-blue">🏢</div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest leading-none mb-1">Pricing Mode</p>
                    <p class="text-nestle-brown font-bold text-sm">Distributor Rates</p>
                </div>
            </div>
        </div>

        <form id="orderForm" action="<?php echo BASE_URL; ?>api/retailer/place-order.php" method="POST">
            <input type="hidden" name="distributor_id" value="<?php echo $distributor_id; ?>">
            <input type="hidden" name="wholesaler_id" value="<?php echo $userData['wholesaler_id'] ?? ''; ?>">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                <!-- Product List -->
                <div class="lg:col-span-8 space-y-8">
                    <?php 
                    $current_cat = '';
                    foreach ($products as $p): 
                        if ($current_cat !== $p['category']):
                            $current_cat = $p['category'];
                    ?>
                        <div class="flex items-center gap-4 border-b border-gray-200 pb-2 mt-8">
                            <h2 class="text-lg font-black text-nestle-brown uppercase tracking-tighter"><?php echo h($current_cat); ?></h2>
                            <div class="flex-1 h-px bg-gradient-to-r from-gray-200 to-transparent"></div>
                        </div>
                    <?php endif; ?>

                    <div class="bg-white rounded-[2rem] border border-gray-100 p-5 sm:p-6 flex flex-col sm:flex-row items-center gap-6 shadow-sm hover:shadow-xl hover:border-nestle-blue/20 transition-all group overflow-hidden relative">
                        <!-- Product Icon -->
                        <div class="h-20 w-20 sm:h-24 sm:w-24 bg-[#F5F3F0] rounded-3xl flex-shrink-0 flex items-center justify-center text-4xl shadow-inner group-hover:scale-105 transition-transform">
                            <?php 
                            switch($p['category']) {
                                case 'Dairy': echo '🥛'; break;
                                case 'Beverages': echo '☕'; break;
                                case 'Noodles': echo '🍜'; break;
                                case 'Confectionery': echo '🍫'; break;
                                case 'Culinary': echo '🥣'; break;
                                default: echo '📦';
                            }
                            ?>
                        </div>

                        <!-- Details -->
                        <div class="flex-1 text-center sm:text-left">
                            <h3 class="text-lg font-bold text-gray-900 group-hover:text-nestle-blue transition-colors"><?php echo h($p['name']); ?></h3>
                            <div class="mt-2 flex flex-wrap justify-center sm:justify-start items-center gap-2">
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-lg text-[10px] font-bold uppercase tracking-widest"><?php echo h($p['sku']); ?></span>
                                <span class="px-2 py-1 bg-nestle-blue/5 text-nestle-blue rounded-lg text-[10px] font-bold uppercase tracking-widest"><?php echo h($p['unit']); ?></span>
                            </div>
                        </div>

                        <!-- Pricing & Qty -->
                        <div class="flex flex-col items-center sm:items-end gap-3 min-w-[150px]">
                            <p class="text-xl font-black text-nestle-brown">Rs <?php echo number_format($p['price'], 2); ?></p>
                            
                            <div class="flex items-center bg-gray-50 rounded-2xl p-1 border border-gray-100 shadow-inner">
                                <button type="button" @click="updateQty(<?php echo $p['id']; ?>, -1)" class="w-10 h-10 rounded-xl flex items-center justify-center text-gray-400 hover:text-nestle-brown hover:bg-white transition-all font-black text-xl">-</button>
                                <input type="number" name="items[<?php echo $p['id']; ?>][quantity]" id="qty_<?php echo $p['id']; ?>" value="0" min="0" @change="calculateTotal()" class="w-12 text-center bg-transparent border-0 p-0 text-sm font-black text-gray-900 focus:ring-0">
                                <input type="hidden" name="items[<?php echo $p['id']; ?>][price]" value="<?php echo $p['price']; ?>">
                                <input type="hidden" name="items[<?php echo $p['id']; ?>][name]" value="<?php echo h($p['name']); ?>">
                                <button type="button" @click="updateQty(<?php echo $p['id']; ?>, 1)" class="w-10 h-10 rounded-xl flex items-center justify-center text-nestle-blue hover:bg-white transition-all font-black text-xl">+</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Desktop Sidebar Cart -->
                <div class="hidden lg:block lg:col-span-4">
                    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-2xl sticky top-28 overflow-hidden">
                        <div class="bg-nestle-brown p-8 text-white">
                            <h3 class="text-2xl font-black tracking-tight">Order Summary</h3>
                            <p class="text-white/60 text-sm mt-1">Direct distributor fulfillment.</p>
                        </div>
                        <div class="p-8 space-y-6">
                            <div id="cartItems" class="space-y-4 max-h-[30vh] overflow-y-auto custom-scrollbar">
                                <p class="text-center py-10 opacity-30 select-none text-sm font-bold uppercase tracking-widest">Cart is Empty</p>
                            </div>
                            <div class="pt-6 border-t border-gray-100 flex justify-between items-center bg-nestle-blue/5 p-4 rounded-2xl">
                                <span class="text-nestle-blue font-bold">Total Amount</span>
                                <span id="totalAmount" class="text-2xl font-black text-gray-900">Rs 0.00</span>
                            </div>
                            <button type="submit" id="submitBtn" disabled class="w-full h-16 bg-gray-200 text-white font-black text-lg rounded-2xl cursor-not-allowed transition-all shadow-lg flex items-center justify-center gap-3">
                                <span>Send Order Request</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Sticky Bottom Bar & Drawer -->
            <div class="lg:hidden fixed bottom-0 left-0 w-full z-[60]">
                <!-- Mobile Drawer (Backdrop) -->
                <div x-show="cartOpen" @click="cartOpen = false" class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>
                
                <!-- Mobile Drawer (Content) -->
                <div x-show="cartOpen" 
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="translate-y-full"
                     x-transition:enter-end="translate-y-0"
                     class="relative bg-white rounded-t-[3rem] shadow-2xl p-8 pb-32">
                    <div class="w-12 h-1.5 bg-gray-200 rounded-full mx-auto mb-8"></div>
                    <h3 class="text-2xl font-black text-gray-900 mb-6 font-serif">Order Review</h3>
                    <div id="cartItemsMobile" class="space-y-4 max-h-[50vh] overflow-y-auto py-2 no-scrollbar">
                        <!-- Cloned from desktop via JS -->
                    </div>
                </div>

                <!-- Primary Bottom Bar -->
                <div class="bg-white border-t border-gray-100 px-6 py-4 flex items-center justify-between shadow-[0_-10px_40px_rgba(0,0,0,0.1)] relative z-10">
                    <div @click="cartOpen = !cartOpen" class="flex flex-col">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Selected Total</p>
                        <p id="totalAmountMobile" class="text-xl font-black text-gray-900">Rs 0.00</p>
                    </div>
                    <button type="submit" id="submitBtnMobile" disabled class="bg-gray-200 text-white px-8 py-4 rounded-2xl font-black text-sm shadow-xl active:scale-95 transition-all flex items-center gap-2">
                        Place Order 🛒
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
        </form>
    </div>
</div>

<style>
/* Custom scrollbar for better aesthetics */
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 20px; }

/* Remove number input arrows */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; margin: 0; 
}
input[type=number] { -moz-appearance: textfield; }
</style>

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
    let subtotal = 0;
    let hasItems = false;
    let cartHtml = '';

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
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">
                    <div class="flex-1">
                        <p class="text-sm font-black text-gray-900 leading-tight">${name}</p>
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest mt-1">${qty} x Rs ${price.toFixed(2)}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-black text-nestle-brown">Rs ${(qty * price).toLocaleString(undefined, {minimumFractionDigits: 2})}</p>
                    </div>
                </div>
            `;
        }
    });

    const formattedTotal = 'Rs ' + subtotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    // Update Desktop
    document.getElementById('totalAmount').textContent = formattedTotal;
    document.getElementById('cartItems').innerHTML = hasItems ? cartHtml : '<p class="text-center py-10 opacity-30 select-none text-sm font-bold uppercase tracking-widest">Cart is Empty</p>';
    
    // Update Mobile
    const mobileTotal = document.getElementById('totalAmountMobile');
    const mobileCart = document.getElementById('cartItemsMobile');
    if (mobileTotal) mobileTotal.textContent = formattedTotal;
    if (mobileCart) mobileCart.innerHTML = hasItems ? cartHtml : '<p class="text-center py-10 opacity-30 select-none text-sm font-bold uppercase tracking-widest text-gray-900">Cart is Empty</p>';

    // Update Buttons
    const btn = document.getElementById('submitBtn');
    const btnMobile = document.getElementById('submitBtnMobile');
    
    [btn, btnMobile].forEach(b => {
        if (!b) return;
        if (!hasItems) {
            b.disabled = true;
            b.classList.add('bg-gray-200', 'cursor-not-allowed');
            b.classList.remove('bg-nestle-blue', 'bg-gray-900');
        } else {
            b.disabled = false;
            b.classList.remove('bg-gray-200', 'cursor-not-allowed');
            b.classList.add('bg-nestle-blue');
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
