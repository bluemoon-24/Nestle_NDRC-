<?php
// reset_users.php
$pdo = new PDO('mysql:host=localhost;dbname=ndrc_nestle', 'root', '');

// Reset Admin
$admin_hash = password_hash('admin123', PASSWORD_BCRYPT);
$pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@nestle.lk'")->execute([$admin_hash]);

// Reset Udakara
$udakara_hash = password_hash('123456', PASSWORD_BCRYPT);
$pdo->prepare("UPDATE users SET password = ? WHERE email = 'udakara@gmail.com'")->execute([$udakara_hash]);

// Reset Sunshine
$sunshine_hash = password_hash('123456', PASSWORD_BCRYPT);
$pdo->prepare("UPDATE users SET password = ? WHERE email = 'sunshine@gmail.com'")->execute([$sunshine_hash]);

echo "SUCCESS: Admin (admin123), Udakara (123456), Sunshine (123456) reset.";
?>
