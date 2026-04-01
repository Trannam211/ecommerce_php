<?php require_once('header.php'); ?>

<?php

if(!isset($_REQUEST['id']) || !isset($_REQUEST['task'])) {
	safe_redirect('logout.php');
}

$order_id = (int)$_REQUEST['id'];
$task = trim((string)$_REQUEST['task']);

$allowed_tasks = array('Pending', 'Completed');
if($order_id <= 0 || !in_array($task, $allowed_tasks, true)) {
	safe_redirect('order.php');
}

// Check the id is valid or not
$statement = $pdo->prepare("SELECT payment_method FROM tbl_payment WHERE id=?");
$statement->execute(array($order_id));
$payment = $statement->fetch(PDO::FETCH_ASSOC);
if(!$payment) {
	safe_redirect('logout.php');
}

// COD: payment status is derived from shipping status.
if(isset($payment['payment_method']) && $payment['payment_method'] === 'Cash On Delivery') {
	safe_redirect('order.php');
}
?>

<?php
	$statement = $pdo->prepare("UPDATE tbl_payment SET payment_status=? WHERE id=?");
	$statement->execute(array($task, $order_id));

	safe_redirect('order.php');
?>
