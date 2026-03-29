<?php
// api/nestle/export-report.php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'nestle') {
    die("Unauthorized");
}

$today = date('Y-m-d');
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="NDRC_Report_' . $today . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Order Number', 'Retailer', 'Wholesaler', 'Distributor', 'Status', 'Date', 'Total Amount']);

$stmt = $pdo->prepare("
    SELECT o.order_number, r.name as retailer, w.name as wholesaler, d.name as distributor, o.status, o.order_date, o.total_amount
    FROM orders o
    JOIN users r ON o.retailer_id = r.id
    LEFT JOIN users w ON o.wholesaler_id = w.id
    JOIN users d ON o.distributor_id = d.id
    WHERE DATE(o.order_date) = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$today]);

while ($row = $stmt->fetch()) {
    fputcsv($output, $row);
}

fclose($output);
exit();
