<?php
// api/distributor/assign-driver.php
require_once '../../config/database.php';
require_once '../../../app/Utils/helper.php';
session_start();

authorize('distributor');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id']; // Internal Order ID (INT)
    $driver_id = $_POST['driver_id'];

    if (!$order_id || !$driver_id) {
        jsonResponse('error', 'Order ID and Driver ID are required', null, 400);
    }

    try {
        $pdo->beginTransaction();

        // 1. Verify the order belongs to this distributor and is in a correct state
        $stmt = $pdo->prepare("SELECT order_number, status, retailer_id FROM orders WHERE id = ? AND distributor_id = ?");
        $stmt->execute([$order_id, $_SESSION['user_id']]);
        $order = $stmt->fetch();

        if (!$order) {
            throw new Exception("Order not found or access denied.");
        }

        // 2. Check driver availability
        $stmt = $pdo->prepare("SELECT status FROM drivers WHERE id = ? AND distributor_id = ?");
        $stmt->execute([$driver_id, $_SESSION['user_id']]);
        $driver = $stmt->fetch();

        if (!$driver || $driver['status'] !== 'idle') {
            throw new Exception("Driver is not available.");
        }

        // 3. Upsert into deliveries table
        $stmt = $pdo->prepare("
            INSERT INTO deliveries (order_id, driver_id, status)
            VALUES (?, ?, 'pending')
            ON DUPLICATE KEY UPDATE driver_id = ?, status = 'pending'
        ");
        $stmt->execute([$order_id, $driver_id, $driver_id]);

        // 4. Update order status to 'dispatched'
        $stmt = $pdo->prepare("UPDATE orders SET status = 'dispatched' WHERE id = ?");
        $stmt->execute([$order_id]);

        // 5. Update driver status to 'active'
        $stmt = $pdo->prepare("UPDATE drivers SET status = 'active' WHERE id = ?");
        $stmt->execute([$driver_id]);

        // 6. Notify Retailer
        $notif = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'order_status', 'Order Dispatched', ?)");
        $notif->execute([$order['retailer_id'], "Your order " . $order['order_number'] . " has been dispatched."]);

        $pdo->commit();
        jsonResponse('success', 'Driver assigned and order dispatched successfully');

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        jsonResponse('error', $e->getMessage(), null, 400);
    }
}
