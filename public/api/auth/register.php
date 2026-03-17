<?php
// api/auth/register.php
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $phone = $_POST['phone'];
    $role = $_POST['role'];
    
    // Role-specific fields
    $region = $_POST['region'] ?? null;
    $territory = $_POST['territory'] ?? null;
    $wholesaler_id = null;
    $order_direct = 0;
    
    if ($role === 'retailer') {
        if (isset($_POST['order_type']) && $_POST['order_type'] === 'wholesaler') {
            $wholesaler_id = !empty($_POST['wholesaler_id']) ? $_POST['wholesaler_id'] : null;
        } else {
            $order_direct = 1;
        }
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, phone, role, region, territory, wholesaler_id, order_direct)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $name, $email, $password, $phone, $role, 
            $region, $territory, $wholesaler_id, $order_direct
        ]);
        
        // Redirect to login
        header('Location: /login.php?registered=1');
        exit();
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            die("Email already exists. <a href='/register.php'>Try again</a>");
        } else {
            die("Registration failed: " . $e->getMessage());
        }
    }
}
