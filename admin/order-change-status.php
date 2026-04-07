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
$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE id=? LIMIT 1");
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
	if($task === 'Pending') {
		$statement = $pdo->prepare("UPDATE tbl_payment SET payment_status='Pending', paid_amount=0 WHERE id=?");
		$statement->execute(array($order_id));
	} else {
		$order_total_amount = 0;
		if(isset($payment['order_total_amount']) && (float)$payment['order_total_amount'] > 0) {
			$order_total_amount = (float)$payment['order_total_amount'];
		} elseif(isset($payment['payment_id']) && trim((string)$payment['payment_id']) !== '') {
			try {
				$statement = $pdo->prepare('SELECT COALESCE(SUM(quantity*unit_price),0) FROM tbl_order WHERE payment_id=?');
				$statement->execute(array($payment['payment_id']));
				$order_total_amount = (float)$statement->fetchColumn();
			} catch(PDOException $e) {
				$order_total_amount = 0;
			}
		}

		if($order_total_amount > 0) {
			$statement = $pdo->prepare("UPDATE tbl_payment SET payment_status='Completed', paid_amount=? WHERE id=?");
			$statement->execute(array($order_total_amount, $order_id));
		} else {
			$statement = $pdo->prepare("UPDATE tbl_payment SET payment_status='Completed' WHERE id=?");
			$statement->execute(array($order_id));
		}
	}

	safe_redirect('order.php');
?>
