<?php
/**
 * Nestle NDRC - Supply Chain Last Mile Visibility System
 * Main entry point and simple router.
 */

// Define project base path
define('BASE_PATH', dirname(__DIR__));

// Simple autoloader (usually handled by Composer)
spl_autoload_register(function ($class) {
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . $path . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// For an academic project MVP, we'll just require the dashboard for now
include_once BASE_PATH . '/app/Views/pages/dashboard.php';
