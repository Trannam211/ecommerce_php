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

if(!isset($_SESSION['customer']) || !isset($_SESSION['cart_p_id']) || !is_array($_SESSION['cart_p_id'])) {
    paypal_json_response(array(
        'success' => false,
        'message' => 'Vui long dang nhap va them san pham vao gio hang.'
    ), 401);
}

$paypal_settings = paypal_get_settings($pdo);
if(!$paypal_settings['enabled']) {
    paypal_json_response(array(
        'success' => false,
        'message' => 'PayPal chua duoc cau hinh day du trong trang quan tri.'
    ), 400);
}

$selected_cart_keys = paypal_get_selected_cart_keys();
if(count($selected_cart_keys) === 0) {
    paypal_json_response(array(
        'success' => false,
        'message' => 'Khong co san pham nao de thanh toan.'
    ), 400);
}

$totals = paypal_calculate_checkout_totals($pdo, $selected_cart_keys);
$gateway_amount = paypal_calculate_gateway_amount($totals['order_total_vnd'], $paypal_settings['currency'], $paypal_settings['exchange_rate']);

$token_error = '';
$access_token = paypal_generate_access_token($paypal_settings, $token_error);
if($access_token === '') {
    paypal_json_response(array(
        'success' => false,
        'message' => 'Khong the ket noi PayPal. '.$token_error
    ), 502);
}

$invoice_id = 'PP-'.date('YmdHis').'-'.(string)random_int(1000, 9999);

$payload = array(
    'intent' => 'CAPTURE',
    'purchase_units' => array(
        array(
            'reference_id' => 'checkout_'.(int)$_SESSION['customer']['cust_id'],
            'description' => 'Thanh toan don hang '.$invoice_id,
            'invoice_id' => $invoice_id,
            'amount' => array(
                'currency_code' => $paypal_settings['currency'],
                'value' => $gateway_amount['value']
            )
        )
    ),
    'application_context' => array(
        'shipping_preference' => 'NO_SHIPPING',
        'user_action' => 'PAY_NOW'
    )
);

$api_error = '';
$response = paypal_api_call($paypal_settings, 'POST', '/v2/checkout/orders', $access_token, $payload, $api_error);
if(!$response['ok'] || !is_array($response['json']) || !isset($response['json']['id'])) {
    paypal_json_response(array(
        'success' => false,
        'message' => 'PayPal khong tao duoc don thanh toan. '.$api_error
    ), 502);
}

$order_id = trim((string)$response['json']['id']);
if($order_id === '') {
    paypal_json_response(array(
        'success' => false,
        'message' => 'PayPal tra ve du lieu khong hop le.'
    ), 502);
}

$_SESSION['paypal_checkout_pending'] = array(
    'order_id' => $order_id,
    'selected_cart_keys' => $selected_cart_keys,
    'order_total_vnd' => (float)$totals['order_total_vnd'],
    'currency' => $paypal_settings['currency'],
    'gateway_amount' => $gateway_amount['value'],
    'created_at' => time()
);

paypal_json_response(array(
    'success' => true,
    'orderID' => $order_id
));
