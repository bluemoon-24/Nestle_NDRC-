<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAuth(['nestle']);

// Get summary stats
$today = date('Y-m-d');
$stats = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM orders WHERE DATE(order_date) = '$today') as total_today,
        (SELECT COUNT(*) FROM orders WHERE status = 'distributor_confirmed' AND DATE(order_date) = '$today') as confirmed_today,
        (SELECT COUNT(*) FROM orders WHERE status = 'distributor_pending' AND DATE(order_date) = '$today') as pending_today,
        (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'distributor_confirmed' AND DATE(order_date) = '$today') as value_today
")->fetch();

include '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">Nestlé Command Center</h2>
            <p class="text-sm text-gray-500 mt-1">Real-time order aggregation and visibility</p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0 space-x-3">
            <a href="<?php echo BASE_URL; ?>api/nestle/export-report.php" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                📥 Export Report
            </a>
            <button type="button" onclick="updateAggregation()" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Refresh Map
            </button>
            <a href="<?php echo BASE_URL; ?>nestle/warehouse.php" class="inline-flex items-center rounded-md bg-nestle-brown px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-nestle-brown/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-nestle-brown">
                Manage Stock
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-xl border border-gray-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-nestle-brown/10 rounded-lg p-3">
                        <span class="text-2xl">📦</span>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Orders Today</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900" id="totalOrders"><?php echo $stats['total_today']; ?></div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-xl border border-gray-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-nestle-success/10 rounded-lg p-3">
                        <span class="text-2xl">✅</span>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Confirmed Orders</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900" id="confirmedOrders"><?php echo $stats['confirmed_today']; ?></div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-xl border border-gray-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-nestle-warning/10 rounded-lg p-3">
                        <span class="text-2xl">⏳</span>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Confirmation</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900" id="pendingOrders"><?php echo $stats['pending_today']; ?></div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-xl border border-gray-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-nestle-blue/10 rounded-lg p-3">
                        <span class="text-2xl">💰</span>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Confirmed Value</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900" id="confirmedValue"><?php echo formatCurrency($stats['value_today']); ?></div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Aggregation Table -->
    <div class="bg-white shadow rounded-xl border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900 leading-6">Distributor Breakdown</h3>
            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 animate-pulse">
                <span class="h-1.5 w-1.5 rounded-full bg-green-400 mr-2"></span>
                Live Updates Active
            </span>
        </div>
        <div id="aggregationTable" class="p-6">
            <p class="text-center text-gray-400 py-10">Loading real-time data...</p>
        </div>
    </div>
</div>

<script>
function updateAggregation() {
    fetch('<?php echo BASE_URL; ?>api/nestle/aggregation.php')
        .then(r => r.json())
        .then(data => {
            document.getElementById('totalOrders').textContent = data.summary.total;
            document.getElementById('confirmedOrders').textContent = data.summary.confirmed;
            document.getElementById('pendingOrders').textContent = data.summary.pending;
            document.getElementById('confirmedValue').textContent = data.summary.value_formatted;
            
            let html = '<div class="space-y-4">';
            if (Object.keys(data.distributors).length === 0) {
                html += '<p class="text-center text-gray-500 py-10">No orders confirmed today yet.</p>';
            } else {
                for (let dId in data.distributors) {
                    let d = data.distributors[dId];
                    html += `
                        <div class="border rounded-xl overflow-hidden shadow-sm">
                            <div class="bg-gray-50 px-4 py-3 flex justify-between items-center cursor-pointer hover:bg-gray-100 transition-colors" onclick="this.nextElementSibling.classList.toggle('hidden')">
                                <div class="flex items-center">
                                    <span class="text-lg mr-3">🏢</span>
                                    <div>
                                        <h4 class="font-bold text-gray-900">${d.name}</h4>
                                        <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">NDRC Node</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-6">
                                    <div class="text-center">
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Incoming</p>
                                        <p class="text-lg font-bold text-orange-600">${d.incoming_count}</p>
                                    </div>
                                    <div class="text-center border-l border-gray-200 pl-6">
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Outgoing</p>
                                        <p class="text-lg font-bold text-green-600">${d.outgoing_count}</p>
                                    </div>
                                    <div class="text-right border-l border-gray-200 pl-6 min-w-[120px]">
                                        <p class="font-bold text-nestle-brown text-lg">Rs ${parseFloat(d.total_value).toLocaleString()}</p>
                                        <p class="text-[10px] text-nestle-blue uppercase font-bold tracking-widest">Confirmed Value</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 space-y-4 bg-white border-t border-gray-50">
                                <div>
                                    <h5 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Wholesaler Channels</h5>
                                    <div class="space-y-2 ml-4">
                    `;
                    
                    if (d.wholesalers && Object.keys(d.wholesalers).length > 0) {
                        for (let wId in d.wholesalers) {
                            let w = d.wholesalers[wId];
                            html += `
                                <div class="flex items-center justify-between p-2 rounded-lg bg-nestle-bg/50 border border-gray-100">
                                    <div class="flex items-center">
                                        <span class="text-sm mr-2">🏭</span>
                                        <span class="text-sm font-medium text-gray-700">${w.name}</span>
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <span class="text-xs text-gray-500">${w.retailer_count} Retailers</span>
                                        <span class="text-sm font-bold text-gray-900">${w.order_count} Orders</span>
                                    </div>
                                </div>
                            `;
                        }
                    } else {
                        html += '<p class="text-xs text-gray-400 italic">No wholesaler orders</p>';
                    }
                    
                    html += `
                                    </div>
                                </div>
                                <div>
                                    <h5 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Direct Retailers</h5>
                                    <div class="space-y-2 ml-4">
                    `;
                    
                    if (d.direct_retailers && Object.keys(d.direct_retailers).length > 0) {
                        for (let rId in d.direct_retailers) {
                            let r = d.direct_retailers[rId];
                            html += `
                                <div class="flex items-center justify-between p-2 rounded-lg bg-blue-50/30 border border-blue-100">
                                    <div class="flex items-center">
                                        <span class="text-sm mr-2">🏪</span>
                                        <span class="text-sm font-medium text-gray-700">${r.name}</span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900">${r.order_count} Orders</span>
                                </div>
                            `;
                        }
                    } else {
                        html += '<p class="text-xs text-gray-400 italic">No direct retailer orders</p>';
                    }
                    
                    html += `
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }
            }
            html += '</div>';
            document.getElementById('aggregationTable').innerHTML = html;
        });
}

// Initial update and then every 30s
updateAggregation();
setInterval(updateAggregation, 30000);
</script>

<?php include '../includes/footer.php'; ?>
