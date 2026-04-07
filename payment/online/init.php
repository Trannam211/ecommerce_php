<?php
ob_start();
session_start();
require_once __DIR__ . '/../../admin/inc/config.php';
require_once __DIR__ . '/../../admin/inc/functions.php';

if(!isset($_POST['form_online']) || !isset($_SESSION['customer']) || !isset($_SESSION['cart_p_id'])) {
    safe_redirect('../../frontend/checkout.php');
}

$all_cart_keys = array_keys($_SESSION['cart_p_id']);
$selected_cart_keys = array();

if(isset($_SESSION['checkout_selected_item_keys']) && is_array($_SESSION['checkout_selected_item_keys'])) {
    foreach($_SESSION['checkout_selected_item_keys'] as $selected_key_raw) {
        $selected_key = (int)$selected_key_raw;
        if(in_array($selected_key, $all_cart_keys, true) && !in_array($selected_key, $selected_cart_keys, true)) {
            $selected_cart_keys[] = $selected_key;
        }
    }
}

if(count($selected_cart_keys) === 0) {
    $selected_cart_keys = $all_cart_keys;
}

if(count($selected_cart_keys) === 0) {
    safe_redirect('../../frontend/cart.php');
}

$payment_date = date('Y-m-d H:i:s');
$payment_id = time();
$amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
$order_total_amount = (int)round($amount);
$paid_amount = 0;
$payment_note = 'Online placeholder - chua xu ly cong thanh toan thuc te';

$has_order_total_amount = false;
try {
    $statement = $pdo->prepare("SHOW COLUMNS FROM tbl_payment LIKE 'order_total_amount'");
    $statement->execute();
    $has_order_total_amount = $statement->rowCount() > 0;
} catch(PDOException $e) {
    $has_order_total_amount = false;
}

if($has_order_total_amount) {
    $statement = $pdo->prepare("INSERT INTO tbl_payment (
                            customer_id,
                            customer_name,
                            customer_email,
                            payment_date,
                            txnid,
                            paid_amount,
                            card_number,
                            card_cvv,
                            card_month,
                            card_year,
                            bank_transaction_info,
                            payment_method,
                            payment_status,
                            shipping_status,
                            payment_id,
                            order_total_amount
                        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $statement->execute(array(
                        $_SESSION['customer']['cust_id'],
                        $_SESSION['customer']['cust_name'],
                        $_SESSION['customer']['cust_email'],
                        $payment_date,
                        '',
                        $paid_amount,
                        '',
                        '',
                        '',
                        '',
                        $payment_note,
                        'Online Payment',
                        'Pending',
                        'Pending',
                        $payment_id,
                        $order_total_amount
                    ));
} else {
    $statement = $pdo->prepare("INSERT INTO tbl_payment (
                            customer_id,
                            customer_name,
                            customer_email,
                            payment_date,
                            txnid,
                            paid_amount,
                            card_number,
                            card_cvv,
                            card_month,
                            card_year,
                            bank_transaction_info,
                            payment_method,
                            payment_status,
                            shipping_status,
                            payment_id
                        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $statement->execute(array(
                        $_SESSION['customer']['cust_id'],
                        $_SESSION['customer']['cust_name'],
                        $_SESSION['customer']['cust_email'],
                        $payment_date,
                        '',
                        $paid_amount,
                        '',
                        '',
                        '',
                        '',
                        $payment_note,
                        'Online Payment',
                        'Pending',
                        'Pending',
                        $payment_id
                    ));
}

$arr_cart_p_id = array();
$arr_cart_p_name = array();
$arr_cart_size_id = array();
$arr_cart_size_name = array();
$arr_cart_color_id = array();
$arr_cart_color_name = array();
$arr_cart_p_qty = array();
$arr_cart_p_current_price = array();

$i = 0;
foreach($selected_cart_keys as $key) {
    $i++;
    $arr_cart_p_id[$i] = $_SESSION['cart_p_id'][$key];
    $arr_cart_p_name[$i] = $_SESSION['cart_p_name'][$key];
    $arr_cart_size_id[$i] = $_SESSION['cart_size_id'][$key];
    $arr_cart_size_name[$i] = $_SESSION['cart_size_name'][$key];
    $arr_cart_color_id[$i] = $_SESSION['cart_color_id'][$key];
    $arr_cart_color_name[$i] = $_SESSION['cart_color_name'][$key];
    $arr_cart_p_qty[$i] = $_SESSION['cart_p_qty'][$key];
    $arr_cart_p_current_price[$i] = $_SESSION['cart_p_current_price'][$key];
}

$variant_table_exists = false;
$variant_stock_map = array();
$statement = $pdo->prepare("SHOW TABLES LIKE 'tbl_product_variant'");
$statement->execute();
$variant_table_exists = $statement->rowCount() > 0;
if($variant_table_exists && count($arr_cart_p_id) > 0) {
    $statement = $pdo->prepare("SELECT p_id, size_id, color_id, pv_qty FROM tbl_product_variant");
    $statement->execute();
    while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $key = ((int)$row['p_id']).'_'.((int)$row['size_id']).'_'.((int)$row['color_id']);
        $variant_stock_map[$key] = (int)$row['pv_qty'];
    }
}

$stock_map = array();
$statement = $pdo->prepare("SELECT p_id, p_qty FROM tbl_product");
$statement->execute();
while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
    $stock_map[(int)$row['p_id']] = (int)$row['p_qty'];
}

