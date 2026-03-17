<?php
// api/wholesaler/submit-to-distributor.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $order_id = $_POST['order_id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'distributor_pending' WHERE id = ?");
        $stmt->execute([$order_id]);
        
        // Notify distributor
        $order = $pdo->prepare("SELECT distributor_id, order_number FROM orders WHERE id = ?");
        $order->execute([$order_id]);
        $o = $order->fetch();
        
        createNotification($o['distributor_id'], 'order_status', 'New Wholesaler Submission', "Wholesaler " . $_SESSION['user_name'] . " submitted order " . $o['order_number'], "/distributor/dashboard.php");
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    } catch (Exception $e) {
        die("Submission failed: " . $e->getMessage());
    }
}
