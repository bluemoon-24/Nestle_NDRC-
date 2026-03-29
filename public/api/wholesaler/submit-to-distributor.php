<?php
// api/wholesaler/submit-to-distributor.php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'wholesaler') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];

    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'distributor_pending' WHERE id = ? AND wholesaler_id = ?");
        $stmt->execute([$order_id, $_SESSION['user_id']]);

        // Notify distributor
        $order_stmt = $pdo->prepare("SELECT order_number, distributor_id FROM orders WHERE id = ?");
        $order_stmt->execute([$order_id]);
        $order = $order_stmt->fetch();

        if ($order) {
            $notif = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'order_status', 'New Wholesaler Order', ?)");
            $notif->execute([$order['distributor_id'], "Wholesaler " . $_SESSION['user_name'] . " has submitted order #{$order['order_number']} for confirmation."]);
        }

        header('Location: ' . BASE_URL . 'wholesaler/dashboard.php?success=1');
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
