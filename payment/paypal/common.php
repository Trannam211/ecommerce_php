<?php

function paypal_json_response(array $payload, $status_code = 200)
{
    http_response_code((int)$status_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function paypal_get_json_input()
{
    $raw = file_get_contents('php://input');
    if($raw === false || trim($raw) === '') {
        return array();
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : array();
}

function paypal_currency_precision($currency)
{
    $code = strtoupper(trim((string)$currency));
    $zero_decimal_codes = array('JPY', 'HUF', 'TWD');
    return in_array($code, $zero_decimal_codes, true) ? 0 : 2;
}

function paypal_get_settings(PDO $pdo)
{
    $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1 LIMIT 1");
    $statement->execute();
    $row = $statement->fetch(PDO::FETCH_ASSOC);
    if(!$row) {
        return array(
            'enabled' => false,
            'client_id' => '',
            'client_secret' => '',
            'environment' => 'sandbox',
            'currency' => 'USD',
            'exchange_rate' => 24000
        );
    }

    $paypal_on_off = isset($row['paypal_on_off']) ? (int)$row['paypal_on_off'] : 0;
    $client_id = isset($row['paypal_client_id']) ? trim((string)$row['paypal_client_id']) : '';
    if($client_id === '' && isset($row['paypal_email'])) {
        $client_id = trim((string)$row['paypal_email']);
    }

    $client_secret = isset($row['paypal_client_secret']) ? trim((string)$row['paypal_client_secret']) : '';
    $environment = isset($row['paypal_env']) ? strtolower(trim((string)$row['paypal_env'])) : 'sandbox';
    if($environment !== 'live') {
        $environment = 'sandbox';
    }

    $currency = isset($row['paypal_currency']) ? strtoupper(trim((string)$row['paypal_currency'])) : 'USD';
    if(!preg_match('/^[A-Z]{3}$/', $currency)) {
        $currency = 'USD';
    }

    $exchange_rate = isset($row['paypal_exchange_rate']) ? (float)$row['paypal_exchange_rate'] : 24000;
    if($exchange_rate <= 0) {
        $exchange_rate = 24000;
    }

    return array(
        'enabled' => ($paypal_on_off === 1 && $client_id !== '' && $client_secret !== ''),
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'environment' => $environment,
        'currency' => $currency,
        'exchange_rate' => $exchange_rate
    );
}

function paypal_get_api_base_url($environment)
{
    return ($environment === 'live')
        ? 'https://api-m.paypal.com'
        : 'https://api-m.sandbox.paypal.com';
}

function paypal_http_request($method, $url, array $headers, $body = null, $auth_userpwd = null)
{
    if(!function_exists('curl_init')) {
        return array(
            'ok' => false,
            'status_code' => 0,
            'body' => '',
            'json' => null,
            'error' => 'May chu khong ho tro curl.'
        );
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper((string)$method));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    if($auth_userpwd !== null) {
        curl_setopt($ch, CURLOPT_USERPWD, (string)$auth_userpwd);
    }

    if($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    $raw_body = curl_exec($ch);
    $curl_error = curl_error($ch);
    $status_code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    $decoded = null;
    if(is_string($raw_body) && trim($raw_body) !== '') {
        $decoded = json_decode($raw_body, true);
    }

    if($curl_error !== '') {
        return array(
            'ok' => false,
            'status_code' => $status_code,
            'body' => (string)$raw_body,
            'json' => is_array($decoded) ? $decoded : null,
            'error' => $curl_error
        );
    }

    $ok = ($status_code >= 200 && $status_code < 300);
    return array(
        'ok' => $ok,
        'status_code' => $status_code,
        'body' => is_string($raw_body) ? $raw_body : '',
        'json' => is_array($decoded) ? $decoded : null,
        'error' => ''
    );
}

function paypal_generate_access_token(array $paypal_settings, &$error_message)
{
    $error_message = '';

    $base_url = paypal_get_api_base_url($paypal_settings['environment']);
    $response = paypal_http_request(
        'POST',
        $base_url.'/v1/oauth2/token',
        array(
            'Accept: application/json',
            'Accept-Language: en_US',
            'Content-Type: application/x-www-form-urlencoded'
        ),
        'grant_type=client_credentials',
        $paypal_settings['client_id'].':'.$paypal_settings['client_secret']
    );

    if(!$response['ok'] || !is_array($response['json']) || !isset($response['json']['access_token'])) {
        $error_message = 'Khong lay duoc access token PayPal.';
        return '';
    }

    return trim((string)$response['json']['access_token']);
}

function paypal_api_call(array $paypal_settings, $method, $path, $access_token, $payload = null, &$error_message = '')
{
    $error_message = '';
    $base_url = paypal_get_api_base_url($paypal_settings['environment']);
    $url = $base_url.$path;

    $headers = array(
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer '.$access_token
    );

    $body = null;
    if($payload !== null) {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    $response = paypal_http_request($method, $url, $headers, $body);
    if(!$response['ok']) {
        if($response['error'] !== '') {
            $error_message = $response['error'];
        } elseif(is_array($response['json']) && isset($response['json']['message'])) {
            $error_message = (string)$response['json']['message'];
        } else {
            $error_message = 'PayPal API request failed.';
        }
    }

    return $response;
}

function paypal_get_selected_cart_keys()
{
    if(!isset($_SESSION['cart_p_id']) || !is_array($_SESSION['cart_p_id'])) {
        return array();
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

    return $selected_cart_keys;
}

function paypal_calculate_checkout_totals(PDO $pdo, array $selected_cart_keys)
{
    $table_total_price = 0;

    foreach($selected_cart_keys as $selected_key) {
        if(!isset($_SESSION['cart_p_id'][$selected_key])) {
            continue;
        }

        $qty = isset($_SESSION['cart_p_qty'][$selected_key]) ? (int)$_SESSION['cart_p_qty'][$selected_key] : 0;
        $price = isset($_SESSION['cart_p_current_price'][$selected_key]) ? (float)$_SESSION['cart_p_current_price'][$selected_key] : 0;
        $table_total_price += ($qty * $price);
    }

    $shipping_cost = 0;
    try {
        $statement = $pdo->prepare("SELECT amount FROM tbl_shipping_cost_all WHERE sca_id=1 LIMIT 1");
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if($row && isset($row['amount'])) {
            $shipping_cost = (float)$row['amount'];
        }
    } catch(Exception $exception) {
        $shipping_cost = 0;
    }

    return array(
        'subtotal_vnd' => (float)$table_total_price,
        'shipping_vnd' => (float)$shipping_cost,
        'order_total_vnd' => (float)$table_total_price + (float)$shipping_cost
    );
}

function paypal_calculate_gateway_amount($order_total_vnd, $currency, $exchange_rate)
{
    $precision = paypal_currency_precision($currency);
    $amount = (float)$order_total_vnd;

    if(strtoupper((string)$currency) !== 'VND') {
        $rate = (float)$exchange_rate;
        if($rate <= 0) {
            $rate = 24000;
        }
        $amount = $amount / $rate;
    }

    $amount = round($amount, $precision);
    if($amount <= 0) {
        $amount = ($precision === 0) ? 1 : 0.01;
    }

    return array(
        'precision' => $precision,
        'value' => number_format((float)$amount, $precision, '.', ''),
        'float_value' => (float)$amount
    );
}

function paypal_find_payment_by_txnid(PDO $pdo, $txnid)
{
    $statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE txnid=? AND payment_method='PayPal' ORDER BY id DESC LIMIT 1");
    $statement->execute(array((string)$txnid));
    return $statement->fetch(PDO::FETCH_ASSOC);
}

function paypal_clear_checked_out_cart_items(array $selected_cart_keys)
{
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
}

function paypal_create_local_order(PDO $pdo, array $selected_cart_keys, array $payment_data, &$error_message)
{
    $error_message = '';

    if(count($selected_cart_keys) === 0) {
        $error_message = 'Khong co san pham de dat hang.';
        return '';
    }

    $payment_date = date('Y-m-d H:i:s');
    $payment_id = date('YmdHis').(string)random_int(100, 999);
    $order_total_amount = isset($payment_data['order_total_amount']) ? (int)round((float)$payment_data['order_total_amount']) : 0;
    $paid_amount = isset($payment_data['paid_amount']) ? (float)$payment_data['paid_amount'] : 0;
    $txnid = isset($payment_data['txnid']) ? trim((string)$payment_data['txnid']) : '';
    $bank_transaction_info = isset($payment_data['bank_transaction_info']) ? trim((string)$payment_data['bank_transaction_info']) : '';
    $payment_method = isset($payment_data['payment_method']) ? trim((string)$payment_data['payment_method']) : 'PayPal';
    $payment_status = isset($payment_data['payment_status']) ? trim((string)$payment_data['payment_status']) : 'Completed';
    $shipping_status = isset($payment_data['shipping_status']) ? trim((string)$payment_data['shipping_status']) : 'Pending';

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
        if(!isset($_SESSION['cart_p_id'][$key])) {
            continue;
        }
        $i++;
        $arr_cart_p_id[$i] = isset($_SESSION['cart_p_id'][$key]) ? $_SESSION['cart_p_id'][$key] : 0;
        $arr_cart_p_name[$i] = isset($_SESSION['cart_p_name'][$key]) ? $_SESSION['cart_p_name'][$key] : '';
        $arr_cart_size_id[$i] = isset($_SESSION['cart_size_id'][$key]) ? $_SESSION['cart_size_id'][$key] : 0;
        $arr_cart_size_name[$i] = isset($_SESSION['cart_size_name'][$key]) ? $_SESSION['cart_size_name'][$key] : '';
        $arr_cart_color_id[$i] = isset($_SESSION['cart_color_id'][$key]) ? $_SESSION['cart_color_id'][$key] : 0;
        $arr_cart_color_name[$i] = isset($_SESSION['cart_color_name'][$key]) ? $_SESSION['cart_color_name'][$key] : '';
        $arr_cart_p_qty[$i] = isset($_SESSION['cart_p_qty'][$key]) ? $_SESSION['cart_p_qty'][$key] : 0;
        $arr_cart_p_current_price[$i] = isset($_SESSION['cart_p_current_price'][$key]) ? $_SESSION['cart_p_current_price'][$key] : 0;
    }

    if(count($arr_cart_p_id) === 0) {
        $error_message = 'Khong tim thay san pham trong gio hang.';
        return '';
    }

    $has_order_total_amount = false;
    try {
        $statement = $pdo->prepare("SHOW COLUMNS FROM tbl_payment LIKE 'order_total_amount'");
        $statement->execute();
        $has_order_total_amount = $statement->rowCount() > 0;
    } catch(PDOException $e) {
        $has_order_total_amount = false;
    }

    try {
        $pdo->beginTransaction();

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
                                $txnid,
                                $paid_amount,
                                '',
                                '',
                                '',
                                '',
                                $bank_transaction_info,
                                $payment_method,
                                $payment_status,
                                $shipping_status,
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
                                $txnid,
                                $paid_amount,
                                '',
                                '',
                                '',
                                '',
                                $bank_transaction_info,
                                $payment_method,
                                $payment_status,
                                $shipping_status,
                                $payment_id
                            ));
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

        paypal_clear_checked_out_cart_items($selected_cart_keys);
        $_SESSION['last_payment_id'] = $payment_id;

        $pdo->commit();
        return $payment_id;
    } catch(Exception $exception) {
        if($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error_message = 'Khong the tao don hang sau khi thanh toan PayPal.';
        return '';
    }
}
