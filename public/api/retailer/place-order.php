<?php
// api/retailer/place-order.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $retailer_id = $_SESSION['user_id'];
    $wholesaler_id = !empty($_POST['wholesaler_id']) ? $_POST['wholesaler_id'] : null;
    $distributor_id = $_POST['distributor_id'];
    $notes = $_POST['notes'] ?? '';
    $items = $_POST['items'] ?? [];

    // Calculate total and filter items
    $order_items = [];
    $total_amount = 0;
    foreach ($items as $product_id => $data) {
        if ($data['quantity'] > 0) {
            $qty = intval($data['quantity']);
            $price = floatval($data['price']);
            $subtotal = $qty * $price;
            $total_amount += $subtotal;
            $order_items[] = [
                'product_id' => $product_id,
                'quantity' => $qty,
                'unit_price' => $price,
                'subtotal' => $subtotal
            ];
        }
    }

    if (empty($order_items)) {
        header('Location: /retailer/place-order.php?error=empty');
        exit();
    }

    // Determine initial status
    // Path A: Small Retailer → Wholesaler
    // Path B: Large Retailer → Distributor (Direct)
    $status = $wholesaler_id ? 'placed' : 'distributor_pending';

    $order_number = 'ORD-' . strtoupper(uniqid());
    $order_date = date('Y-m-d');
    $scheduled_dispatch = date('Y-m-d', strtotime('+3 days'));

    try {
        $pdo->beginTransaction();

        // Create order
        $stmt = $pdo->prepare("
            INSERT INTO orders (order_number, retailer_id, wholesaler_id, distributor_id, status, order_date, scheduled_dispatch_date, total_amount, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $order_number, $retailer_id, $wholesaler_id, $distributor_id, $status, $order_date, $scheduled_dispatch, $total_amount, $notes
        ]);
        $order_id = $pdo->lastInsertId();

        // Create order items
        $stmt_item = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal)
            VALUES (?, ?, ?, ?, ?)
        ");
        foreach ($order_items as $item) {
            $stmt_item->execute([
                $order_id, $item['product_id'], $item['quantity'], $item['unit_price'], $item['subtotal']
            ]);
        }

        // Notification
        if ($wholesaler_id) {
            createNotification($wholesaler_id, 'order_status', 'New Retailer Order', "Order $order_number received from " . $_SESSION['user_name'], "/wholesaler/orders.php");
        } else {
            createNotification($distributor_id, 'order_status', 'New Direct Order', "Order $order_number received from " . $_SESSION['user_name'], "/distributor/orders.php");
        }

        $pdo->commit();
        header('Location: /retailer/dashboard.php?success=1');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Order failed: " . $e->getMessage());
    }
}
