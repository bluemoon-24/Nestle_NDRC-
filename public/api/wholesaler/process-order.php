<?php
// api/wholesaler/process-order.php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'wholesaler') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $action = $_POST['action']; // 'accept' or 'reject'

    try {
        $status = ($action === 'accept') ? 'wholesaler_accepted' : 'rejected';
        
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ? AND wholesaler_id = ?");
        $stmt->execute([$status, $order_id, $_SESSION['user_id']]);

        // Notify retailer
        $order_stmt = $pdo->prepare("SELECT order_number, retailer_id FROM orders WHERE id = ?");
        $order_stmt->execute([$order_id]);
        $order = $order_stmt->fetch();

        if ($order) {
            $title = ($action === 'accept') ? 'Order Accepted' : 'Order Rejected';
            $msg = ($action === 'accept') ? "Wholesaler has accepted your order #{$order['order_number']}" : "Your order #{$order['order_number']} was rejected.";
            
            $notif = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'order_status', ?, ?)");
            $notif->execute([$order['retailer_id'], $title, $msg]);
        }

        header('Location: ' . BASE_URL . 'wholesaler/dashboard.php?success=1');
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
