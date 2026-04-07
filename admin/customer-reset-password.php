<?php
require_once('inc/config.php');
require_once('inc/functions.php');

if(session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if(!isset($_SESSION['user'])) {
    safe_redirect('login.php');
}

$cust_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($cust_id <= 0) {
    safe_redirect('customer.php');
}

$statement = $pdo->prepare("SELECT cust_id, cust_name, cust_email FROM tbl_customer WHERE cust_id=? LIMIT 1");
$statement->execute(array($cust_id));
$customer = $statement->fetch(PDO::FETCH_ASSOC);
if(!$customer) {
    safe_redirect('customer.php');
}

$seed = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
$temp_password = '';
for($i=0;$i<10;$i++) {
    $temp_password .= $seed[random_int(0, strlen($seed) - 1)];
}

ensure_customer_password_storage($pdo);
$temp_password_hash = password_hash($temp_password, PASSWORD_DEFAULT);
if($temp_password_hash === false) {
    $_SESSION['admin_customer_reset_message'] = 'Không thể tạo mật khẩu an toàn để đặt lại cho '.(string)$customer['cust_email'].'. Vui lòng thử lại.';
    safe_redirect('customer.php');
}

$statement = $pdo->prepare("UPDATE tbl_customer SET cust_password=?, cust_token='' WHERE cust_id=?");
$statement->execute(array($temp_password_hash, $cust_id));

$_SESSION['admin_customer_reset_message'] = 'Đã đặt lại mật khẩu cho '.(string)$customer['cust_email'].' thành công. Mật khẩu tạm: '.$temp_password;

safe_redirect('customer.php');
