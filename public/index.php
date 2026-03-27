<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['user_role']) {
        case 'retailer': header('Location: ' . BASE_URL . 'retailer/dashboard.php'); break;
        case 'wholesaler': header('Location: ' . BASE_URL . 'wholesaler/dashboard.php'); break;
        case 'distributor': header('Location: ' . BASE_URL . 'distributor/dashboard.php'); break;
        case 'nestle': header('Location: ' . BASE_URL . 'nestle/dashboard.php'); break;
    }
    exit();
}

include 'includes/header.php';
?>

<!-- Premium Multi-Layer Hero Section -->
<div class="relative min-h-screen flex items-center overflow-hidden bg-[#FBF9F6]">
    <!-- Dynamic Glass Background -->
    <div class="absolute inset-0 z-0">
        <div class="absolute top-[-10%] right-[-10%] w-[60%] h-[60%] bg-nestle-blue/5 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[50%] h-[50%] bg-nestle-brown/5 rounded-full blur-[100px]"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 py-20">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <div class="space-y-10">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-gray-100 shadow-sm text-nestle-blue text-sm font-bold tracking-tight">
                    <span class="w-2 h-2 rounded-full bg-nestle-blue animate-pulse"></span>
                    NESTLÉ NDRC PLATFORM
                </div>
                
                <h1 class="text-6xl lg:text-8xl font-black text-[#1A1A1A] leading-[0.95] tracking-tighter">
                    Direct Retail <br/>
                    <span class="text-nestle-blue italic font-serif">Intelligence.</span>
                </h1>
                
                <p class="text-xl text-gray-600 leading-relaxed max-w-lg font-medium">
                    <span class="font-bold text-gray-900">NDRC</span> — Nestlé Direct Retail Channel. Bridging the gap with unparalleled real-time data insights into the last-mile retail ecosystem.
                </p>

                <div class="flex flex-wrap gap-5">
                    <a href="<?php echo BASE_URL; ?>login.php" class="bg-nestle-blue text-white px-12 py-5 rounded-[2rem] font-black text-xl shadow-2xl shadow-nestle-blue/30 hover:scale-[1.02] active:scale-95 transition-all group flex items-center">
                        Login
                        <svg class="ml-3 w-6 h-6 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </a>
                    <a href="<?php echo BASE_URL; ?>register.php" class="px-12 py-5 rounded-[2rem] font-bold text-xl text-gray-800 border-2 border-gray-100 hover:bg-white hover:border-nestle-blue/20 transition-all flex items-center">
                        Register Now
                    </a>
                </div>

                <div class="grid grid-cols-3 gap-8 pt-10 border-t border-gray-100">
                    <div>
                        <p class="text-3xl font-black text-nestle-blue">100%</p>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Transparency</p>
                    </div>
                    <div>
                        <p class="text-3xl font-black text-nestle-brown">Real-Time</p>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Order Tracking</p>
                    </div>
                    <div>
                        <p class="text-3xl font-black text-gray-800">Secure</p>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Direct Channel</p>
                    </div>
                </div>
            </div>

            <!-- Enhanced Visual Ecosystem - Retailer Sales Intelligence -->
            <div class="relative hidden lg:block">
                <div class="relative bg-white rounded-[4rem] p-6 shadow-[0_50px_100px_-20px_rgba(0,0,0,0.08)] border border-gray-100 overflow-hidden">
                    <!-- Glass Header -->
                    <div class="px-8 py-6 bg-gray-50/50 border-b border-gray-100 -mx-6 -mt-6 mb-8 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-nestle-blue flex items-center justify-center text-white text-xl">💡</div>
                            <span class="text-sm font-black text-gray-900 uppercase tracking-widest">Retailer Intelligence</span>
                        </div>
                        <div class="flex gap-1">
                            <div class="w-2 h-2 rounded-full bg-gray-200"></div>
                            <div class="w-2 h-2 rounded-full bg-gray-200"></div>
                            <div class="w-2 h-2 rounded-full bg-nestle-blue"></div>
                        </div>
                    </div>

                    <div class="space-y-10">
                        <!-- Simulated Analytics Dashboard -->
                        <div class="grid grid-cols-2 gap-6">
                            <div class="p-6 bg-[#F9FAFB] rounded-3xl border border-gray-50">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Market Demand</p>
                                <p class="text-3xl font-black text-gray-900">+24.8%</p>
                                <div class="mt-4 flex gap-1 h-12 items-end">
                                    <div class="flex-1 bg-nestle-blue/10 rounded-t-lg h-2/3"></div>
                                    <div class="flex-1 bg-nestle-blue/20 rounded-t-lg h-1/2"></div>
                                    <div class="flex-1 bg-nestle-blue/40 rounded-t-lg h-3/4"></div>
                                    <div class="flex-1 bg-nestle-blue rounded-t-lg h-full"></div>
                                </div>
                            </div>
                            <div class="p-6 bg-[#F9FAFB] rounded-3xl border border-gray-50">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Product Velocity</p>
                                <p class="text-3xl font-black text-nestle-brown">High</p>
                                <div class="mt-4 space-y-2">
                                    <div class="w-full h-2 bg-gray-200 rounded-full"><div class="w-[85%] h-full bg-nestle-brown rounded-full"></div></div>
                                    <div class="w-full h-2 bg-gray-200 rounded-full"><div class="w-[45%] h-full bg-nestle-brown/30 rounded-full"></div></div>
                                </div>
                            </div>
                        </div>

                        <!-- Active Intelligence Notification -->
                        <div class="p-8 bg-white rounded-3xl border border-gray-100 shadow-2xl shadow-nestle-blue/5 border-l-8 border-l-nestle-blue group">
                            <div class="flex items-center gap-6">
                                <div class="w-16 h-16 rounded-2xl bg-nestle-success/10 flex items-center justify-center text-nestle-success text-3xl group-hover:scale-110 transition-transform">✅</div>
                                <div class="flex-1">
                                    <p class="text-sm font-black text-gray-900 leading-tight uppercase tracking-tight">Supply Chain Optimized</p>
                                    <p class="text-sm text-gray-500 font-medium mt-1">Retailer network stock levels at 100% fulfillment capability.</p>
                                </div>
                            </div>
                            <div class="mt-6 pt-6 border-t border-gray-50 flex items-center justify-between">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Ref #NDRC-LOG-2026</span>
                                <span class="text-[10px] font-black text-nestle-blue uppercase tracking-widest flex items-center gap-1">Live Update <span class="w-2 h-2 rounded-full bg-nestle-blue animate-ping"></span></span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Decorative Elements -->
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-nestle-blue/10 rounded-full blur-2xl animate-pulse"></div>
                <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-nestle-brown/10 rounded-full blur-2xl animate-pulse delay-500"></div>
            </div>
        </div>
    </div>
