<?php
ob_start();
session_start();
include("../../admin/inc/config.php");

if(!isset($_POST['form_cod']) || !isset($_SESSION['customer']) || !isset($_SESSION['cart_p_id'])) {
    header('location: ../../checkout.php');
    exit;
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
    header('location: ../../cart.php');
    exit;
}

$payment_date = date('Y-m-d H:i:s');
$payment_id = time();
$amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;

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
                    $amount,
                    '',
                    '',
                    '',
                    '',
                    '',
                    'Cash On Delivery',
                    'Pending',
                    'Pending',
                    $payment_id
                ));

$arr_cart_p_id = array();
$arr_cart_p_name = array();
$arr_cart_size_name = array();
$arr_cart_color_name = array();
$arr_cart_p_qty = array();
$arr_cart_p_current_price = array();

$i = 0;
foreach($selected_cart_keys as $key) {
    $i++;
    $arr_cart_p_id[$i] = $_SESSION['cart_p_id'][$key];
    $arr_cart_p_name[$i] = $_SESSION['cart_p_name'][$key];
    $arr_cart_size_name[$i] = $_SESSION['cart_size_name'][$key];
    $arr_cart_color_name[$i] = $_SESSION['cart_color_name'][$key];
    $arr_cart_p_qty[$i] = $_SESSION['cart_p_qty'][$key];
    $arr_cart_p_current_price[$i] = $_SESSION['cart_p_current_price'][$key];
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

header('location: ../../payment_success.php');
exit;
