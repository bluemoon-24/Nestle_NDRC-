<?php
// api/wholesaler/handle-network-request.php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'wholesaler') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];
    $status = $_POST['status']; // 'accepted' or 'rejected'

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT retailer_id FROM network_requests WHERE id = ? AND wholesaler_id = ?");
        $stmt->execute([$request_id, $_SESSION['user_id']]);
        $request = $stmt->fetch();
        
        if (!$request) {
            die("Invalid Request");
        }

        // Update request status
        $update = $pdo->prepare("UPDATE network_requests SET status = ? WHERE id = ?");
        $update->execute([$status, $request_id]);

        // If accepted, update retailer's wholesaler_id
        if ($status === 'accepted') {
            $update_retailer = $pdo->prepare("UPDATE users SET wholesaler_id = ?, order_direct = 0 WHERE id = ?");
            $update_retailer->execute([$_SESSION['user_id'], $request['retailer_id']]);
        }

        // Notify retailer
        $title = $status === 'accepted' ? 'Network Request Accepted' : 'Network Request Rejected';
        $message = $status === 'accepted' ? "Wholesaler " . $_SESSION['user_name'] . " has accepted your affiliation request." : "Wholesaler " . $_SESSION['user_name'] . " has declined your request.";
        
        $notif = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'system', ?, ?)");
        $notif->execute([$request['retailer_id'], $title, $message]);

        $pdo->commit();
        header('Location: ' . BASE_URL . 'wholesaler/dashboard.php?success=1');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
