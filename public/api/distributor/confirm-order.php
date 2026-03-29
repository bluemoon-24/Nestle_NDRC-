<?php
// api/distributor/confirm-order.php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'distributor') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];

    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'distributor_confirmed' WHERE id = ? AND distributor_id = ?");
        $stmt->execute([$order_id, $_SESSION['user_id']]);

        // Notify retailer and wholesaler
        $order_stmt = $pdo->prepare("SELECT order_number, retailer_id, wholesaler_id FROM orders WHERE id = ?");
        $order_stmt->execute([$order_id]);
        $order = $order_stmt->fetch();

        if ($order) {
            $title = 'Order Confirmed';
            $msg = "Distributor " . $_SESSION['user_name'] . " has confirmed order #{$order['order_number']} for delivery.";
            
            $notif = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'order_status', ?, ?)");
            $notif->execute([$order['retailer_id'], $title, $msg]);
            
            if ($order['wholesaler_id']) {
                $notif->execute([$order['wholesaler_id'], $title, $msg]);
            }
        }

        header('Location: ' . BASE_URL . 'distributor/dashboard.php?success=1');
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
