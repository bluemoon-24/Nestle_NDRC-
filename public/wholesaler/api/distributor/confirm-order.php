<?php
// api/distributor/confirm-order.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $order_id = $_POST['order_id'];
    
    try {
        $pdo->beginTransaction();
        
        // 1. Update order status
        $stmt = $pdo->prepare("UPDATE orders SET status = 'distributor_confirmed' WHERE id = ?");
        $stmt->execute([$order_id]);
        
        // 2. Increase reserved stock for each item in the order
        // Note: In a real system, we'd check if available stock exists first. 
        // Flow: Fetch items from order_items and update warehouse_stock
        $items_stmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        $items_stmt->execute([$order_id]);
        $items = $items_stmt->fetchAll();
        
        $update_reserved = $pdo->prepare("
            UPDATE warehouse_stock 
            SET reserved_stock = reserved_stock + ? 
            WHERE product_id = ?
        ");
        
        foreach ($items as $item) {
            $update_reserved->execute([$item['quantity'], $item['product_id']]);
            
            // 3. Check for low stock alert
            $check = $pdo->prepare("
                SELECT ws.available_stock, ws.reorder_point, p.name 
                FROM warehouse_stock ws
                JOIN products p ON ws.product_id = p.id
                WHERE ws.product_id = ?
            ");
            $check->execute([$item['product_id']]);
            $stock = $check->fetch();
            
            if ($stock['available_stock'] < $stock['reorder_point']) {
                // Send alert to Nestle Admin
                $nestle_admins = $pdo->query("SELECT id FROM users WHERE role = 'nestle'")->fetchAll();
                foreach ($nestle_admins as $admin) {
                    createNotification(
                        $admin['id'], 
                        'stock_alert', 
                        'Low Stock Warning', 
                        "Stock for " . $stock['name'] . " is below the reorder point.", 
                        "/nestle/warehouse.php"
                    );
                }
            }
        }
        
        // 4. Notify Retailer and Wholesaler
        $order_info = $pdo->prepare("SELECT retailer_id, wholesaler_id, order_number FROM orders WHERE id = ?");
        $order_info->execute([$order_id]);
        $o = $order_info->fetch();
        
        createNotification($o['retailer_id'], 'order_status', 'Order Confirmed', "Your order " . $o['order_number'] . " has been confirmed by the distributor.", "/retailer/dashboard.php");
        
        if ($o['wholesaler_id']) {
            createNotification($o['wholesaler_id'], 'order_status', 'Order Confirmed', "Retailer order " . $o['order_number'] . " confirmed by distributor.", "/wholesaler/dashboard.php");
        }
        
        $pdo->commit();
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Confirmation failed: " . $e->getMessage());
    }
}
