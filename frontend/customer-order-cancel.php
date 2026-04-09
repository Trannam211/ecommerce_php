<?php require_once('header.php'); ?>

<?php
if(!isset($_SESSION['customer'])) {
    safe_redirect('logout.php');
}

if(!isset($_POST['order_id'])) {
    safe_redirect('customer-order.php');
}

$order_id = (int)$_POST['order_id'];
if($order_id <= 0) {
    safe_redirect('customer-order.php');
}

$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE id=? AND customer_id=? LIMIT 1");
$statement->execute(array($order_id, (int)$_SESSION['customer']['cust_id']));
$order = $statement->fetch(PDO::FETCH_ASSOC);
if(!$order) {
    safe_redirect('customer-order.php');
}

$current_shipping_status = normalize_shipping_status_code($order['shipping_status']);

// Customer is allowed to cancel only while admin is still waiting for confirmation.
if($current_shipping_status !== 'Pending') {
    safe_redirect('customer-order.php');
}

// Return stock for all products in this order because the order is canceled before processing.
$statement = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id=?");
$statement->execute(array($order['payment_id']));
$order_items = $statement->fetchAll(PDO::FETCH_ASSOC);

$variant_table_exists = false;
$statement_variant_check = $pdo->prepare("SHOW TABLES LIKE 'tbl_product_variant'");
$statement_variant_check->execute();
$variant_table_exists = ($statement_variant_check->rowCount() > 0);

$size_id_cache = array();
$color_id_cache = array();

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

    if($variant_table_exists) {
        $size_name = isset($item['size']) ? trim((string)$item['size']) : '';
        $color_name = isset($item['color']) ? trim((string)$item['color']) : '';

        if($size_name !== '' && $color_name !== '') {
            if(array_key_exists($size_name, $size_id_cache)) {
                $size_id = (int)$size_id_cache[$size_name];
            } else {
                $statement_size = $pdo->prepare("SELECT size_id FROM tbl_size WHERE size_name=? LIMIT 1");
                $statement_size->execute(array($size_name));
                $size_row = $statement_size->fetch(PDO::FETCH_ASSOC);
                $size_id = $size_row ? (int)$size_row['size_id'] : 0;
                $size_id_cache[$size_name] = $size_id;
            }

            if(array_key_exists($color_name, $color_id_cache)) {
                $color_id = (int)$color_id_cache[$color_name];
            } else {
                $statement_color = $pdo->prepare("SELECT color_id FROM tbl_color WHERE color_name=? LIMIT 1");
                $statement_color->execute(array($color_name));
                $color_row = $statement_color->fetch(PDO::FETCH_ASSOC);
                $color_id = $color_row ? (int)$color_row['color_id'] : 0;
                $color_id_cache[$color_name] = $color_id;
            }

            if($size_id > 0 && $color_id > 0) {
                $statement_variant = $pdo->prepare("SELECT pv_qty FROM tbl_product_variant WHERE p_id=? AND size_id=? AND color_id=? LIMIT 1");
                $statement_variant->execute(array($product_id, $size_id, $color_id));
                $variant_row = $statement_variant->fetch(PDO::FETCH_ASSOC);

                if($variant_row) {
                    $next_variant_qty = ((int)$variant_row['pv_qty']) + $qty;
                    $statement_variant_update = $pdo->prepare("UPDATE tbl_product_variant SET pv_qty=? WHERE p_id=? AND size_id=? AND color_id=?");
                    $statement_variant_update->execute(array($next_variant_qty, $product_id, $size_id, $color_id));
                }
            }
        }
    }
}

if($order['payment_method'] === 'Cash On Delivery') {
    $statement = $pdo->prepare("UPDATE tbl_payment SET shipping_status='Canceled', payment_status='Pending', paid_amount=0 WHERE id=?");
    $statement->execute(array($order_id));
} else {
    $statement = $pdo->prepare("UPDATE tbl_payment SET shipping_status='Canceled', payment_status='Canceled' WHERE id=?");
    $statement->execute(array($order_id));
}

safe_redirect('customer-order.php');
