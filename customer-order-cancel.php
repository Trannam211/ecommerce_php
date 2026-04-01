<?php require_once('header.php'); ?>

<?php
if(!isset($_SESSION['customer'])) {
    safe_redirect(BASE_URL.'logout.php');
}

if(!isset($_POST['order_id'])) {
    safe_redirect(BASE_URL.'customer-order.php');
}

$order_id = (int)$_POST['order_id'];
if($order_id <= 0) {
    safe_redirect(BASE_URL.'customer-order.php');
}

$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE id=? AND customer_id=? LIMIT 1");
$statement->execute(array($order_id, (int)$_SESSION['customer']['cust_id']));
$order = $statement->fetch(PDO::FETCH_ASSOC);
if(!$order) {
    safe_redirect(BASE_URL.'customer-order.php');
}

$current_shipping_status = normalize_shipping_status_code($order['shipping_status']);

// Customer is allowed to cancel only while admin is still waiting for confirmation.
if($current_shipping_status !== 'Pending') {
    safe_redirect(BASE_URL.'customer-order.php');
}

// Return stock for all products in this order because the order is canceled before processing.
$statement = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id=?");
$statement->execute(array($order['payment_id']));
$order_items = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach($order_items as $item) {
    $product_id = isset($item['product_id']) ? (int)$item['product_id'] : 0;
    $qty = isset($item['quantity']) ? (int)$item['quantity'] : 0;
    if($product_id <= 0 || $qty <= 0) {
        continue;
    }

    $statement1 = $pdo->prepare("SELECT p_qty FROM tbl_product WHERE p_id=? LIMIT 1");
    $statement1->execute(array($product_id));
    $product_row = $statement1->fetch(PDO::FETCH_ASSOC);
    if(!$product_row) {
        continue;
    }

    $final_qty = ((int)$product_row['p_qty']) + $qty;
    $statement1 = $pdo->prepare("UPDATE tbl_product SET p_qty=? WHERE p_id=?");
    $statement1->execute(array($final_qty, $product_id));
}

if($order['payment_method'] === 'Cash On Delivery') {
    $statement = $pdo->prepare("UPDATE tbl_payment SET shipping_status='Canceled', payment_status='Pending', paid_amount=0 WHERE id=?");
    $statement->execute(array($order_id));
} else {
    $statement = $pdo->prepare("UPDATE tbl_payment SET shipping_status='Canceled', payment_status='Canceled' WHERE id=?");
    $statement->execute(array($order_id));
}

safe_redirect(BASE_URL.'customer-order.php');
