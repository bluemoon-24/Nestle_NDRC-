<?php
// includes/auth_check.php
session_start();

function checkAuth($allowed_roles = []) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit();
    }

    if (!empty($allowed_roles) && !in_repeat($_SESSION['user_role'], $allowed_roles)) {
        // Redirect to their respective dashboard if they try to access unauthorized area
        switch ($_SESSION['user_role']) {
            case 'retailer':
                header('Location: /retailer/dashboard.php');
                break;
            case 'wholesaler':
                header('Location: /wholesaler/dashboard.php');
                break;
            case 'distributor':
                header('Location: /distributor/dashboard.php');
                break;
            case 'nestle':
                header('Location: /nestle/dashboard.php');
                break;
            default:
                header('Location: /login.php');
                break;
        }
        exit();
    }
}

// Helper to check if role is in array (since in_array is standard but I'll use it correctly)
function in_repeat($needle, $haystack) {
    return in_array($needle, $haystack);
}
