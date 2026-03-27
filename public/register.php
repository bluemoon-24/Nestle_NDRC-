<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL);
    exit();
}

// Fetch wholesalers for retailer registration
$stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'wholesaler' AND status = 'active'");
$wholesalers = $stmt->fetchAll();

// Fetch pre-registered distributors
$stmt = $pdo->query("SELECT id, name, territory FROM users WHERE role = 'distributor' AND status = 'active'");
$distributors = $stmt->fetchAll();
?>

<style>
.role-card.active {
    border-color: #0082c3 !important;
    background-color: #f0f9ff !important;
    box-shadow: 0 0 0 2px #0082c3 !important;
}
</style>

<?php include 'includes/header.php'; ?>

<div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-nestle-brown underline decoration-nestle-blue decoration-4 underline-offset-8">Create NDRC Account</h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Already have an account?
            <a href="<?php echo BASE_URL; ?>login.php" class="font-semibold leading-6 text-nestle-blue hover:text-nestle-blue/80">Sign in</a>
        </p>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[550px]">
        <div class="bg-white px-6 py-12 shadow sm:rounded-xl sm:px-12 border border-gray-100">
            <form id="registerForm" class="space-y-6" action="<?php echo BASE_URL; ?>api/auth/register.php" method="POST">
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
                        <label for="address" class="block text-sm font-medium text-gray-900">Physical Store / Business Address</label>
                        <div class="mt-2">
                            <textarea id="address" name="address" rows="2" required placeholder="e.g. 123, Galle Road, Colombo 03" class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-nestle-blue px-4"></textarea>
                        </div>
                        <p class="mt-2 text-[10px] text-gray-500 italic">This address will be used for route optimization and delivery planning.</p>
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
                            <label id="label-retailer" class="role-card relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-nestle-blue group transition-all" onclick="selectRole('retailer')">
                                <input type="radio" id="radio-retailer" name="role" value="retailer" required class="sr-only">
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900">Retailer</span>
                                        <span class="mt-1 flex items-center text-sm text-gray-500">I own a shop and want to place orders.</span>
                                    </span>
                                </span>
                                <span class="text-2xl group-hover:scale-110 transition-transform">🏪</span>
                            </label>

                            <label id="label-wholesaler" class="role-card relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-nestle-blue group transition-all" onclick="selectRole('wholesaler')">
                                <input type="radio" id="radio-wholesaler" name="role" value="wholesaler" class="sr-only">
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900">Wholesaler</span>
                                        <span class="mt-1 flex items-center text-sm text-gray-500">I supply to retailers and order from distributors.</span>
                                    </span>
                                </span>
                                <span class="text-2xl group-hover:scale-110 transition-transform">🏭</span>
                            </label>

                            <label id="label-distributor" class="role-card relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-nestle-blue group transition-all" onclick="selectRole('distributor')">
                                <input type="radio" id="radio-distributor" name="role" value="distributor" class="sr-only">
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
                                    <input id="direct_dist" name="order_type" type="radio" value="direct" onchange="toggleWholesaler(false)" required class="h-4 w-4 border-gray-300 text-nestle-blue focus:ring-nestle-blue">
                                    <label for="direct_dist" class="ml-3 block text-sm font-medium text-gray-700">Directly from Distributor (Large Supermarket)</label>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-900">Choose Your Primary Distributor</label>
                            <select name="distributor_id" required class="mt-2 block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-nestle-blue px-4">
                                <option value="">Select a Distributor</option>
                                <?php foreach ($distributors as $d): ?>
                                    <option value="<?php echo $d['id']; ?>"><?php echo h($d['name']); ?> (<?php echo h($d['territory']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <p class="mt-2 text-xs text-gray-500 italic">* This distributor will fulfill your direct or aggregated orders.</p>
                        </div>
                    </div>

                    <div id="wholesalerFields" class="hidden space-y-6">
                        <h3 class="text-lg font-bold text-nestle-brown">Wholesaler Details</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-900">Affiliated Nestle Distributor</label>
                            <select name="distributor_id" required class="mt-2 block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-nestle-blue px-4">
                                <option value="">Select your Distributor</option>
                                <?php foreach ($distributors as $d): ?>
                                    <option value="<?php echo $d['id']; ?>"><?php echo h($d['name']); ?> (<?php echo h($d['territory']); ?>)</option>
                                <?php endforeach; ?>
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
function selectRole(role) {
    selectedRole = role;
    document.getElementById('radio-' + role).checked = true;
    
    // UI Feedback
    document.querySelectorAll('.role-card').forEach(el => el.classList.remove('active'));
    document.getElementById('label-' + role).classList.add('active');
}

function goToStep2() {
    // Validate Step 1 first
    const step1 = document.getElementById('step1');
    const inputs = step1.querySelectorAll('input');
    let valid = true;
    inputs.forEach(i => {
        if (!i.reportValidity()) valid = false;
    });
    if (!valid) return;

    const roleInput = document.querySelector('input[name="role"]:checked');
    if (!roleInput) {
        alert('Please select your role');
        return;
    }
    
    const role = roleInput.value;
    document.getElementById('step1').classList.add('hidden');
    document.getElementById('step2').classList.remove('hidden');
    
    // Hide all role specific sections and remove 'required' to prevent validation blocks
    // Also disable inputs in hidden sections to avoid key collision in POST
    const sections = ['retailer', 'wholesaler', 'distributor'];
    sections.forEach(s => {
        const div = document.getElementById(s + 'Fields');
        div.classList.add('hidden');
        div.querySelectorAll('select, input').forEach(input => {
            input.removeAttribute('required');
            input.setAttribute('disabled', 'disabled');
        });
    });
    
    // Show selected and add 'required' back
    const activeDiv = document.getElementById(role + 'Fields');
    activeDiv.classList.remove('hidden');
    activeDiv.querySelectorAll('select, input').forEach(input => {
        input.setAttribute('required', '');
        input.removeAttribute('disabled');
    });
    
    // Special handling for wholesaler select if retailer
    if (role === 'retailer') {
        toggleWholesaler(document.getElementById('via_wholesaler').checked);
    }
}

function goToStep1() {
    document.getElementById('step2').classList.add('hidden');
    document.getElementById('step1').classList.remove('hidden');
}

function toggleWholesaler(show) {
    const div = document.getElementById('wholesalerSelectDiv');
    const select = div.querySelector('select');
    div.classList.toggle('hidden', !show);
    if (show) select.setAttribute('required', '');
    else select.removeAttribute('required');
}
</script>

<?php include 'includes/footer.php'; ?>