</div>

<!-- Unified Platform Features -->
<section id="ecosystem" class="py-32 bg-white relative overflow-hidden">
    <!-- Sophisticated Background Pattern -->
    <div class="absolute inset-0 opacity-[0.03] pointer-events-none grayscale">
        <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
            <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                <path d="M 10 0 L 0 0 0 10" fill="none" stroke="currentColor" stroke-width="0.5"/>
            </pattern>
            <rect width="100" height="100" fill="url(#grid)"/>
        </svg>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-8 mb-20">
            <div class="max-w-2xl">
                <h2 class="text-nestle-blue font-bold tracking-[0.2em] text-sm uppercase mb-6 inline-block border-b-2 border-nestle-blue/20 pb-2">Integrated Platform</h2>
                <p class="text-5xl font-black text-gray-900 tracking-tight leading-tight">Everything you need <br/> to master the supply chain.</p>
            </div>
            <p class="text-xl text-gray-500 font-medium max-w-sm">
                From real-time order aggregation to advanced logistics optimization, the NDRC platform is built for growth.
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Strategic Insight Card -->
            <div class="group p-10 rounded-[3rem] border border-gray-100 bg-gray-50/30 hover:bg-white hover:shadow-[0_40px_80px_-15px_rgba(0,0,0,0.08)] transition-all duration-500">
                <div class="w-16 h-16 bg-nestle-blue rounded-3xl flex items-center justify-center text-white text-3xl shadow-lg shadow-nestle-blue/20 mb-10 group-hover:rotate-6 transition-transform">📊</div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4 tracking-tight">Real-Time Aggregation</h3>
                <p class="text-gray-500 leading-relaxed font-medium">Instantly view order totals across all distributors and wholesalers on a live, interactive command dashboard.</p>
                <div class="mt-8 flex items-center text-nestle-blue font-bold text-sm uppercase tracking-widest gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    Core Feature <span class="w-8 h-[2px] bg-nestle-blue"></span>
                </div>
            </div>

            <!-- Logistics Card -->
            <div class="group p-10 rounded-[3rem] border border-gray-100 bg-gray-50/30 hover:bg-white hover:shadow-[0_40px_80px_-15px_rgba(0,0,0,0.08)] transition-all duration-500">
                <div class="w-16 h-16 bg-nestle-brown rounded-3xl flex items-center justify-center text-white text-3xl shadow-lg shadow-nestle-brown/20 mb-10 group-hover:rotate-6 transition-transform">📦</div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4 tracking-tight">Intelligent Inventory</h3>
                <p class="text-gray-500 leading-relaxed font-medium">Automated reserved stock calculations based on confirmed orders to prevent over-promising and ensure 100% fulfillments.</p>
                <div class="mt-8 flex items-center text-nestle-brown font-bold text-sm uppercase tracking-widest gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    Stock Alerts <span class="w-8 h-[2px] bg-nestle-brown"></span>
                </div>
            </div>

            <!-- Network Strength Card -->
            <div class="group p-10 rounded-[3rem] border border-gray-100 bg-gray-50/30 hover:bg-white hover:shadow-[0_40px_80px_-15px_rgba(0,0,0,0.08)] transition-all duration-500">
                <div class="w-16 h-16 bg-gray-800 rounded-3xl flex items-center justify-center text-white text-3xl shadow-lg shadow-gray-800/20 mb-10 group-hover:rotate-6 transition-transform">🗺️</div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4 tracking-tight">Network Mapping</h3>
                <p class="text-gray-500 leading-relaxed font-medium">Visualize your entire retailer and wholesaler network. Monitor geographic coverage and territory demand instantly.</p>
                <div class="mt-8 flex items-center text-gray-800 font-bold text-sm uppercase tracking-widest gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    Territory Map <span class="w-8 h-[2px] bg-gray-800"></span>
                </div>
            </div>

            <!-- Highlight Large Feature -->
            <div class="lg:col-span-2 group p-12 rounded-[3.5rem] bg-nestle-blue text-white overflow-hidden relative shadow-2xl shadow-nestle-blue/20">
                <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="grid md:grid-cols-2 gap-12 items-center relative z-10">
                    <div>
                        <div class="w-16 h-16 bg-white rounded-3xl flex items-center justify-center text-nestle-blue text-3xl shadow-xl mb-8">💡</div>
                        <h3 class="text-3xl font-black mb-6 tracking-tight leading-tight">Advanced Analytics & <br/>Smart Recommendations</h3>
                        <p class="text-white/80 leading-relaxed font-medium text-lg">Use the power of data to predict market demand. Get AI-driven suggestions for stock replenishment based on historical sales intelligence.</p>
                    </div>
                    <div class="space-y-4">
                        <div class="p-4 bg-white/10 rounded-2xl border border-white/20 flex gap-4">
                            <span class="font-black text-2xl opacity-40">01</span>
                            <p class="font-bold">Market Demand Forecasting</p>
                        </div>
                        <div class="p-4 bg-white/10 rounded-2xl border border-white/20 flex gap-4">
                            <span class="font-black text-2xl opacity-40">02</span>
                            <p class="font-bold">Automated Reorder Alerts</p>
                        </div>
                        <div class="p-4 bg-white/10 rounded-2xl border border-white/20 flex gap-4">
                            <span class="font-black text-2xl opacity-40">03</span>
                            <p class="font-bold">Route & Delivery Priority Engine</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Alerts Card -->
            <div class="group p-10 rounded-[3rem] border border-gray-100 bg-[#FFF5EE] hover:bg-white hover:shadow-[0_40px_80px_-15px_rgba(0,0,0,0.08)] transition-all duration-500 flex flex-col justify-between">
                <div>
                    <div class="w-16 h-16 bg-nestle-success rounded-3xl flex items-center justify-center text-white text-3xl shadow-lg shadow-nestle-success/20 mb-10 group-hover:rotate-6 transition-transform">🔔</div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 tracking-tight">Active Notifications</h3>
                    <p class="text-gray-500 leading-relaxed font-medium">Never miss an order update or stock threshold breach with real-time push notifications.</p>
                </div>
                <a href="<?php echo BASE_URL; ?>register.php" class="mt-10 font-black text-nestle-brown hover:text-nestle-blue transition-colors flex items-center gap-2 group/link">
                   Register Now <svg class="w-5 h-5 group-hover/link:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Stats / CTA Section -->
<div class="py-24 bg-[#1A1A1A] overflow-hidden relative">
    <div class="absolute inset-0 z-0">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full bg-[radial-gradient(circle_at_center,_#2D2D2D_1px,_transparent_1px)] bg-[length:40px_40px] opacity-20"></div>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center max-w-3xl mx-auto space-y-10">
            <h2 class="text-white text-5xl lg:text-7xl font-black tracking-tight leading-tight">Ready to Digitalize <br/> Your Network?</h2>
            <p class="text-xl text-gray-400 font-medium">Join the thousands of retailers and distributors transforming Nestlé Lanka's distribution ecosystem.</p>
            <div class="flex flex-wrap justify-center gap-6 pt-4">
                <a href="<?php echo BASE_URL; ?>register.php" class="bg-nestle-blue text-white px-14 py-6 rounded-[2.5rem] font-black text-2xl shadow-2xl shadow-nestle-blue/20 hover:scale-105 active:scale-95 transition-all">Register Now</a>
            </div>
            <p class="text-xs font-bold text-gray-500 uppercase tracking-[0.5em]">Enterprise Grade Security Included</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
