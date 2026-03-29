<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

checkAuth(['wholesaler']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'] ?? null;
    $action = $_POST['action'] ?? null; // 'approve' or 'reject'
    $wholesaler_id = $_SESSION['user_id'];

    if (!$request_id || !$action) {
        header('Location: ' . BASE_URL . 'wholesaler/retailers.php?error=Missing params');
        exit();
    }

    // 1. Fetch request details to verify wholesaler_id
    $req_stmt = $pdo->prepare("SELECT * FROM network_requests WHERE id = ? AND wholesaler_id = ? AND status = 'pending'");
    $req_stmt->execute([$request_id, $wholesaler_id]);
    $request = $req_stmt->fetch();

    if (!$request) {
        header('Location: ' . BASE_URL . 'wholesaler/retailers.php?error=Request not found');
        exit();
    }

    $retailer_id = $request['retailer_id'];

    if ($action === 'approve') {
        try {
            $pdo->beginTransaction();

            // Update request status
            $upd_req = $pdo->prepare("UPDATE network_requests SET status = 'approved' WHERE id = ?");
            $upd_req->execute([$request_id]);

            // Assign wholesaler to retailer
            $upd_user = $pdo->prepare("UPDATE users SET wholesaler_id = ? WHERE id = ?");
            $upd_user->execute([$wholesaler_id, $retailer_id]);

            // Add notification for retailer
            $notif = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'system', 'Network Approved', 'Your request to join the wholesaler network has been approved.')");
            $notif->execute([$retailer_id]);

            $pdo->commit();
            header('Location: ' . BASE_URL . 'wholesaler/retailers.php?success=Retailer approved');
        } catch (Exception $e) {
            $pdo->rollBack();
            header('Location: ' . BASE_URL . 'wholesaler/retailers.php?error=' . urlencode($e->getMessage()));
        }
    } elseif ($action === 'reject') {
        $upd_req = $pdo->prepare("UPDATE network_requests SET status = 'rejected' WHERE id = ?");
        $upd_req->execute([$request_id]);

        // Add notification for retailer
        $notif = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'system', 'Network Request Rejected', 'Your request to join the wholesaler network was rejected.')");
        $notif->execute([$retailer_id]);

        header('Location: ' . BASE_URL . 'wholesaler/retailers.php?success=Request rejected');
    }
} else {
    header('Location: ' . BASE_URL . 'wholesaler/retailers.php');
}
