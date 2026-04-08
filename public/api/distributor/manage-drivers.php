<?php
// api/distributor/manage-drivers.php
require_once '../../config/database.php';
require_once '../../../app/Utils/helper.php';
session_start();

authorize('distributor');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // List all drivers (or specific one)
    $stmt = $pdo->query("SELECT * FROM drivers ORDER BY created_at DESC");
    $drivers = $stmt->fetchAll();
    jsonResponse('success', 'Drivers retrieved', $drivers);
}

if ($method === 'POST') {
    // Add new driver
    $name = $_POST['name'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $vehicle_type = $_POST['vehicle_type'] ?? 'van';

    if (!$name || !$phone) {
        jsonResponse('error', 'Name and phone are required', null, 400);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO drivers (name, phone, vehicle_type, status) VALUES (?, ?, ?, 'idle')");
        $stmt->execute([$name, $phone, $vehicle_type]);
        jsonResponse('success', 'Driver added successfully', ['id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        jsonResponse('error', 'Failed to add driver: ' . $e->getMessage(), null, 500);
    }
}

if ($method === 'DELETE' || (isset($_POST['_method']) && $_POST['_method'] === 'DELETE')) {
    // Delete driver
    $id = $_REQUEST['id'] ?? null;
    if (!$id) jsonResponse('error', 'Driver ID required', null, 400);

    try {
        $stmt = $pdo->prepare("DELETE FROM drivers WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse('success', 'Driver deleted');
    } catch (PDOException $e) {
        jsonResponse('error', 'Failed to delete driver', null, 500);
    }
}
