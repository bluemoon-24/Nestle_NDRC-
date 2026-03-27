<?php
// reset_admin.php
$pdo = new PDO('mysql:host=localhost;dbname=ndrc_nestle', 'root', '');
$hash = password_hash('admin123', PASSWORD_BCRYPT);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@nestle.lk'");
if ($stmt->execute([$hash])) {
    echo "SUCCESS: Admin password reset to 'admin123'";
} else {
    echo "FAILURE: SQL Error";
}
?>
