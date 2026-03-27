<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL);
    exit();
}

include 'includes/header.php';
?>

<div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-nestle-brown underline decoration-nestle-blue decoration-4 underline-offset-8">Sign in to NDRC</h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Or
            <a href="<?php echo BASE_URL; ?>register.php" class="font-semibold leading-6 text-nestle-blue hover:text-nestle-blue/80">create a new account</a>
        </p>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
        <div class="bg-white px-6 py-12 shadow sm:rounded-xl sm:px-12 border border-gray-100">
            <?php if (isset($_GET['error'])): ?>
                <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm">
                    Invalid email or password.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['registered'])): ?>
                <div class="mb-4 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg text-sm">
                    Registration successful! Please sign in.
                </div>
            <?php endif; ?>

            <form class="space-y-6" action="<?php echo BASE_URL; ?>api/auth/login.php" method="POST">
                <div>
                    <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email address</label>
                    <div class="mt-2">
                        <input id="email" name="email" type="email" autocomplete="email" required class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-nestle-blue sm:text-sm sm:leading-6 px-4">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
                    </div>
                    <div class="mt-2">
                        <input id="password" name="password" type="password" autocomplete="current-password" required class="block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-nestle-blue sm:text-sm sm:leading-6 px-4">
                    </div>
                </div>

                <div>
                    <button type="submit" class="flex w-full justify-center rounded-lg bg-nestle-brown px-3 py-3 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-nestle-brown/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-nestle-brown transition-all">Sign in</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
