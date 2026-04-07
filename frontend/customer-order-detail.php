<?php require_once('header.php'); ?>

<?php
if(!isset($_SESSION['customer'])) {
    safe_redirect('logout.php');
}

if(!isset($_GET['id'])) {
    safe_redirect('customer-order.php');
}

$order_id = (int)$_GET['id'];
if($order_id <= 0) {
    safe_redirect('customer-order.php');
}

$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE id=? AND customer_id=? LIMIT 1");
$statement->execute(array($order_id, (int)$_SESSION['customer']['cust_id']));
$payment = $statement->fetch(PDO::FETCH_ASSOC);
if(!$payment) {
    safe_redirect('customer-order.php');
}

$customer_address = null;
try {
    $statement = $pdo->prepare("SHOW TABLES LIKE 'tbl_customer_address'");
    $statement->execute();
    if($statement->rowCount() > 0) {
        $statement = $pdo->prepare("SELECT * FROM tbl_customer_address WHERE cust_id=? ORDER BY is_default DESC, address_id ASC LIMIT 1");
        $statement->execute(array((int)$payment['customer_id']));
        $customer_address = $statement->fetch(PDO::FETCH_ASSOC);
    }
} catch(PDOException $e) {
    $customer_address = null;
}

$receiver_name = $payment['customer_name'];
$receiver_phone = isset($_SESSION['customer']['cust_phone']) ? $_SESSION['customer']['cust_phone'] : '';
$email = $payment['customer_email'];

$address_line = '';
$city = '';
$district = '';
$ward = '';
$state = '';
$zip = '';

if($customer_address) {
    if(isset($customer_address['receiver_name']) && trim((string)$customer_address['receiver_name']) !== '') {
        $receiver_name = trim((string)$customer_address['receiver_name']);
    }
    if(isset($customer_address['receiver_phone']) && trim((string)$customer_address['receiver_phone']) !== '') {
        $receiver_phone = trim((string)$customer_address['receiver_phone']);
    }

    $address_line = isset($customer_address['address_line']) ? trim((string)$customer_address['address_line']) : '';
    $city = isset($customer_address['city']) ? trim((string)$customer_address['city']) : '';
    $district = isset($customer_address['district']) ? trim((string)$customer_address['district']) : '';
    $ward = isset($customer_address['ward']) ? trim((string)$customer_address['ward']) : '';
}

if(isset($_SESSION['customer']['cust_s_address']) && trim((string)$_SESSION['customer']['cust_s_address']) !== '' && $address_line === '') {
    $address_line = trim((string)$_SESSION['customer']['cust_s_address']);
}
if(isset($_SESSION['customer']['cust_s_city']) && trim((string)$_SESSION['customer']['cust_s_city']) !== '' && $city === '') {
    $city = trim((string)$_SESSION['customer']['cust_s_city']);
}
if(isset($_SESSION['customer']['cust_s_state']) && trim((string)$_SESSION['customer']['cust_s_state']) !== '' && $state === '') {
    $state = trim((string)$_SESSION['customer']['cust_s_state']);
}
if(isset($_SESSION['customer']['cust_s_zip']) && trim((string)$_SESSION['customer']['cust_s_zip']) !== '' && $zip === '') {
    $zip = trim((string)$_SESSION['customer']['cust_s_zip']);
}

$address_parts = array();
if($address_line !== '') { $address_parts[] = $address_line; }
if($ward !== '') { $address_parts[] = $ward; }
if($district !== '') { $address_parts[] = $district; }
if($city !== '') { $address_parts[] = $city; }
if($state !== '') { $address_parts[] = $state; }
if($zip !== '') { $address_parts[] = $zip; }
$full_address = implode(', ', $address_parts);
if($full_address === '') {
    $full_address = 'Chưa có dữ liệu địa chỉ giao hàng';
}

$statement = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id=? ORDER BY id ASC");
$statement->execute(array($payment['payment_id']));
$order_items = $statement->fetchAll(PDO::FETCH_ASSOC);

$order_total_display = isset($payment['order_total_amount']) ? (float)$payment['order_total_amount'] : (float)$payment['paid_amount'];
$payment_status_code = trim((string)$payment['payment_status']);
$payment_badge_class = ($payment_status_code === 'Completed' || $payment_status_code === 'Paid') ? 'payment-completed' : 'payment-pending';

