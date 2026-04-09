<?php
ob_start();
session_start();

require_once __DIR__ . '/../../admin/inc/config.php';
require_once __DIR__ . '/../../admin/inc/functions.php';
require_once __DIR__ . '/common.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    paypal_json_response(array(
        'success' => false,
        'message' => 'Method not allowed.'
    ), 405);
}

$payload = paypal_get_json_input();
$order_id = isset($payload['orderID']) ? trim((string)$payload['orderID']) : '';
if($order_id === '') {
    paypal_json_response(array(
        'success' => false,
        'message' => 'Thieu orderID tu PayPal.'
    ), 400);
}

$existing_payment = paypal_find_payment_by_txnid($pdo, $order_id);
if($existing_payment) {
    $_SESSION['last_payment_id'] = $existing_payment['payment_id'];
    unset($_SESSION['paypal_checkout_pending']);
    paypal_json_response(array(
        'success' => true,
        'already_processed' => true,
        'redirect_url' => '../../frontend/payment_success.php'
    ));
}

if(!isset($_SESSION['customer']) || !isset($_SESSION['cart_p_id']) || !is_array($_SESSION['cart_p_id'])) {
    paypal_json_response(array(
        'success' => false,
        'message' => 'Phien thanh toan da het han. Vui long thu lai.'
    ), 401);
}

if(!isset($_SESSION['paypal_checkout_pending']) || !is_array($_SESSION['paypal_checkout_pending'])) {
    paypal_json_response(array(
        'success' => false,
        'message' => 'Khong tim thay phien thanh toan PayPal dang cho.'
    ), 400);
}

$pending = $_SESSION['paypal_checkout_pending'];
if(!isset($pending['order_id']) || trim((string)$pending['order_id']) !== $order_id) {
    paypal_json_response(array(
        'success' => false,
        'message' => 'Ma don PayPal khong khop voi phien hien tai.'
    ), 400);
}

if(!isset($pending['created_at']) || (time() - (int)$pending['created_at']) > 1800) {
    unset($_SESSION['paypal_checkout_pending']);
    paypal_json_response(array(
        'success' => false,
        'message' => 'Phien PayPal da het han. Vui long thanh toan lai.'
    ), 400);
}

$paypal_settings = paypal_get_settings($pdo);
if(!$paypal_settings['enabled']) {
    paypal_json_response(array(
        'success' => false,
        'message' => 'PayPal chua duoc cau hinh day du trong trang quan tri.'
    ), 400);
}

$token_error = '';
$access_token = paypal_generate_access_token($paypal_settings, $token_error);
if($access_token === '') {
    paypal_json_response(array(
        'success' => false,
        'message' => 'Khong the ket noi PayPal. '.$token_error
    ), 502);
}

$api_error = '';
$capture_data = array();
$response = paypal_api_call($paypal_settings, 'POST', '/v2/checkout/orders/'.rawurlencode($order_id).'/capture', $access_token, null, $api_error);
if(!$response['ok'] || !is_array($response['json'])) {
    $capture_error_payload = is_array($response['json']) ? $response['json'] : array();
    $already_captured = false;

    if(isset($capture_error_payload['details']) && is_array($capture_error_payload['details'])) {
        foreach($capture_error_payload['details'] as $detail) {
            if(isset($detail['issue']) && trim((string)$detail['issue']) === 'ORDER_ALREADY_CAPTURED') {
                $already_captured = true;
                break;
            }
        }
    }

    if($already_captured) {
        $existing_payment = paypal_find_payment_by_txnid($pdo, $order_id);
        if($existing_payment) {
            $_SESSION['last_payment_id'] = $existing_payment['payment_id'];
            unset($_SESSION['paypal_checkout_pending']);
            paypal_json_response(array(
                'success' => true,
                'already_processed' => true,
                'redirect_url' => '../../frontend/payment_success.php'
            ));
        }

        $details_error = '';
        $details_response = paypal_api_call($paypal_settings, 'GET', '/v2/checkout/orders/'.rawurlencode($order_id), $access_token, null, $details_error);
        if($details_response['ok'] && is_array($details_response['json'])) {
            $capture_data = $details_response['json'];
        } else {
            paypal_json_response(array(
                'success' => false,
                'message' => 'Don PayPal da duoc capture, nhung he thong chua dong bo duoc. Vui long lien he quan tri vien.'
            ), 502);
        }
    } else {
        paypal_json_response(array(
            'success' => false,
            'message' => 'Khong capture duoc don PayPal. '.$api_error
        ), 502);
    }
} else {
    $capture_data = $response['json'];
}

