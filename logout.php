<?php 
ob_start();
session_start();
require_once __DIR__ . '/admin/inc/config.php';
require_once __DIR__ . '/admin/inc/functions.php';
unset($_SESSION['customer']);
safe_redirect(BASE_URL.'login.php');
?>