$shipping_status_code = normalize_shipping_status_code($payment['shipping_status']);
$shipping_badge_class = 'shipping-pending';
if($shipping_status_code === 'Shipping') {
    $shipping_badge_class = 'shipping-shipping';
} elseif($shipping_status_code === 'Completed') {
    $shipping_badge_class = 'shipping-completed';
} elseif($shipping_status_code === 'Canceled') {
    $shipping_badge_class = 'shipping-canceled';
}
?>

<style>
.customer-order-detail-card {
    border: 1px solid #d2d6de;
    background: #fff;
    padding: 16px;
}

.customer-order-detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
}

.customer-order-detail-header h3 {
    margin: 0;
}

.customer-order-summary-table,
.customer-order-item-table {
    margin-bottom: 0;
}

.customer-order-summary-table th,
.customer-order-summary-table td,
.customer-order-item-table th,
.customer-order-item-table td {
    border-color: #e6e9ee !important;
}

.customer-order-summary-table th,
.customer-order-item-table thead th {
    background: #f9fafc;
    color: #555;
}

.customer-order-item-table thead th {
    text-transform: uppercase;
    letter-spacing: 0.3px;
    font-size: 11px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 10px;
    font-size: 12px;
    font-weight: 700;
    line-height: 1;
    color: #fff;
}

.status-badge.payment-pending,
.status-badge.shipping-pending {
    background: #777;
}

.status-badge.payment-completed,
.status-badge.shipping-completed {
    background: #00a65a;
}

.status-badge.shipping-shipping {
    background: #f39c12;
}

.status-badge.shipping-canceled {
    background: #dd4b39;
}
</style>

<div class="page">
    <div class="container">
        <div class="account-layout">
            <div class="account-sidebar-col">
                <?php require_once('customer-sidebar.php'); ?>
            </div>
            <div class="account-content-col">
                <div class="user-content account-content-card customer-order-detail-card">
                    <div class="customer-order-detail-header">
                        <h3>Chi tiết đơn hàng</h3>
                        <a href="customer-order.php" class="btn btn-primary btn-sm">Quay lại</a>
                    </div>

                    <div class="table-responsive" style="margin-bottom:16px;">
                        <table class="table table-bordered customer-order-summary-table">
                            <tr>
                                <th style="width:220px;">Họ tên</th>
                                <td><?php echo htmlspecialchars((string)$receiver_name, ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><?php echo htmlspecialchars((string)$email, ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th>Số điện thoại</th>
                                <td><?php echo htmlspecialchars((string)$receiver_phone, ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th>Địa chỉ giao hàng</th>
                                <td><?php echo htmlspecialchars((string)$full_address, ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th>Phương thức thanh toán</th>
                                <td><?php echo htmlspecialchars(format_payment_method_vi($payment['payment_method']), ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <tr>
                                <th>Trạng thái thanh toán</th>
                                <td>
                                    <span class="status-badge <?php echo $payment_badge_class; ?>">
                                        <?php echo htmlspecialchars(format_payment_status_vi($payment_status_code), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Trạng thái giao hàng</th>
                                <td>
                                    <span class="status-badge <?php echo $shipping_badge_class; ?>">
                                        <?php echo htmlspecialchars(format_shipping_status_vi($shipping_status_code), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Tổng tiền đơn hàng</th>
                                <td><?php echo format_price_vnd($order_total_display); ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped customer-order-item-table">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Sản phẩm</th>
                                    <th>Size</th>
                                    <th>Màu</th>
                                    <th>Số lượng</th>
                                    <th>Đơn giá</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($order_items) === 0): ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center;">Không có sản phẩm.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $idx = 0; foreach($order_items as $item): $idx++; ?>
                                        <?php
                                            $qty = isset($item['quantity']) ? (int)$item['quantity'] : 0;
                                            $unit_price = isset($item['unit_price']) ? (float)$item['unit_price'] : 0;
                                            $line_total = $qty * $unit_price;
                                        ?>
                                        <tr>
                                            <td><?php echo $idx; ?></td>
                                            <td><?php echo htmlspecialchars((string)$item['product_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars((string)$item['size'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars((string)$item['color'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo $qty; ?></td>
                                            <td><?php echo format_price_vnd($unit_price); ?></td>
                                            <td><?php echo format_price_vnd($line_total); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
