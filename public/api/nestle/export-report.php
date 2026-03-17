<?php
// api/nestle/export-report.php
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth_check.php';

checkAuth(['nestle']);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="NDRC_Daily_Report_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// Headers
fputcsv($output, ['Distributor', 'Wholesaler/Direct', 'Retailer', 'Order Count', 'Total Value (LKR)', 'Dispatch Date']);

$today = date('Y-m-d');

$query = "
    SELECT 
        d.name as distributor_name,
        COALESCE(w.name, 'DIRECT') as source_name,
        r.name as retailer_name,
        COUNT(o.id) as order_count,
        SUM(o.total_amount) as total_value,
        o.scheduled_dispatch_date
    FROM orders o
    JOIN users r ON o.retailer_id = r.id
    JOIN users d ON o.distributor_id = d.id
    LEFT JOIN users w ON o.wholesaler_id = w.id
    WHERE o.status = 'distributor_confirmed'
      AND DATE(o.order_date) = ?
    GROUP BY d.id, w.id, r.id
    ORDER BY d.name, w.name, r.name
";

$stmt = $pdo->prepare($query);
$stmt->execute([$today]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}

fclose($output);
exit();
