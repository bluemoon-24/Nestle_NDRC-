<?php
// api/auth/login.php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['wholesaler_id'] = $user['wholesaler_id'];
        $_SESSION['distributor_id'] = $user['distributor_id'];
        
        // Role-based redirect
        switch ($user['role']) {
            case 'retailer':
                header('Location: ' . BASE_URL . 'retailer/dashboard.php');
                break;
            case 'wholesaler':
                header('Location: ' . BASE_URL . 'wholesaler/dashboard.php');
                break;
            case 'distributor':
                header('Location: ' . BASE_URL . 'distributor/dashboard.php');
                break;
            case 'nestle':
                header('Location: ' . BASE_URL . 'nestle/dashboard.php');
                break;
        }
        exit();
    } else {
        header('Location: ' . BASE_URL . 'login.php?error=1');
        exit();
    }
}
