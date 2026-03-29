<?php
// api/retailer/place-order.php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $retailer_id = $_SESSION['user_id'];
    $wholesaler_id = !empty($_POST['wholesaler_id']) ? $_POST['wholesaler_id'] : null;
    $distributor_id = $_POST['distributor_id'];
    $items_post = $_POST['items'] ?? [];
    $order_number = "ORD-" . date('Ymd') . "-" . strtoupper(bin2hex(random_bytes(3)));

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
            }
        }

        if (empty($order_items)) {
            die("No items selected");
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
        $notif_stmt->execute([$notify_target, "New order $order_number has been placed by " . $_SESSION['user_name']]);

        $pdo->commit();
        
        $redirect = $_SESSION['user_role'] === 'retailer' ? 'retailer/dashboard.php' : 'wholesaler/dashboard.php';
        header('Location: ' . BASE_URL . $redirect . '?order_success=1');
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Order failed: " . $e->getMessage());
    }
}
