<?php
// api/retailer/join-network.php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'retailer') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $retailer_id = $_SESSION['user_id'];
    $wholesaler_id = $_POST['wholesaler_id'];
    $message = $_POST['message'] ?? '';

    try {
        // Check for existing request
        $check = $pdo->prepare("SELECT id FROM network_requests WHERE retailer_id = ? AND wholesaler_id = ? AND status = 'pending'");
        $check->execute([$retailer_id, $wholesaler_id]);
        
        if ($check->fetch()) {
            die("Request already pending.");
        }

        $stmt = $pdo->prepare("INSERT INTO network_requests (retailer_id, wholesaler_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$retailer_id, $wholesaler_id, $message]);

        // Add system notification for wholesaler
        $notif = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'system', 'New Network Request', ?)");
        $retailer_name = $_SESSION['user_name'];
        $notif->execute([$wholesaler_id, "$retailer_name wants to join your retail network."]);

        header('Location: ' . BASE_URL . 'retailer/dashboard.php?request_sent=1');
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
