<?php
require_once 'public/config/database.php';

$admin_hash = password_hash('admin123', PASSWORD_BCRYPT);
$pass_hash = password_hash('password123', PASSWORD_BCRYPT);

try {
    $pdo->beginTransaction();
    
    // Update admin
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE role = 'nestle'");
    $stmt->execute([$admin_hash]);
    
    // Update distributors
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE role = 'distributor'");
    $stmt->execute([$pass_hash]);
    
    $pdo->commit();
    echo "Passwords updated successfully.\n";
    echo "Admin: admin@nestle.lk / admin123\n";
    echo "Distributors: [email]@ndrc.lk / password123\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
