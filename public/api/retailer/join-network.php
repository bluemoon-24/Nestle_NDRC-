<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

checkAuth(['retailer']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wholesaler_id = $_POST['wholesaler_id'] ?? null;
    $retailer_id = $_SESSION['user_id'];
    $message = $_POST['message'] ?? '';

    if (!$wholesaler_id) {
        header('Location: ' . BASE_URL . 'retailer/dashboard.php?error=Select a wholesaler');
        exit();
    }

    // 1. Check if already affiliated
    $user_check = $pdo->prepare("SELECT wholesaler_id FROM users WHERE id = ?");
    $user_check->execute([$retailer_id]);
    $user = $user_check->fetch();

    if ($user['wholesaler_id']) {
        header('Location: ' . BASE_URL . 'retailer/dashboard.php?error=You are already in a network');
        exit();
    }

    // 2. Check for existing pending request
    $req_check = $pdo->prepare("SELECT id FROM network_requests WHERE retailer_id = ? AND status = 'pending'");
    $req_check->execute([$retailer_id]);
    if ($req_check->fetch()) {
        header('Location: ' . BASE_URL . 'retailer/dashboard.php?error=Existing request pending');
        exit();
    }

    // 3. Insert request
    $stmt = $pdo->prepare("INSERT INTO network_requests (retailer_id, wholesaler_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$retailer_id, $wholesaler_id, $message]);

    // 4. Add notification for wholesaler
    $notif = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'system', 'New Join Request', 'A new retailer has requested to join your network.')");
    $notif->execute([$wholesaler_id]);

    header('Location: ' . BASE_URL . 'retailer/dashboard.php?success=Request sent successfully');
} else {
    header('Location: ' . BASE_URL . 'retailer/dashboard.php');
}
