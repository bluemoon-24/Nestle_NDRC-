<?php
// api/nestle/handle-factory-order.php
require_once '../../config/database.php';
require_once '../../../app/Utils/helper.php';
session_start();

authorize('nestle');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $status = $_POST['status']; // 'accepted' or 'rejected'

    if (!in_array($status, ['accepted', 'rejected'])) {
        jsonResponse('error', 'Invalid status', null, 400);
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT order_number, distributor_id, status FROM factory_orders WHERE id = ? FOR UPDATE");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();
        
        if (!$order) {
            throw new Exception("Order not found.");
        }

        if ($order['status'] !== 'pending') {
            throw new Exception("Order is already processed (Status: " . $order['status'] . ")");
        }

        // Get order items to process stock
        $items_stmt = $pdo->prepare("SELECT product_id, quantity FROM factory_order_items WHERE factory_order_id = ?");
        $items_stmt->execute([$order_id]);
        $order_items = $items_stmt->fetchAll();

        if ($status === 'accepted') {
            foreach ($order_items as $item) {
                // Deduct both total and reserved stock
                $stock = $pdo->prepare("UPDATE warehouse_stock SET total_stock = total_stock - ?, reserved_stock = reserved_stock - ? WHERE product_id = ? AND total_stock >= ? AND reserved_stock >= ?");
                $stock->execute([$item['quantity'], $item['quantity'], $item['product_id'], $item['quantity'], $item['quantity']]);
                
                if ($stock->rowCount() === 0) {
                    throw new Exception("Critical Error: Stock inconsistency for item: " . $item['product_id']);
                }
            }
        } else {
            // status === 'rejected'
            foreach ($order_items as $item) {
                // Release reserved stock back to available
                $stock = $pdo->prepare("UPDATE warehouse_stock SET reserved_stock = reserved_stock - ? WHERE product_id = ? AND reserved_stock >= ?");
                $stock->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
            }
        }

        // Update Order Status
        $stmt = $pdo->prepare("UPDATE factory_orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);

        // Notify Distributor
        $title = $status === 'accepted' ? 'Factory Order Accepted' : 'Factory Order Rejected';
        $message = $status === 'accepted' ? "Your replenishment order " . $order['order_number'] . " has been accepted." : "Your order " . $order['order_number'] . " was declined.";
        
        $notif = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'order_status', ?, ?)");
        $notif->execute([$order['distributor_id'], $title, $message]);

        $pdo->commit();
        jsonResponse('success', "Order $status successfully", ['order_number' => $order['order_number']]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        jsonResponse('error', $e->getMessage(), null, 400);
    }
}
