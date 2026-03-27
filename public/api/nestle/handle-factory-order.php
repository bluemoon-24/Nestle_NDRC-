<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

checkAuth(['nestle']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? null;
    $action = $_POST['action'] ?? null; // 'accept' or 'reject'

    if (!$order_id || !$action) {
        header('Location: ' . BASE_URL . 'nestle/warehouse.php?error=Missing params');
        exit();
    }

    try {
        $pdo->beginTransaction();

        // 1. Fetch order details
        $stmt = $pdo->prepare("SELECT * FROM factory_orders WHERE id = ? AND status = 'pending'");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();

        if (!$order) {
            throw new Exception("Order not found or not pending");
        }

        if ($action === 'accept') {
            // 2. Fetch items to check and reduce stock
            $items_stmt = $pdo->prepare("SELECT product_id, quantity FROM factory_order_items WHERE factory_order_id = ?");
            $items_stmt->execute([$order_id]);
            $items = $items_stmt->fetchAll();

            foreach ($items as $item) {
                // Check stock
                $stock_stmt = $pdo->prepare("SELECT available_stock, total_stock FROM warehouse_stock WHERE product_id = ? FOR UPDATE");
                $stock_stmt->execute([$item['product_id']]);
                $stock = $stock_stmt->fetch();

                if (!$stock || $stock['available_stock'] < $item['quantity']) {
                    throw new Exception("Insufficient stock for product id " . $item['product_id']);
                }

                // Reduce total stock (or reserved if implemented, but here let's just reduce total for a simple factory flow)
                $upd_stock = $pdo->prepare("UPDATE warehouse_stock SET total_stock = total_stock - ?, last_updated = CURRENT_TIMESTAMP WHERE product_id = ?");
                $upd_stock->execute([$item['quantity'], $item['product_id']]);

                // 3. Check for reorder point (Notification)
                $new_stock = $stock['total_stock'] - $item['quantity'];
                $check_reorder = $pdo->prepare("SELECT reorder_point FROM warehouse_stock WHERE product_id = ?");
                $check_reorder->execute([$item['product_id']]);
                $reorder_point = $check_reorder->fetchColumn();

                if ($new_stock <= $reorder_point) {
                    $notif = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'stock_alert', 'Low Stock Level', 'Product ID # ? is below reorder point.')");
                    $notif->execute([$_SESSION['user_id'], $item['product_id']]);
                }
            }

            // Update order status
            $upd_order = $pdo->prepare("UPDATE factory_orders SET status = 'accepted' WHERE id = ?");
            $upd_order->execute([$order_id]);

            // Notify Distributor
            $notif_dist = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'system', 'Stock Replenishment Accepted', 'Nestlé has accepted your factory order # ?')");
            $notif_dist->execute([$order['distributor_id'], $order['order_number']]);

        } elseif ($action === 'reject') {
            $upd_order = $pdo->prepare("UPDATE factory_orders SET status = 'rejected' WHERE id = ?");
            $upd_order->execute([$order_id]);

            // Notify Distributor
            $notif_dist = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'system', 'Order Rejected', 'Your factory order # ? was rejected by Nestlé.')");
            $notif_dist->execute([$order['distributor_id'], $order['order_number']]);
        }

        $pdo->commit();
        header('Location: ' . BASE_URL . 'nestle/warehouse.php?success=Order processed');
    } catch (Exception $e) {
        $pdo->rollBack();
        header('Location: ' . BASE_URL . 'nestle/warehouse.php?error=' . urlencode($e->getMessage()));
    }
} else {
    header('Location: ' . BASE_URL . 'nestle/warehouse.php');
}
