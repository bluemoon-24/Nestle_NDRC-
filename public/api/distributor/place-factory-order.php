<?php
// api/distributor/place-factory-order.php
require_once '../../config/database.php';
require_once '../../../app/Utils/helper.php';
session_start();

authorize('distributor');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $distributor_id = $_SESSION['user_id'];
    $items = $_POST['items'] ?? []; // Array of product_id => quantity
    $order_number = "FO-" . strtoupper(bin2hex(random_bytes(4)));

    if (empty($items)) {
        jsonResponse('error', 'Order cannot be empty', null, 400);
    }

    try {
        $pdo->beginTransaction();

        $total_amount = 0;
        $order_items = [];

        foreach ($items as $product_id => $quantity) {
            if ($quantity <= 0) continue;

            // Check if product exists and get price
            $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();

            if (!$product) {
                 throw new Exception("Product ID $product_id not found.");
            }

            // Check availability and reserve stock
            // available_stock = total_stock - reserved_stock
            $stockCheck = $pdo->prepare("UPDATE warehouse_stock SET reserved_stock = reserved_stock + ? WHERE product_id = ? AND (total_stock - reserved_stock) >= ?");
            $stockCheck->execute([$quantity, $product_id, $quantity]);

            if ($stockCheck->rowCount() === 0) {
                // Check if it failed because product not in warehouse_stock or insufficient stock
                $check = $pdo->prepare("SELECT total_stock, reserved_stock FROM warehouse_stock WHERE product_id = ?");
                $check->execute([$product_id]);
                $s = $check->fetch();
                if (!$s) {
                    throw new Exception("Product $product_id is not available in warehouse stock.");
                } else {
                    $available = $s['total_stock'] - $s['reserved_stock'];
                    throw new Exception("Insufficient stock for Product $product_id. Available: $available, Requested: $quantity");
                }
            }

            $subtotal = $product['price'] * $quantity;
            $total_amount += $subtotal;
            $order_items[] = [
                'id' => $product_id,
                'quantity' => $quantity,
                'price' => $product['price'],
                'subtotal' => $subtotal
            ];
        }

        if (empty($order_items)) {
            throw new Exception("No valid items in order.");
        }

        // Create Order
        $stmt = $pdo->prepare("INSERT INTO factory_orders (order_number, distributor_id, total_amount, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$order_number, $distributor_id, $total_amount]);
        $factory_order_id = $pdo->lastInsertId();

        // Create Order Items
        $stmt = $pdo->prepare("INSERT INTO factory_order_items (factory_order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
        foreach ($order_items as $item) {
            $stmt->execute([$factory_order_id, $item['id'], $item['quantity'], $item['price'], $item['subtotal']]);
        }

        // Notify Nestle Admin
        $nestle_admins = $pdo->query("SELECT id FROM users WHERE role = 'nestle'")->fetchAll();
        $notif = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'order_status', 'New Factory Order', ?)");
        foreach ($nestle_admins as $admin) {
            $notif->execute([$admin['id'], "New restock request $order_number received from distributor " . ($_SESSION['user_name'] ?? 'Distributor')]);
        }

        $pdo->commit();
        jsonResponse('success', 'Factory order placed and stock reserved successfully', ['order_number' => $order_number]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        jsonResponse('error', $e->getMessage(), null, 400);
    }
}
