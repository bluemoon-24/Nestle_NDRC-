<?php
// api/distributor/place-factory-order.php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'distributor') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $distributor_id = $_SESSION['user_id'];
    $items = $_POST['items'] ?? []; // Array of product_id => quantity
    $order_number = "FO-" . strtoupper(bin2hex(random_bytes(4)));

    if (empty($items)) {
        header('Location: ' . BASE_URL . 'distributor/factory-order.php?error=empty');
        exit();
    }

    try {
        $pdo->beginTransaction();

        $total_amount = 0;
        $order_items = [];

        foreach ($items as $product_id => $quantity) {
            if ($quantity <= 0) continue;

            $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();

            if (!$product) continue;

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
            die("No valid items");
        }

        $stmt = $pdo->prepare("INSERT INTO factory_orders (order_number, distributor_id, total_amount) VALUES (?, ?, ?)");
        $stmt->execute([$order_number, $distributor_id, $total_amount]);
        $factory_order_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO factory_order_items (factory_order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
        foreach ($order_items as $item) {
            $stmt->execute([$factory_order_id, $item['id'], $item['quantity'], $item['price'], $item['subtotal']]);
        }

        // Notify Nestle Admin
        $nestle_admins = $pdo->query("SELECT id FROM users WHERE role = 'nestle'")->fetchAll();
        $notif = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'order_status', 'New Factory Order', ?)");
        foreach ($nestle_admins as $admin) {
            $notif->execute([$admin['id'], "New restock request $order_number received from distributor " . $_SESSION['user_name']]);
        }

        $pdo->commit();
        header('Location: ' . BASE_URL . 'distributor/dashboard.php?order_placed=1');
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
