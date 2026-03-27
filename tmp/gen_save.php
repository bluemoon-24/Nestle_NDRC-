<?php
$a = password_hash('admin123', PASSWORD_BCRYPT);
$p = password_hash('password123', PASSWORD_BCRYPT);
file_put_contents('c:/xampp/htdocs/Nestle_NDRC-/tmp/hashes_final.txt', $a . "\n" . $p);
