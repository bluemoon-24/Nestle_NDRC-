<?php
$pageTitle = "Nestle NDRC - Last Mile Dashboard";
ob_start();
?>

<div class="space-y-8">
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Last Mile Visibility</h1>
            <p class="text-slate-500 mt-1">Real-time tracking and logistics overview.</p>
        </div>
        <div class="flex gap-2 text-sm">
            <span class="px-3 py-1 bg-nestle-brown/10 text-nestle-brown rounded-full font-medium">Logistics Center</span>
            <span class="px-3 py-1 bg-nestle-blue/10 text-nestle-blue rounded-full font-medium">12 Active Drivers</span>
        </div>
    </header>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="glass-card p-6 border-l-4 border-nestle-blue">
            <p class="text-sm font-medium text-slate-500">Total Deliveries Today</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-2">1,248</h3>
            <div class="mt-4 flex items-center text-xs text-nestle-blue">
                 <span class="bg-nestle-blue/10 p-0.5 rounded mr-1">↑ 12%</span> vs yesterday
            </div>
        </div>
        <div class="glass-card p-6 border-l-4 border-nestle-brown">
            <p class="text-sm font-medium text-slate-500">In Transit (Coffee/Sweets)</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-2">412</h3>
            <div class="mt-4 w-full bg-slate-100 rounded-full h-1.5">
                <div class="bg-nestle-brown h-1.5 rounded-full" style="width: 65%"></div>
            </div>
        </div>
        <div class="glass-card p-6">
            <p class="text-sm font-medium text-slate-500">On-Time Rate</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-2">94.8%</h3>
            <div class="mt-4 flex items-center text-xs text-green-600">
                 <span class="bg-green-100 p-0.5 rounded mr-1">↑ 2.1%</span> top performing
            </div>
        </div>
        <div class="glass-card p-6">
            <p class="text-sm font-medium text-slate-500">Issues Reported</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-2">3</h3>
            <div class="mt-4 flex items-center text-xs text-red-600">
                 <span class="bg-red-100 p-0.5 rounded mr-1">!</span> Urgent attention
            </div>
        </div>
    </div>

    <!-- Map/Main View Placeholder -->
    <div class="glass-card h-96 flex items-center justify-center overflow-hidden relative">
        <div class="absolute inset-0 bg-slate-100 animate-pulse"></div>
        <div class="z-10 text-center">
             <div class="w-16 h-16 bg-nestle-blue/10 rounded-full flex items-center justify-center mx-auto mb-4">
                 <svg class="w-8 h-8 text-nestle-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
             </div>
             <p class="text-slate-600 font-medium">Interactive Logistics Map Loading...</p>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include_once BASE_PATH . '/app/Views/layouts/main.php';
?>
