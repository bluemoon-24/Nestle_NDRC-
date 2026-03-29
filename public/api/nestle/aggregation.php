<?php
// api/nestle/aggregation.php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'nestle') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$today = date('Y-m-d');

// 1. Summary Metrics
$summary = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'distributor_confirmed' THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN status IN ('distributor_pending', 'placed') THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'distributor_confirmed' THEN total_amount ELSE 0 END) as value
    FROM orders 
    WHERE DATE(order_date) = '$today'
")->fetch();

$summary['value_formatted'] = 'Rs ' . number_format($summary['value'] ?? 0, 2);

// 2. Comprehensive Tree (Distributor -> Wholesaler/Direct -> Retailer)
$distributors_raw = $pdo->query("SELECT id, name FROM users WHERE role = 'distributor' AND status = 'active'")->fetchAll();
$distributors = [];

foreach ($distributors_raw as $d) {
    $dId = $d['id'];
    
    // Get stats for this distributor
    $dist_stats = $pdo->prepare("
        SELECT 
            COUNT(*) as incoming_count,
            SUM(CASE WHEN status = 'distributor_confirmed' THEN 1 ELSE 0 END) as outgoing_count,
            SUM(CASE WHEN status = 'distributor_confirmed' THEN total_amount ELSE 0 END) as total_value
        FROM orders 
        WHERE distributor_id = ? AND DATE(order_date) = '$today'
    ");
    $dist_stats->execute([$dId]);
    $stats = $dist_stats->fetch();

    if ($stats['incoming_count'] > 0) {
        $distributors[$dId] = [
            'name' => $d['name'],
            'incoming_count' => $stats['incoming_count'],
            'outgoing_count' => $stats['outgoing_count'],
            'total_value' => $stats['total_value'] ?? 0,
            'wholesalers' => [],
            'direct_retailers' => []
        ];

        // Fetch wholesalers under this distributor having orders today
        $wholesalers_stmt = $pdo->prepare("
            SELECT DISTINCT w.id, w.name, 
                   (SELECT COUNT(*) FROM users r WHERE r.wholesaler_id = w.id) as retailer_count,
                   (SELECT COUNT(*) FROM orders o WHERE o.wholesaler_id = w.id AND DATE(o.order_date) = '$today') as order_count
            FROM users w
            JOIN orders o ON o.wholesaler_id = w.id
            WHERE w.distributor_id = ? AND DATE(o.order_date) = '$today'
        ");
        $wholesalers_stmt->execute([$dId]);
        $wholesalers = $wholesalers_stmt->fetchAll();
        
        foreach ($wholesalers as $w) {
            $distributors[$dId]['wholesalers'][$w['id']] = $w;
        }

        // Fetch direct retailers under this distributor having orders today
        $direct_stmt = $pdo->prepare("
            SELECT DISTINCT r.id, r.name,
                   (SELECT COUNT(*) FROM orders o WHERE o.retailer_id = r.id AND DATE(o.order_date) = '$today' AND o.wholesaler_id IS NULL) as order_count
            FROM users r
            JOIN orders o ON o.retailer_id = r.id
            WHERE r.distributor_id = ? AND r.order_direct = 1 AND DATE(o.order_date) = '$today'
        ");
        $direct_stmt->execute([$dId]);
        $directs = $direct_stmt->fetchAll();
        
        foreach ($directs as $r) {
            $distributors[$dId]['direct_retailers'][$r['id']] = $r;
        }
    }
}

header('Content-Type: application/json');
echo json_encode([
    'summary' => $summary,
    'distributors' => $distributors
]);
