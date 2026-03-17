<?php
// api/notifications/mark-read.php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("UPDATE notifications SET read_status = 1 WHERE user_id = ?");
$stmt->execute([$user_id]);

echo json_encode(['success' => true]);
