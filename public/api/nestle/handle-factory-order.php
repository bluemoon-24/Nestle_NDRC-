<?php
// api/nestle/handle-factory-order.php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'nestle') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $status = $_POST['status']; // 'accepted' or 'rejected'

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT order_number, distributor_id FROM factory_orders WHERE id = ? FOR UPDATE");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();
        
        if (!$order) {
            die("Invalid Order");
        }

        if ($status === 'accepted') {
            // Deduct stock
            $items = $pdo->prepare("SELECT product_id, quantity FROM factory_order_items WHERE factory_order_id = ?");
            $items->execute([$order_id]);
            $order_items = $items->fetchAll();

            foreach ($order_items as $item) {
                $stock = $pdo->prepare("UPDATE warehouse_stock SET total_stock = total_stock - ? WHERE product_id = ? AND total_stock >= ?");
                $stock->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
                
                if ($stock->rowCount() === 0) {
                    throw new Exception("Insufficient warehouse stock for item: " . $item['product_id']);
                }
            }
        }

        $stmt = $pdo->prepare("UPDATE factory_orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);

        // Notify Distributor
        $title = $status === 'accepted' ? 'Factory Order Accepted' : 'Factory Order Rejected';
        $message = $status === 'accepted' ? "Your replenishment order " . $order['order_number'] . " has been accepted." : "Your order " . $order['order_number'] . " was declined.";
        
        $notif = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'order_status', ?, ?)");
        $notif->execute([$order['distributor_id'], $title, $message]);

        $pdo->commit();
        header('Location: ' . BASE_URL . 'nestle/warehouse.php?success=1');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
