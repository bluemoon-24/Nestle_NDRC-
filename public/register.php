<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header('Location: /');
    exit();
}

// Fetch wholesalers for retailer registration
$stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'wholesaler' AND status = 'active'");
$wholesalers = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-nestle-brown underline decoration-nestle-blue decoration-4 underline-offset-8">Create NDRC Account</h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Already have an account?
            <a href="/login.php" class="font-semibold leading-6 text-nestle-blue hover:text-nestle-blue/80">Sign in</a>
        </p>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[550px]">
        <div class="bg-white px-6 py-12 shadow sm:rounded-xl sm:px-12 border border-gray-100">
            <form id="registerForm" class="space-y-6" action="/api/auth/register.php" method="POST">
                <!-- Step 1: Basic Info -->
                <div id="step1" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-900">Full Name / Business Name</label>
                        <div class="mt-2">
                            <input id="name" name="name" type="text" required class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-nestle-blue px-4">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-900">Email address</label>
                            <div class="mt-2">
                                <input id="email" name="email" type="email" required class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-nestle-blue px-4">
                            </div>
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-900">Phone Number</label>
                            <div class="mt-2">
                                <input id="phone" name="phone" type="tel" required class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-nestle-blue px-4">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-900">Password</label>
                        <div class="mt-2">
                            <input id="password" name="password" type="password" required class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-nestle-blue px-4">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-4">Select Your Role</label>
                        <div class="grid grid-cols-1 gap-3">
                            <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-nestle-blue group transition-all" onclick="showRoleFields('retailer')">
                                <input type="radio" name="role" value="retailer" required class="sr-only">
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900">Retailer</span>
                                        <span class="mt-1 flex items-center text-sm text-gray-500">I own a shop and want to place orders.</span>
                                    </span>
                                </span>
                                <span class="text-2xl group-hover:scale-110 transition-transform">🏪</span>
                            </label>

                            <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-nestle-blue group transition-all" onclick="showRoleFields('wholesaler')">
                                <input type="radio" name="role" value="wholesaler" class="sr-only">
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900">Wholesaler</span>
                                        <span class="mt-1 flex items-center text-sm text-gray-500">I supply to retailers and order from distributors.</span>
                                    </span>
                                </span>
                                <span class="text-2xl group-hover:scale-110 transition-transform">🏭</span>
                            </label>

                            <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-nestle-blue group transition-all" onclick="showRoleFields('distributor')">
                                <input type="radio" name="role" value="distributor" class="sr-only">
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900">Distributor</span>
                                        <span class="mt-1 flex items-center text-sm text-gray-500">I supply directly to wholesalers and large retailers.</span>
                                    </span>
                                </span>
                                <span class="text-2xl group-hover:scale-110 transition-transform">🚚</span>
                            </label>
                        </div>
                    </div>

                    <button type="button" onclick="goToStep2()" class="flex w-full justify-center rounded-lg bg-nestle-brown px-3 py-3 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-nestle-brown/90 transition-all">Continue to Details →</button>
                </div>

                <!-- Step 2: Role-Specific Fields -->
                <div id="step2" class="hidden space-y-6">
                    <button type="button" onclick="goToStep1()" class="text-sm font-semibold text-nestle-blue hover:text-nestle-blue/80 flex items-center">
                        <span class="mr-1">←</span> Back to Basic Info
                    </button>

                    <div id="retailerFields" class="hidden space-y-6">
                        <h3 class="text-lg font-bold text-nestle-brown">Retailer Context</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-900">How do you place orders?</label>
                            <div class="mt-4 space-y-4">
                                <div class="flex items-center">
                                    <input id="via_wholesaler" name="order_type" type="radio" value="wholesaler" onchange="toggleWholesaler(true)" class="h-4 w-4 border-gray-300 text-nestle-blue focus:ring-nestle-blue">
                                    <label for="via_wholesaler" class="ml-3 block text-sm font-medium text-gray-700">Via a Wholesaler (Small/Medium Shop)</label>
                                </div>
                                <div id="wholesalerSelectDiv" class="hidden ml-7 mt-2">
                                    <select name="wholesaler_id" class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-nestle-blue px-4">
                                        <option value="">Select your Wholesaler</option>
                                        <?php foreach ($wholesalers as $w): ?>
                                            <option value="<?php echo $w['id']; ?>"><?php echo h($w['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="flex items-center">
                                    <input id="direct_dist" name="order_type" type="radio" value="direct" onchange="toggleWholesaler(false)" class="h-4 w-4 border-gray-300 text-nestle-blue focus:ring-nestle-blue">
                                    <label for="direct_dist" class="ml-3 block text-sm font-medium text-gray-700">Directly from Distributor (Large Supermarket)</label>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-900">Region</label>
                            <select name="region" class="mt-2 block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-nestle-blue px-4">
                                <option>Western Province</option>
                                <option>Central Province</option>
                                <option>Southern Province</option>
                                <option>Northern Province</option>
                                <option>Eastern Province</option>
                            </select>
                        </div>
                    </div>

                    <div id="wholesalerFields" class="hidden space-y-6">
                        <h3 class="text-lg font-bold text-nestle-brown">Wholesaler Details</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-900">Primary Region of Coverage</label>
                            <select name="region" class="mt-2 block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-nestle-blue px-4">
                                <option>Western Province</option>
                                <option>Central Province</option>
                                <option>Southern Province</option>
                                <option>Northern Province</option>
                                <option>Eastern Province</option>
                            </select>
                        </div>
                    </div>

                    <div id="distributorFields" class="hidden space-y-6">
                        <h3 class="text-lg font-bold text-nestle-brown">Distributor Assignment</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-900">Assigned Territory</label>
                            <select name="territory" class="mt-2 block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-nestle-blue px-4">
                                <option>Western Province</option>
                                <option>Central Province</option>
                                <option>Southern Province</option>
                                <option>Northern Province</option>
                                <option>Eastern Province</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="flex w-full justify-center rounded-lg bg-nestle-brown px-3 py-3 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-nestle-brown/90 transition-all">Complete Registration</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let selectedRole = '';

function showRoleFields(role) {
    selectedRole = role;
    // Highlight selection (optional styling here)
}

function goToStep2() {
    const role = document.querySelector('input[name="role"]:checked');
    if (!role) {
        alert('Please select your role');
        return;
    }
    
    document.getElementById('step1').classList.add('hidden');
    document.getElementById('step2').classList.remove('hidden');
    
    // Hide all role specific sections
    document.getElementById('retailerFields').classList.add('hidden');
    document.getElementById('wholesalerFields').classList.add('hidden');
    document.getElementById('distributorFields').classList.add('hidden');
    
    // Show selected one
    if (role.value === 'retailer') document.getElementById('retailerFields').classList.remove('hidden');
    else if (role.value === 'wholesaler') document.getElementById('wholesalerFields').classList.remove('hidden');
    else if (role.value === 'distributor') document.getElementById('distributorFields').classList.remove('hidden');
}

function goToStep1() {
    document.getElementById('step2').classList.add('hidden');
    document.getElementById('step1').classList.remove('hidden');
}

function toggleWholesaler(show) {
    document.getElementById('wholesalerSelectDiv').classList.toggle('hidden', !show);
}
</script>

<?php include 'includes/footer.php'; ?>
