<?php
// includes/auth_check.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure BASE_URL is available
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/database.php';
}

function checkAuth($allowed_roles = []) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }

    if (!empty($allowed_roles) && !in_array($_SESSION['user_role'], $allowed_roles)) {
        // Redirect to their respective dashboard if they try to access unauthorized area
        switch ($_SESSION['user_role']) {
            case 'retailer':
                header('Location: ' . BASE_URL . 'retailer/dashboard.php');
                break;
            case 'wholesaler':
                header('Location: ' . BASE_URL . 'wholesaler/dashboard.php');
                break;
            case 'distributor':
                header('Location: ' . BASE_URL . 'distributor/dashboard.php');
                break;
            case 'nestle':
                header('Location: ' . BASE_URL . 'nestle/dashboard.php');
                break;
            default:
                header('Location: ' . BASE_URL . 'login.php');
                break;
        }
        exit();
    }
}
