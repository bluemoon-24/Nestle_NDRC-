<?php
// api/notifications/count.php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND read_status = 0");
$stmt->execute([$user_id]);
$row = $stmt->fetch();

echo json_encode(['count' => (int)$row['unread']]);
