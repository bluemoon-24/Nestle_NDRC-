<?php
$hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
echo "Check 'password': " . (password_verify('password', $hash) ? 'YES' : 'NO') . "\n";
echo "Check 'admin123': " . (password_verify('admin123', $hash) ? 'YES' : 'NO') . "\n";
echo "Check 'password123': " . (password_verify('password123', $hash) ? 'YES' : 'NO') . "\n";
