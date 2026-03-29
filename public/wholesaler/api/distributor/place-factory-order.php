<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

checkAuth(['distributor']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $distributor_id = $_SESSION['user_id'];
    $items = $_POST['items'] ?? [];
    $total_amount = 0;
    
    // 1. Calculate and validate
    $valid_items = [];
    foreach ($items as $p_id => $item) {
        $qty = (int)$item['qty'];
        if ($qty > 0) {
            $price = (float)$item['price'];
            $total_amount += ($qty * $price);
            $valid_items[] = [
                'p_id' => $p_id,
                'qty' => $qty,
                'price' => $price,
                'subtotal' => ($qty * $price)
            ];
        }
    }

    if (empty($valid_items)) {
        header('Location: ' . BASE_URL . 'distributor/factory-order.php?error=No items selected');
        exit();
    }

    try {
        $pdo->beginTransaction();

        $order_number = 'FC-' . strtoupper(dechex(time())) . '-' . rand(100, 999);
        
        // 2. Insert order
        $stmt = $pdo->prepare("INSERT INTO factory_orders (order_number, distributor_id, total_amount) VALUES (?, ?, ?)");
        $stmt->execute([$order_number, $distributor_id, $total_amount]);
        $order_id = $pdo->lastInsertId();

        // 3. Insert items
        $item_stmt = $pdo->prepare("INSERT INTO factory_order_items (factory_order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
        foreach ($valid_items as $vi) {
            $item_stmt->execute([$order_id, $vi['p_id'], $vi['qty'], $vi['price'], $vi['subtotal']]);
        }

        // 4. Notify Nestlé Admin (id 1 usually)
        $nestle_notif = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (1, 'system', 'New Factory Order', 'Replenishment order # ? submitted by Distributor.')");
        $nestle_notif->execute([$order_number]);

        $pdo->commit();
        header('Location: ' . BASE_URL . 'distributor/dashboard.php?success=Order submitted to Nestlé');
    } catch (Exception $e) {
        $pdo->rollBack();
        header('Location: ' . BASE_URL . 'distributor/factory-order.php?error=' . urlencode($e->getMessage()));
    }
} else {
    header('Location: ' . BASE_URL . 'distributor/factory-order.php');
}
