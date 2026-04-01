<?php require_once('header.php'); ?>

<?php
if(!isset($_POST['id']) || !isset($_POST['shipping_status'])) {
	safe_redirect('order.php');
}

$order_id = (int)$_POST['id'];
$shipping_status = normalize_shipping_status_code($_POST['shipping_status']);

$allowed_statuses = array('Pending', 'Shipping', 'Completed', 'Canceled');
if($order_id <= 0 || !in_array($shipping_status, $allowed_statuses, true)) {
	safe_redirect('order.php');
}

$statement = $pdo->prepare("SELECT id, payment_method, shipping_status FROM tbl_payment WHERE id=?");
$statement->execute(array($order_id));
$order = $statement->fetch(PDO::FETCH_ASSOC);
if(!$order) {
	safe_redirect('order.php');
}

$current_shipping_status = normalize_shipping_status_code($order['shipping_status']);

// Lock updates once order is delivered or canceled.
if($current_shipping_status === 'Completed' || $current_shipping_status === 'Canceled') {
	safe_redirect('order.php');
}

// Only allow moving to the immediate next step or cancel.
$allowed_next_statuses = array('Canceled');
if($current_shipping_status === 'Pending') {
	$allowed_next_statuses[] = 'Shipping';
} elseif($current_shipping_status === 'Shipping') {
	$allowed_next_statuses[] = 'Completed';
}

if(!in_array($shipping_status, $allowed_next_statuses, true)) {
	safe_redirect('order.php');
}

if($order['payment_method'] === 'Cash On Delivery') {
	$new_payment_status = ($shipping_status === 'Completed') ? 'Completed' : 'Pending';

	$has_order_total_amount = false;
	try {
		$statement = $pdo->prepare("SHOW COLUMNS FROM tbl_payment LIKE 'order_total_amount'");
		$statement->execute();
		$has_order_total_amount = $statement->rowCount() > 0;
	} catch(PDOException $e) {
		$has_order_total_amount = false;
	}

	if($has_order_total_amount) {
		if($shipping_status === 'Completed') {
			$statement = $pdo->prepare("UPDATE tbl_payment SET shipping_status=?, payment_status=?, paid_amount=order_total_amount WHERE id=?");
			$statement->execute(array($shipping_status, $new_payment_status, $order_id));
		} else {
			$statement = $pdo->prepare("UPDATE tbl_payment SET shipping_status=?, payment_status=?, paid_amount=0 WHERE id=?");
			$statement->execute(array($shipping_status, $new_payment_status, $order_id));
		}
	} else {
		if($shipping_status !== 'Completed') {
			$statement = $pdo->prepare("UPDATE tbl_payment SET shipping_status=?, payment_status=?, paid_amount=0 WHERE id=?");
			$statement->execute(array($shipping_status, $new_payment_status, $order_id));
		} else {
			$statement = $pdo->prepare("UPDATE tbl_payment SET shipping_status=?, payment_status=? WHERE id=?");
			$statement->execute(array($shipping_status, $new_payment_status, $order_id));
		}
	}
} else {
	$statement = $pdo->prepare("UPDATE tbl_payment SET shipping_status=? WHERE id=?");
	$statement->execute(array($shipping_status, $order_id));
}

safe_redirect('order.php');
