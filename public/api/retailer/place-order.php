<?php
// api/retailer/place-order.php
require_once '../../config/database.php';
require_once '../../../app/Utils/helper.php';
session_start();

authorize(['retailer', 'wholesaler']); // Both can place orders up the chain

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $retailer_id = $_SESSION['user_id'];
    $wholesaler_id = !empty($_POST['wholesaler_id']) ? $_POST['wholesaler_id'] : null;
    $distributor_id = $_POST['distributor_id'];
    $items_post = $_POST['items'] ?? [];
    $order_number = "ORD-" . date('Ymd') . "-" . strtoupper(bin2hex(random_bytes(3)));

    if (empty($distributor_id)) {
        jsonResponse('error', 'Target distributor must be specified', null, 400);
    }

    try {
        $pdo->beginTransaction();

        $total_amount = 0;
        $order_items = [];

        foreach ($items_post as $product_id => $data) {
            $qty = (int)($data['quantity'] ?? 0);
            if ($qty <= 0) continue;

            $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();

            if ($product) {
                $subtotal = $product['price'] * $qty;
                $total_amount += $subtotal;
                $order_items[] = [
                    'product_id' => $product_id,
                    'qty' => $qty,
                    'price' => $product['price'],
                    'subtotal' => $subtotal
                ];
            } else {
                 throw new Exception("Product ID $product_id not found.");
            }
        }

        if (empty($order_items)) {
            throw new Exception("No valid items selected for the order.");
        }

        // Determine status based on chain
        $status = $wholesaler_id ? 'wholesaler_pending' : 'distributor_pending';

        $stmt = $pdo->prepare("
            INSERT INTO orders (order_number, retailer_id, wholesaler_id, distributor_id, status, order_date, scheduled_dispatch_date, total_amount)
            VALUES (?, ?, ?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 2 DAY), ?)
        ");
        $stmt->execute([$order_number, $retailer_id, $wholesaler_id, $distributor_id, $status, $total_amount]);
        $order_id = $pdo->lastInsertId();

        $item_stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($order_items as $item) {
            $item_stmt->execute([$order_id, $item['product_id'], $item['qty'], $item['price'], $item['subtotal']]);
        }

        // Notify next in line
        $notify_target = $wholesaler_id ? $wholesaler_id : $distributor_id;
        $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'order_status', 'New Order Received', ?)");
        $notif_stmt->execute([$notify_target, "New order $order_number has been placed by " . ($_SESSION['user_name'] ?? 'User')]);

        $pdo->commit();
        jsonResponse('success', 'Order placed successfully', ['order_number' => $order_number, 'status' => $status]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        jsonResponse('error', $e->getMessage(), null, 400);
    }
}
