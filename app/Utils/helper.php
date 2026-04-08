<?php
// app/Utils/helper.php

/**
 * Send a JSON response and exit
 */
function jsonResponse($status, $message, $data = null, $code = 200) {
    header('Content-Type: application/json');
    http_response_code($code);
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

/**
 * Check if user has a specific role
 */
function authorize($roles) {
    if (!isset($_SESSION['user_id'])) {
        jsonResponse('error', 'Unauthorized access', null, 401);
    }
    
    if (is_string($roles) && $_SESSION['user_role'] !== $roles) {
        jsonResponse('error', 'Forbidden: Insufficient permissions', null, 403);
    }
    
    if (is_array($roles) && !in_array($_SESSION['user_role'], $roles)) {
        jsonResponse('error', 'Forbidden: Insufficient permissions', null, 403);
    }
}
