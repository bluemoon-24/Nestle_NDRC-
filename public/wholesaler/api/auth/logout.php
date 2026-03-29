<?php
// api/auth/logout.php
session_start();
session_unset();
session_destroy();
require_once '../../config/database.php';
header('Location: ' . BASE_URL . 'index.php');
exit();
