<?php
// api/wholesaler/process-order.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $order_id = $_POST['order_id'];
    $action = $_POST['action']; // accept or reject
    
    $status = ($action === 'accept') ? 'wholesaler_accepted' : 'rejected';
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        
        // Notify retailer
        $order = $pdo->prepare("SELECT retailer_id, order_number FROM orders WHERE id = ?");
        $order->execute([$order_id]);
        $o = $order->fetch();
        
        $msg = ($action === 'accept') ? "Your order " . $o['order_number'] . " has been accepted by the wholesaler." : "Your order " . $o['order_number'] . " was rejected.";
        createNotification($o['retailer_id'], 'order_status', "Order " . ucfirst($action) . "ed", $msg, "/retailer/dashboard.php");
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    } catch (Exception $e) {
        die("Process failed: " . $e->getMessage());
    }
}