for($i=1;$i<=count($arr_cart_p_name);$i++) {
    $statement = $pdo->prepare("INSERT INTO tbl_order (
                    product_id,
                    product_name,
                    size,
                    color,
                    quantity,
                    unit_price,
                    payment_id
                    )
                    VALUES (?,?,?,?,?,?,?)");
    $statement->execute(array(
                    $arr_cart_p_id[$i],
                    $arr_cart_p_name[$i],
                    $arr_cart_size_name[$i],
                    $arr_cart_color_name[$i],
                    $arr_cart_p_qty[$i],
                    $arr_cart_p_current_price[$i],
                    $payment_id
                ));

    $current_qty = isset($stock_map[(int)$arr_cart_p_id[$i]]) ? $stock_map[(int)$arr_cart_p_id[$i]] : 0;
    $final_quantity = $current_qty - (int)$arr_cart_p_qty[$i];
    if($final_quantity < 0) {
        $final_quantity = 0;
    }
    $statement = $pdo->prepare("UPDATE tbl_product SET p_qty=? WHERE p_id=?");
    $statement->execute(array($final_quantity, $arr_cart_p_id[$i]));

    $stock_map[(int)$arr_cart_p_id[$i]] = $final_quantity;

    if($variant_table_exists) {
        $v_p_id = (int)$arr_cart_p_id[$i];
        $v_size_id = isset($arr_cart_size_id[$i]) ? (int)$arr_cart_size_id[$i] : 0;
        $v_color_id = isset($arr_cart_color_id[$i]) ? (int)$arr_cart_color_id[$i] : 0;
        if($v_size_id > 0 && $v_color_id > 0) {
            $v_key = $v_p_id.'_'.$v_size_id.'_'.$v_color_id;
            $current_variant_qty = isset($variant_stock_map[$v_key]) ? (int)$variant_stock_map[$v_key] : 0;
            $next_variant_qty = $current_variant_qty - (int)$arr_cart_p_qty[$i];
            if($next_variant_qty < 0) {
                $next_variant_qty = 0;
            }
            $statement = $pdo->prepare("UPDATE tbl_product_variant SET pv_qty=? WHERE p_id=? AND size_id=? AND color_id=?");
            $statement->execute(array($next_variant_qty, $v_p_id, $v_size_id, $v_color_id));
            $variant_stock_map[$v_key] = $next_variant_qty;
        }
    }
}

$cart_fields = array(
    'cart_p_id',
    'cart_size_id',
    'cart_size_name',
    'cart_color_id',
    'cart_color_name',
    'cart_p_qty',
    'cart_p_current_price',
    'cart_p_name',
    'cart_p_featured_photo'
);
$selected_lookup = array_flip($selected_cart_keys);
foreach($cart_fields as $field) {
    if(!isset($_SESSION[$field]) || !is_array($_SESSION[$field])) {
        continue;
    }
    $new_values = array();
    $new_idx = 1;
    foreach($_SESSION[$field] as $key => $value) {
        if(isset($selected_lookup[(int)$key])) {
            continue;
        }
        $new_values[$new_idx] = $value;
        $new_idx++;
    }
    if(count($new_values) === 0) {
        unset($_SESSION[$field]);
    } else {
        $_SESSION[$field] = $new_values;
    }
}

unset($_SESSION['checkout_selected_item_keys']);

$_SESSION['last_payment_id'] = $payment_id;

safe_redirect('../../frontend/payment_success.php');
