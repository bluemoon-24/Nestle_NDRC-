<?php
// includes/functions.php

/**
 * Create a notification for a user
 */
function createNotification($user_id, $type, $title, $message, $link = null) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, type, title, message, link)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    return $stmt->execute([$user_id, $type, $title, $message, $link]);
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return 'Rs ' . number_format($amount, 2);
}

/**
 * Sanitize output
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Get status badge classes
 */
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'placed':
        case 'wholesaler_pending':
        case 'distributor_pending':
            return 'bg-orange-100 text-orange-800';
        case 'wholesaler_accepted':
        case 'distributor_confirmed':
            return 'bg-blue-100 text-blue-800';
        case 'dispatched':
            return 'bg-purple-100 text-purple-800';
        case 'delivered':
            return 'bg-green-100 text-green-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}