$order_status = isset($capture_data['status']) ? strtoupper(trim((string)$capture_data['status'])) : '';
$purchase_unit = (isset($capture_data['purchase_units'][0]) && is_array($capture_data['purchase_units'][0])) ? $capture_data['purchase_units'][0] : array();
$capture = (isset($purchase_unit['payments']['captures'][0]) && is_array($purchase_unit['payments']['captures'][0])) ? $purchase_unit['payments']['captures'][0] : array();
$capture_status = isset($capture['status']) ? strtoupper(trim((string)$capture['status'])) : '';
$capture_currency = isset($capture['amount']['currency_code']) ? strtoupper(trim((string)$capture['amount']['currency_code'])) : '';
$capture_value = isset($capture['amount']['value']) ? (float)$capture['amount']['value'] : 0;

if($order_status !== 'COMPLETED' || $capture_status !== 'COMPLETED') {
    paypal_json_response(array(
        'success' => false,
        'message' => 'PayPal chua xac nhan thanh toan thanh cong.'
    ), 400);
}

$expected_currency = isset($pending['currency']) ? strtoupper(trim((string)$pending['currency'])) : 'USD';
$expected_value = isset($pending['gateway_amount']) ? (float)$pending['gateway_amount'] : 0;
$precision = paypal_currency_precision($expected_currency);
$tolerance = ($precision === 0) ? 1 : 0.01;

if($capture_currency !== $expected_currency || abs($capture_value - $expected_value) > $tolerance) {
    paypal_json_response(array(
        'success' => false,
        'message' => 'So tien capture tu PayPal khong trung voi don hang.'
    ), 400);
}

$existing_payment = paypal_find_payment_by_txnid($pdo, $order_id);
if($existing_payment) {
    $_SESSION['last_payment_id'] = $existing_payment['payment_id'];
    unset($_SESSION['paypal_checkout_pending']);
    paypal_json_response(array(
        'success' => true,
        'already_processed' => true,
        'redirect_url' => '../../frontend/payment_success.php'
    ));
}

$selected_cart_keys = array();
if(isset($pending['selected_cart_keys']) && is_array($pending['selected_cart_keys'])) {
    foreach($pending['selected_cart_keys'] as $selected_key_raw) {
        $selected_key = (int)$selected_key_raw;
        if(!in_array($selected_key, $selected_cart_keys, true)) {
            $selected_cart_keys[] = $selected_key;
        }
    }
}
if(count($selected_cart_keys) === 0) {
    $selected_cart_keys = paypal_get_selected_cart_keys();
}

$order_total_vnd = isset($pending['order_total_vnd']) ? (float)$pending['order_total_vnd'] : 0;
if($order_total_vnd <= 0) {
    $totals = paypal_calculate_checkout_totals($pdo, $selected_cart_keys);
    $order_total_vnd = (float)$totals['order_total_vnd'];
}

$payer_email = isset($capture_data['payer']['email_address']) ? trim((string)$capture_data['payer']['email_address']) : '';
$payer_id = isset($capture_data['payer']['payer_id']) ? trim((string)$capture_data['payer']['payer_id']) : '';
$capture_id = isset($capture['id']) ? trim((string)$capture['id']) : '';

$payment_info_parts = array();
$payment_info_parts[] = 'PayPal Order ID: '.$order_id;
if($capture_id !== '') {
    $payment_info_parts[] = 'Capture ID: '.$capture_id;
}
if($payer_email !== '') {
    $payment_info_parts[] = 'Payer Email: '.$payer_email;
}
if($payer_id !== '') {
    $payment_info_parts[] = 'Payer ID: '.$payer_id;
}

$payment_data = array(
    'txnid' => $order_id,
    'paid_amount' => (float)$order_total_vnd,
    'order_total_amount' => (float)$order_total_vnd,
    'bank_transaction_info' => implode(' | ', $payment_info_parts),
    'payment_method' => 'PayPal',
    'payment_status' => 'Completed',
    'shipping_status' => 'Pending'
);

$local_order_error = '';
$new_payment_id = paypal_create_local_order($pdo, $selected_cart_keys, $payment_data, $local_order_error);
if($new_payment_id === '') {
    paypal_json_response(array(
        'success' => false,
        'message' => $local_order_error !== '' ? $local_order_error : 'Khong tao duoc don hang noi bo sau khi da thanh toan PayPal.'
    ), 500);
}

unset($_SESSION['paypal_checkout_pending']);

paypal_json_response(array(
    'success' => true,
    'redirect_url' => '../../frontend/payment_success.php',
    'payment_id' => $new_payment_id
));
