<?php
// api/auth/register.php
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $phone = $_POST['phone'];
    $address = !empty($_POST['address']) ? $_POST['address'] : null;
    $role = $_POST['role'];
    
    // Role-specific fields
    $region = !empty($_POST['region']) ? $_POST['region'] : null;
    $territory = !empty($_POST['territory']) ? $_POST['territory'] : null;
    $wholesaler_id = !empty($_POST['wholesaler_id']) ? $_POST['wholesaler_id'] : null;
    $distributor_id = !empty($_POST['distributor_id']) ? $_POST['distributor_id'] : null;
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
            INSERT INTO users (name, email, password, phone, address, role, region, territory, wholesaler_id, distributor_id, order_direct)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $name, $email, $password, $phone, $address, $role, 
            $region, $territory, $wholesaler_id, $distributor_id, $order_direct
        ]);
        
        // Redirect to login
        header('Location: ' . BASE_URL . 'login.php?registered=1');
        exit();
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            die("Email or record already exists (" . $e->getMessage() . "). <a href='" . BASE_URL . "register.php'>Try again</a>");
        } else {
            die("Registration failed: " . $e->getMessage());
        }
    }
}
