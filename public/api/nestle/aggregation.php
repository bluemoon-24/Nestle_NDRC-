<?php
// api/nestle/aggregation.php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

$today = date('Y-m-d');

// Summary stats
$stats = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM orders WHERE DATE(order_date) = '$today') as total,
        (SELECT COUNT(*) FROM orders WHERE status = 'distributor_confirmed' AND DATE(order_date) = '$today') as confirmed,
        (SELECT COUNT(*) FROM orders WHERE status = 'distributor_pending' AND DATE(order_date) = '$today') as pending,
        (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'distributor_confirmed' AND DATE(order_date) = '$today') as value
")->fetch();

$stats['value_formatted'] = formatCurrency($stats['value']);

// Detailed breakdown
$query = "
    SELECT 
        d.id as distributor_id,
        d.name as distributor_name,
        w.id as wholesaler_id,
        w.name as wholesaler_name,
        r.id as retailer_id,
        r.name as retailer_name,
        o.status,
        COUNT(o.id) as order_count,
        SUM(o.total_amount) as total_value
    FROM orders o
    JOIN users r ON o.retailer_id = r.id
    JOIN users d ON o.distributor_id = d.id
    LEFT JOIN users w ON o.wholesaler_id = w.id
    WHERE DATE(o.order_date) = ?
    GROUP BY d.id, w.id, r.id, o.status
    ORDER BY d.name, w.name, r.name
";

$stmt = $pdo->prepare($query);
$stmt->execute([$today]);
$rows = $stmt->fetchAll();

$distributors = [];

foreach ($rows as $row) {
    if (!isset($distributors[$row['distributor_id']])) {
        $distributors[$row['distributor_id']] = [
            'name' => $row['distributor_name'],
            'total_value' => 0,
            'order_count' => 0,
            'incoming_count' => 0,
            'outgoing_count' => 0,
            'wholesalers' => [],
            'direct_retailers' => []
        ];
    }
    
    // Categorize by status
    if ($row['status'] === 'distributor_pending') {
        $distributors[$row['distributor_id']]['incoming_count'] += $row['order_count'];
    } elseif (in_array($row['status'], ['distributor_confirmed', 'dispatched', 'delivered'])) {
        $distributors[$row['distributor_id']]['outgoing_count'] += $row['order_count'];
        $distributors[$row['distributor_id']]['total_value'] += $row['total_value'];
    }
    
    $distributors[$row['distributor_id']]['order_count'] += $row['order_count'];
    
    if ($row['wholesaler_id']) {
        if (!isset($distributors[$row['distributor_id']]['wholesalers'][$row['wholesaler_id']])) {
            $distributors[$row['distributor_id']]['wholesalers'][$row['wholesaler_id']] = [
                'name' => $row['wholesaler_name'],
                'order_count' => 0,
                'retailer_count' => 0,
                'total_value' => 0
            ];
        }
        $distributors[$row['distributor_id']]['wholesalers'][$row['wholesaler_id']]['order_count'] += $row['order_count'];
        $distributors[$row['distributor_id']]['wholesalers'][$row['wholesaler_id']]['retailer_count']++;
        $distributors[$row['distributor_id']]['wholesalers'][$row['wholesaler_id']]['total_value'] += $row['total_value'];
    } else {
        // Direct retailer
        if (!isset($distributors[$row['distributor_id']]['direct_retailers'][$row['retailer_id']])) {
            $distributors[$row['distributor_id']]['direct_retailers'][$row['retailer_id']] = [
                'name' => $row['retailer_name'],
                'order_count' => 0,
                'total_value' => 0
            ];
        }
        $distributors[$row['distributor_id']]['direct_retailers'][$row['retailer_id']]['order_count'] += $row['order_count'];
        $distributors[$row['distributor_id']]['direct_retailers'][$row['retailer_id']]['total_value'] += $row['total_value'];
    }
}

echo json_encode([
    'summary' => $stats,
    'distributors' => $distributors
]);
