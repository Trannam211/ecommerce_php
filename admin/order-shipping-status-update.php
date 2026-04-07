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

$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE id=? LIMIT 1");
$statement->execute(array($order_id));
$order = $statement->fetch(PDO::FETCH_ASSOC);
if(!$order) {
	safe_redirect('order.php');
}

$order_total_amount = 0;
if(isset($order['order_total_amount']) && (float)$order['order_total_amount'] > 0) {
	$order_total_amount = (float)$order['order_total_amount'];
} elseif(isset($order['payment_id']) && trim((string)$order['payment_id']) !== '') {
	try {
		$statement = $pdo->prepare('SELECT COALESCE(SUM(quantity*unit_price),0) FROM tbl_order WHERE payment_id=?');
		$statement->execute(array($order['payment_id']));
		$order_total_amount = (float)$statement->fetchColumn();
	} catch(PDOException $e) {
		$order_total_amount = 0;
	}
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
	$new_paid_amount = ($shipping_status === 'Completed') ? $order_total_amount : 0;

	$statement = $pdo->prepare('UPDATE tbl_payment SET shipping_status=?, payment_status=?, paid_amount=? WHERE id=?');
	$statement->execute(array($shipping_status, $new_payment_status, $new_paid_amount, $order_id));
} else {
	if($shipping_status === 'Completed') {
		if($order_total_amount > 0) {
			$statement = $pdo->prepare('UPDATE tbl_payment SET shipping_status=?, payment_status=?, paid_amount=? WHERE id=?');
			$statement->execute(array($shipping_status, 'Completed', $order_total_amount, $order_id));
		} else {
			$statement = $pdo->prepare('UPDATE tbl_payment SET shipping_status=?, payment_status=? WHERE id=?');
			$statement->execute(array($shipping_status, 'Completed', $order_id));
		}
	} else {
		$statement = $pdo->prepare('UPDATE tbl_payment SET shipping_status=? WHERE id=?');
		$statement->execute(array($shipping_status, $order_id));
	}
}

safe_redirect('order.php');
