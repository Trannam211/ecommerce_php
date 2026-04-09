<?php require_once('header.php'); ?>

<?php
function admin_is_valid_ymd($value) {
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$value) === 1;
}

function admin_casefold_text($value) {
    $text = trim((string)$value);
    $text = preg_replace('/\s+/u', ' ', $text);

    if(function_exists('mb_strtolower')) {
        return mb_strtolower($text, 'UTF-8');
    }

    return strtolower($text);
}

function admin_location_equals($left, $right) {
    $a = admin_casefold_text($left);
    $b = admin_casefold_text($right);
    if($a === '' || $b === '') {
        return false;
    }
    return $a === $b;
}

function build_admin_order_filter_url($status, $base_params) {
    $params = $base_params;
    $params['status'] = $status;
    return 'order.php?'.http_build_query($params);
}

$admin_order_filter = isset($_GET['status']) ? trim((string)$_GET['status']) : 'all';
$allowed_admin_filters = array('all', 'pending', 'shipping', 'completed', 'canceled');
if(!in_array($admin_order_filter, $allowed_admin_filters, true)) {
    $admin_order_filter = 'all';
}

$admin_order_from_date = isset($_GET['from_date']) ? trim((string)$_GET['from_date']) : '';
$admin_order_to_date = isset($_GET['to_date']) ? trim((string)$_GET['to_date']) : '';
$admin_order_province_id = isset($_GET['province_id']) ? (int)$_GET['province_id'] : 0;
$admin_order_district_id = isset($_GET['district_id']) ? (int)$_GET['district_id'] : 0;
$admin_order_ward_id = isset($_GET['ward_id']) ? (int)$_GET['ward_id'] : 0;
$admin_order_city = isset($_GET['city']) ? trim((string)$_GET['city']) : '';
$admin_order_district = isset($_GET['district']) ? trim((string)$_GET['district']) : '';
$admin_order_ward = isset($_GET['ward']) ? trim((string)$_GET['ward']) : '';

if($admin_order_province_id < 0) { $admin_order_province_id = 0; }
if($admin_order_district_id < 0) { $admin_order_district_id = 0; }
if($admin_order_ward_id < 0) { $admin_order_ward_id = 0; }

if(strlen($admin_order_city) > 120) { $admin_order_city = substr($admin_order_city, 0, 120); }
if(strlen($admin_order_district) > 120) { $admin_order_district = substr($admin_order_district, 0, 120); }
if(strlen($admin_order_ward) > 120) { $admin_order_ward = substr($admin_order_ward, 0, 120); }

if($admin_order_from_date !== '' && !admin_is_valid_ymd($admin_order_from_date)) {
    $admin_order_from_date = '';
}
if($admin_order_to_date !== '' && !admin_is_valid_ymd($admin_order_to_date)) {
    $admin_order_to_date = '';
}
if($admin_order_from_date !== '' && $admin_order_to_date !== '' && $admin_order_from_date > $admin_order_to_date) {
    $temp_date = $admin_order_from_date;
    $admin_order_from_date = $admin_order_to_date;
    $admin_order_to_date = $temp_date;
}

$admin_order_base_params = array();
if($admin_order_from_date !== '') {
    $admin_order_base_params['from_date'] = $admin_order_from_date;
}
if($admin_order_to_date !== '') {
    $admin_order_base_params['to_date'] = $admin_order_to_date;
}
if($admin_order_province_id > 0) {
    $admin_order_base_params['province_id'] = $admin_order_province_id;
}
if($admin_order_district_id > 0) {
    $admin_order_base_params['district_id'] = $admin_order_district_id;
}
if($admin_order_ward_id > 0) {
    $admin_order_base_params['ward_id'] = $admin_order_ward_id;
}
if($admin_order_city !== '') {
    $admin_order_base_params['city'] = $admin_order_city;
}
if($admin_order_district !== '') {
    $admin_order_base_params['district'] = $admin_order_district;
}
if($admin_order_ward !== '') {
    $admin_order_base_params['ward'] = $admin_order_ward;
}

$has_customer_address_table = false;
try {
    $statement_check_address = $pdo->prepare("SHOW TABLES LIKE 'tbl_customer_address'");
    $statement_check_address->execute();
    $has_customer_address_table = $statement_check_address->rowCount() > 0;
} catch(PDOException $e) {
    $has_customer_address_table = false;
}

if($has_customer_address_table) {
    $statement = $pdo->prepare("SELECT p.*, 
        (SELECT ca.city FROM tbl_customer_address ca WHERE ca.cust_id=p.customer_id ORDER BY ca.is_default DESC, ca.address_id ASC LIMIT 1) AS delivery_city,
        (SELECT ca.district FROM tbl_customer_address ca WHERE ca.cust_id=p.customer_id ORDER BY ca.is_default DESC, ca.address_id ASC LIMIT 1) AS delivery_district,
        (SELECT ca.ward FROM tbl_customer_address ca WHERE ca.cust_id=p.customer_id ORDER BY ca.is_default DESC, ca.address_id ASC LIMIT 1) AS delivery_ward
        FROM tbl_payment p
        ORDER BY p.id DESC");
} else {
    $statement = $pdo->prepare("SELECT p.*, '' AS delivery_city, '' AS delivery_district, '' AS delivery_ward FROM tbl_payment p ORDER BY p.id DESC");
}
$statement->execute();
$all_admin_orders = $statement->fetchAll(PDO::FETCH_ASSOC);

$admin_order_counts = array(
    'all' => 0,
    'pending' => 0,
    'shipping' => 0,
    'completed' => 0,
    'canceled' => 0
);
$filtered_admin_orders = array();

foreach($all_admin_orders as $admin_order_row) {
    $order_payment_date = isset($admin_order_row['payment_date']) ? substr((string)$admin_order_row['payment_date'], 0, 10) : '';
    if($admin_order_from_date !== '' && ($order_payment_date === '' || $order_payment_date < $admin_order_from_date)) {
        continue;
    }
    if($admin_order_to_date !== '' && ($order_payment_date === '' || $order_payment_date > $admin_order_to_date)) {
        continue;
    }

    $order_city_text = isset($admin_order_row['delivery_city']) ? trim((string)$admin_order_row['delivery_city']) : '';
    $order_district_text = isset($admin_order_row['delivery_district']) ? trim((string)$admin_order_row['delivery_district']) : '';
    $order_ward_text = isset($admin_order_row['delivery_ward']) ? trim((string)$admin_order_row['delivery_ward']) : '';

    if($admin_order_city !== '' && !admin_location_equals($order_city_text, $admin_order_city)) {
        continue;
    }
    if($admin_order_district !== '' && !admin_location_equals($order_district_text, $admin_order_district)) {
        continue;
    }
    if($admin_order_ward !== '' && !admin_location_equals($order_ward_text, $admin_order_ward)) {
        continue;
    }

    $admin_order_counts['all']++;
    $status_code = normalize_shipping_status_code($admin_order_row['shipping_status']);
    if($status_code === 'Pending') {
        $admin_order_counts['pending']++;
    } elseif($status_code === 'Shipping') {
        $admin_order_counts['shipping']++;
    } elseif($status_code === 'Completed') {
        $admin_order_counts['completed']++;
    } elseif($status_code === 'Canceled') {
        $admin_order_counts['canceled']++;
    }

    if($admin_order_filter === 'all') {
        $filtered_admin_orders[] = $admin_order_row;
    } elseif($admin_order_filter === 'pending' && $status_code === 'Pending') {
        $filtered_admin_orders[] = $admin_order_row;
    } elseif($admin_order_filter === 'shipping' && $status_code === 'Shipping') {
        $filtered_admin_orders[] = $admin_order_row;
    } elseif($admin_order_filter === 'completed' && $status_code === 'Completed') {
        $filtered_admin_orders[] = $admin_order_row;
    } elseif($admin_order_filter === 'canceled' && $status_code === 'Canceled') {
        $filtered_admin_orders[] = $admin_order_row;
    }
}
?>

<style>
.order-management-card {
    border: 1px solid #d2d6de;
    border-radius: 8px;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.order-management-card .box-body {
    padding: 18px 20px;
}

.order-filter-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 14px;
}

.order-advanced-filter-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 10px;
    margin-bottom: 14px;
}

.order-advanced-filter-item label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #4c5d73;
    margin-bottom: 5px;
}

.order-advanced-filter-actions {
    display: flex;
    align-items: flex-end;
    gap: 8px;
}

.order-advanced-filter-actions .btn {
    border-radius: 0 !important;
}

.order-filter-nav a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border: 1px solid #d2d6de;
    color: #444;
    background: #fff;
    text-decoration: none;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.order-filter-nav a:hover {
    background: #f4f4f4;
    border-color: #c7ccd3;
    color: #333;
}

.order-filter-nav a.active {
    background: #3c8dbc;
    border-color: #367fa9;
    color: #fff;
    box-shadow: none;
}

.order-filter-count {
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    color: #3c8dbc;
    background: #ecf5fb;
}

.order-filter-nav a.active .order-filter-count {
    color: #3c8dbc;
    background: #fff;
}

#example1 {
    margin-bottom: 0;
}

.admin-order-table {
    width: 100% !important;
    table-layout: fixed;
}

#example1 thead th {
    background: #f9fafc;
    border-color: #dfe4ea;
    color: #555;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    padding: 13px 12px;
    white-space: normal;
}

#example1 tbody td {
    border-color: #e6e9ee;
    padding: 14px 12px;
    vertical-align: top;
    word-break: break-word;
}

#example1 thead th:nth-child(1),
#example1 tbody td:nth-child(1) {
    width: 56px;
    white-space: nowrap;
}

#example1 thead th:nth-child(2),
#example1 tbody td:nth-child(2) {
    width: 17%;
}

#example1 thead th:nth-child(3),
#example1 tbody td:nth-child(3) {
    width: 19%;
}

#example1 thead th:nth-child(4),
#example1 tbody td:nth-child(4) {
    width: 18%;
}

#example1 thead th:nth-child(5),
#example1 tbody td:nth-child(5) {
    width: 10%;
    text-align: center;
    vertical-align: middle;
}

#example1 thead th:nth-child(6),
#example1 tbody td:nth-child(6),
#example1 thead th:nth-child(7),
#example1 tbody td:nth-child(7) {
    width: 12%;
}

#example1 thead th:nth-child(8),
#example1 tbody td:nth-child(8) {
    width: 12%;
}

#example1 tbody tr.order-row {
    transition: background-color 0.2s ease;
}

#example1 tbody tr.order-row:hover {
    background: #f5f8fb;
}

.order-no {
    font-weight: 700;
    color: #333;
}

.order-customer-code {
    font-size: 12px;
    color: #777;
    margin-bottom: 4px;
}

.order-customer-name {
    font-size: 14px;
    font-weight: 700;
    color: #333;
    margin-bottom: 2px;
}

.order-customer-email {
    font-size: 13px;
    color: #888;
    margin-bottom: 10px;
    white-space: normal;
    overflow-wrap: anywhere;
}

.order-product-item {
    padding: 10px 0;
    border-bottom: 1px dashed #dcdfe4;
}

.order-product-item:first-child {
    padding-top: 0;
}

.order-product-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.order-product-name {
    font-weight: 700;
    color: #333;
    margin-bottom: 4px;
}

.order-product-meta {
    font-size: 12px;
    color: #666;
    margin-bottom: 2px;
}

.order-payment-method {
    display: inline-block;
    font-size: 12px;
    font-weight: 700;
    color: #3c8dbc;
    background: #ecf5fb;
    border: 1px solid #cfe2ef;
    border-radius: 999px;
    padding: 4px 10px;
    margin-bottom: 8px;
}

.order-payment-detail {
    font-size: 12px;
    color: #555;
    margin-bottom: 3px;
    word-break: break-word;
}

.order-payment-total {
    font-size: 15px;
    font-weight: 700;
    color: #333;
    word-break: break-word;
    text-align: center;
}

.order-cell-status {
    text-align: center;
    vertical-align: middle !important;
}

.order-cell-status .status-badge {
    min-width: 112px;
    margin: 0 auto 8px;
}

.order-cell-status .btn-payment-complete,
.order-status-actions .btn {
    width: 170px;
    max-width: 100%;
    margin: 0 auto 6px;
    display: block;
}

.order-status-actions {
    max-width: 170px;
    margin: 0 auto;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    line-height: 1;
    padding: 6px 10px;
    margin-bottom: 8px;
}

.status-badge--success {
    color: #fff;
    background: #00a65a;
}

.status-badge--warning {
    color: #fff;
    background: #f39c12;
}

.status-badge--danger {
    color: #fff;
    background: #dd4b39;
}

.status-badge--pending {
    color: #fff;
    background: #777;
}

.order-status-actions form {
    margin: 0 0 6px;
}

.order-status-actions form:last-child {
    margin-bottom: 0;
}

.order-status-actions .btn {
    width: 100%;
    border-radius: 6px;
}

.order-actions .btn {
    width: 136px;
    max-width: 100%;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-bottom: 6px;
    font-weight: 600;
    font-size: 12px;
    padding: 6px 8px;
}

.order-actions {
    text-align: center;
    vertical-align: middle !important;
}

.order-actions .btn {
    margin-left: auto;
    margin-right: auto;
}

.order-actions .btn:last-child {
    margin-bottom: 0;
}

.btn-order-primary {
    color: #fff;
    background: #3c8dbc;
    border-color: #367fa9;
}

.btn-order-primary:hover,
.btn-order-primary:focus {
    color: #fff;
    background: #367fa9;
    border-color: #2f6f91;
}

.btn-order-danger-outline {
    color: #dd4b39;
    background: #fff;
    border: 1px solid #dd4b39;
}

.btn-order-danger-outline:hover,
.btn-order-danger-outline:focus {
    color: #fff;
    background: #dd4b39;
    border-color: #c23321;
}

/* Disable rounded corners on this page */
.order-management-card,
.order-filter-nav a,
.order-filter-count,
.order-payment-method,
.status-badge,
.order-status-actions .btn,
.order-actions .btn,
.btn-order-primary,
.btn-order-danger-outline {
    border-radius: 0 !important;
}

@media (max-width: 991px) {
    .order-management-card .box-body {
        padding: 14px;
    }

    #example1 tbody td {
        padding: 12px 10px;
    }
}
</style>

<section class="content-header">
	<div class="content-header-left">
        <h1>Xem đơn hàng</h1>
	</div>
</section>


<section class="content">

  <div class="row">
    <div class="col-md-12">


          <div class="box box-info order-management-card">
                <div class="box-body" style="padding-bottom:0;">
                        <form method="get" action="order.php" class="order-advanced-filter-form">
                            <input type="hidden" name="status" value="<?php echo htmlspecialchars($admin_order_filter, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="order-advanced-filter-item">
                                <label for="from_date">Từ ngày</label>
                                <input type="date" id="from_date" name="from_date" class="form-control" value="<?php echo htmlspecialchars($admin_order_from_date, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="order-advanced-filter-item">
                                <label for="to_date">Đến ngày</label>
                                <input type="date" id="to_date" name="to_date" class="form-control" value="<?php echo htmlspecialchars($admin_order_to_date, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="order-advanced-filter-item">
                                <label for="province_id">Tỉnh/Thành phố</label>
                                <select id="province_id" name="province_id" class="form-control select2 order-vn-province">
                                    <option value="">Chọn tỉnh/thành phố</option>
                                </select>
                            </div>
                            <div class="order-advanced-filter-item">
                                <label for="district_id">Quận/Huyện</label>
                                <select id="district_id" name="district_id" class="form-control select2 order-vn-district">
                                    <option value="">Chọn quận/huyện</option>
                                </select>
                            </div>
                            <div class="order-advanced-filter-item">
                                <label for="ward_id">Phường/Xã</label>
                                <select id="ward_id" name="ward_id" class="form-control select2 order-vn-ward">
                                    <option value="">Chọn phường/xã</option>
                                </select>
                            </div>

                            <input type="hidden" name="city" class="order-vn-province-name" value="<?php echo htmlspecialchars($admin_order_city, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="district" class="order-vn-district-name" value="<?php echo htmlspecialchars($admin_order_district, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="ward" class="order-vn-ward-name" value="<?php echo htmlspecialchars($admin_order_ward, ENT_QUOTES, 'UTF-8'); ?>">

                            <div class="order-advanced-filter-actions">
                                <button type="submit" class="btn btn-primary btn-sm">Lọc</button>
                                <a href="order.php" class="btn btn-default btn-sm">Xóa lọc</a>
                            </div>
                        </form>
                        <div class="order-filter-nav">
                    <a href="<?php echo build_admin_order_filter_url('all', $admin_order_base_params); ?>" class="<?php echo ($admin_order_filter === 'all') ? 'active' : ''; ?>"><span>Tất cả</span><span class="order-filter-count"><?php echo $admin_order_counts['all']; ?></span></a>
                    <a href="<?php echo build_admin_order_filter_url('pending', $admin_order_base_params); ?>" class="<?php echo ($admin_order_filter === 'pending') ? 'active' : ''; ?>"><span>Đơn mới</span><span class="order-filter-count"><?php echo $admin_order_counts['pending']; ?></span></a>
                    <a href="<?php echo build_admin_order_filter_url('shipping', $admin_order_base_params); ?>" class="<?php echo ($admin_order_filter === 'shipping') ? 'active' : ''; ?>"><span>Đang giao</span><span class="order-filter-count"><?php echo $admin_order_counts['shipping']; ?></span></a>
                    <a href="<?php echo build_admin_order_filter_url('completed', $admin_order_base_params); ?>" class="<?php echo ($admin_order_filter === 'completed') ? 'active' : ''; ?>"><span>Đã giao</span><span class="order-filter-count"><?php echo $admin_order_counts['completed']; ?></span></a>
                    <a href="<?php echo build_admin_order_filter_url('canceled', $admin_order_base_params); ?>" class="<?php echo ($admin_order_filter === 'canceled') ? 'active' : ''; ?>"><span>Đã hủy</span><span class="order-filter-count"><?php echo $admin_order_counts['canceled']; ?></span></a>
                        </div>
                </div>
        
        <div class="box-body table-responsive">
          <table id="example1" class="table table-bordered table-hover admin-order-table">
			<thead>
			    <tr>
			        <th>#</th>
                    <th>Khách hàng</th>
                    <th>Chi tiết sản phẩm</th>
                    <th>
                        Thông tin thanh toán
                    </th>
                    <th>Số tiền đã thanh toán</th>
                    <th>Trạng thái thanh toán</th>
                    <th>Trạng thái giao hàng</th>
                    <th>Thao tác</th>
			    </tr>
			</thead>
            <tbody>
            	<?php
                $i=0;
                foreach ($filtered_admin_orders as $row) {
            		$i++;
            		?>
					<tr class="order-row">
	                    <td><span class="order-no">#<?php echo $i; ?></span></td>
	                    <td>
                       <div class="order-customer-code">Mã KH: #<?php echo $row['customer_id']; ?></div>
                       <div class="order-customer-name"><?php echo $row['customer_name']; ?></div>
                       <div class="order-customer-email"><?php echo $row['customer_email']; ?></div>
                       <div class="order-customer-code">Phường/Xã: <?php echo htmlspecialchars((string)($row['delivery_ward'] !== '' ? $row['delivery_ward'] : 'Chưa cập nhật'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </td>
                        <td>
                           <?php
                           $statement1 = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id=?");
                           $statement1->execute(array($row['payment_id']));
                           $result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);
                           foreach ($result1 as $row1) {
                                echo '<div class="order-product-item">';
                                echo '<div class="order-product-name">'.$row1['product_name'].'</div>';
                                echo '<div class="order-product-meta">Kích cỡ: '.$row1['size'].' | Màu sắc: '.$row1['color'].'</div>';
                              echo '<div class="order-product-meta">Số lượng: '.$row1['quantity'].'</div>';
                                echo '</div>';
                           }
                           ?>
                        </td>
                        <td>
                            	<?php if($row['payment_method'] == 'Cash On Delivery'): ?>
                                <div class="order-payment-method">Thanh toán khi nhận hàng (COD)</div>
                                <div class="order-payment-detail"><b>Mã thanh toán:</b> <?php echo $row['payment_id']; ?></div>
                                <div class="order-payment-detail"><b>Ngày:</b> <?php echo $row['payment_date']; ?></div>
                            <?php else: ?>
								<div class="order-payment-method"><?php echo format_payment_method_vi($row['payment_method']); ?></div>
                                <div class="order-payment-detail"><b>Mã thanh toán:</b> <?php echo $row['payment_id']; ?></div>
                                <div class="order-payment-detail"><b>Ngày:</b> <?php echo $row['payment_date']; ?></div>
                            		<?php if(!empty($row['bank_transaction_info'])): ?>
                            			<div class="order-payment-detail"><b>Thông tin giao dịch:</b> <?php echo $row['bank_transaction_info']; ?></div>
                            		<?php endif; ?>
                            		<?php if(!empty($row['card_number'])): ?>
                            			<div class="order-payment-detail"><b>Số thẻ:</b> <?php echo $row['card_number']; ?></div>
                            		<?php endif; ?>
                            		<?php if(!empty($row['card_cvv'])): ?>
                            			<div class="order-payment-detail"><b>Mã CVV:</b> <?php echo $row['card_cvv']; ?></div>
                            		<?php endif; ?>
                            		<?php if(!empty($row['card_month'])): ?>
                            			<div class="order-payment-detail"><b>Tháng hết hạn:</b> <?php echo $row['card_month']; ?></div>
                            		<?php endif; ?>
                            		<?php if(!empty($row['card_year'])): ?>
                            			<div class="order-payment-detail"><b>Năm hết hạn:</b> <?php echo $row['card_year']; ?></div>
                            		<?php endif; ?>
                        	<?php endif; ?>
                        </td>
                        <td><div class="order-payment-total"><?php echo format_price_vnd($row['paid_amount']); ?></div></td>
                        <td class="order-cell-status">
							<?php
                                $payment_status_badge_class = ($row['payment_status'] === 'Completed') ? 'status-badge--success' : 'status-badge--pending';
                            ?>
                            <div class="status-badge <?php echo $payment_status_badge_class; ?>"><?php echo format_payment_status_vi($row['payment_status']); ?></div>
                            <?php
                                if($row['payment_status']=='Pending' && $row['payment_method'] !== 'Cash On Delivery'){
                                    ?>
                                <a href="order-change-status.php?id=<?php echo $row['id']; ?>&task=Completed" class="btn btn-success btn-sm btn-payment-complete">Đánh dấu đã thanh toán</a>
                                    <?php
                                }
                            ?>
                        </td>
                        <td class="order-cell-status">
                            <?php
                                $shipping_status_value = normalize_shipping_status_code($row['shipping_status']);
                                $shipping_badge_class = 'status-badge--pending';
                                $shipping_status_label = 'Đơn mới';
                                $show_shipping_badge = ($shipping_status_value !== 'Pending' && $shipping_status_value !== 'Shipping');
                                if($shipping_status_value === 'Shipping') {
                                    $shipping_badge_class = 'status-badge--warning';
                                    $shipping_status_label = 'Đang giao';
                                } elseif($shipping_status_value === 'Completed') {
                                    $shipping_badge_class = 'status-badge--success';
                                    $shipping_status_label = 'Đã giao';
                                } elseif($shipping_status_value === 'Canceled') {
                                    $shipping_badge_class = 'status-badge--danger';
                                    $shipping_status_label = 'Đã hủy';
                                }
                            ?>
                            <?php if($show_shipping_badge): ?>
                                <div class="status-badge <?php echo $shipping_badge_class; ?>"><?php echo $shipping_status_label; ?></div>
                            <?php endif; ?>

                            <?php if($shipping_status_value !== 'Completed' && $shipping_status_value !== 'Canceled'): ?>
                                <?php
                                    $next_shipping_status = '';
                                    $next_shipping_label = '';
                                    if($shipping_status_value === 'Pending') {
                                        $next_shipping_status = 'Shipping';
                                        $next_shipping_label = 'Bắt đầu giao';
                                    } elseif($shipping_status_value === 'Shipping') {
                                        $next_shipping_status = 'Completed';
                                        $next_shipping_label = 'Đánh dấu đã giao';
                                    }
                                ?>

                                <div class="order-status-actions">
                                <?php if($next_shipping_status !== ''): ?>
                                    <form action="order-shipping-status-update.php" method="post">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="shipping_status" value="<?php echo $next_shipping_status; ?>">
                                        <button type="submit" class="btn btn-warning btn-sm"><?php echo $next_shipping_label; ?></button>
                                    </form>
                                <?php endif; ?>

                                <form action="order-shipping-status-update.php" method="post">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="shipping_status" value="Canceled">
                                    <button type="submit" class="btn btn-default btn-sm">Hủy đơn</button>
                                </form>
                                </div>
                            <?php endif; ?>
                        </td>
	                    <td class="order-actions">
                            <a href="order-detail.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-order-primary"><i class="fa fa-eye"></i><span>Xem chi tiết</span></a>
                            <a href="#" class="btn btn-sm btn-order-danger-outline" data-href="order-delete.php?id=<?php echo $row['id']; ?>" data-toggle="modal" data-target="#confirm-delete"><i class="fa fa-trash"></i><span>Xóa</span></a>
	                    </td>
	                </tr>
            		<?php
            	}
            	?>
            </tbody>
          </table>
        </div>
      </div>
  

</section>


<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Xác nhận xóa</h4>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn xóa mục này không?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                <a class="btn btn-danger btn-ok">Xóa</a>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        var provinceSelect = document.querySelector('.order-vn-province');
        var districtSelect = document.querySelector('.order-vn-district');
        var wardSelect = document.querySelector('.order-vn-ward');

        if (!provinceSelect || !districtSelect || !wardSelect) {
            return;
        }

        var provinceNameInput = document.querySelector('.order-vn-province-name');
        var districtNameInput = document.querySelector('.order-vn-district-name');
        var wardNameInput = document.querySelector('.order-vn-ward-name');

        var selectedProvinceId = '<?php echo (int)$admin_order_province_id; ?>';
        var selectedDistrictId = '<?php echo (int)$admin_order_district_id; ?>';
        var selectedWardId = '<?php echo (int)$admin_order_ward_id; ?>';

        if (String(selectedProvinceId) === '0') {
            selectedProvinceId = '';
        }
        if (String(selectedDistrictId) === '0') {
            selectedDistrictId = '';
        }
        if (String(selectedWardId) === '0') {
            selectedWardId = '';
        }

        var vnTree = [];

        function refreshSelect2(selectEl) {
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                window.jQuery(selectEl).trigger('change.select2');
            }
        }

        function resetSelect(selectEl, placeholder) {
            selectEl.innerHTML = '';
            var option = document.createElement('option');
            option.value = '';
            option.textContent = placeholder;
            selectEl.appendChild(option);
            selectEl.value = '';
            refreshSelect2(selectEl);
        }

        function fillSelect(selectEl, items, placeholder, valueKey, textKey, selectedValue) {
            resetSelect(selectEl, placeholder);
            items.forEach(function(item) {
                var option = document.createElement('option');
                option.value = item[valueKey];
                option.textContent = item[textKey];
                if (String(item[valueKey]) === String(selectedValue)) {
                    option.selected = true;
                }
                selectEl.appendChild(option);
            });
            refreshSelect2(selectEl);
        }

        function updateSelectedNames() {
            if (provinceNameInput) {
                provinceNameInput.value = provinceSelect.selectedIndex > 0 ? provinceSelect.options[provinceSelect.selectedIndex].text : '';
            }
            if (districtNameInput) {
                districtNameInput.value = districtSelect.selectedIndex > 0 ? districtSelect.options[districtSelect.selectedIndex].text : '';
            }
            if (wardNameInput) {
                wardNameInput.value = wardSelect.selectedIndex > 0 ? wardSelect.options[wardSelect.selectedIndex].text : '';
            }
        }

        function fetchWithTimeout(url, timeoutMs) {
            return Promise.race([
                fetch(url, { cache: 'no-store' }),
                new Promise(function(_, reject) {
                    setTimeout(function() { reject(new Error('timeout')); }, timeoutMs);
                })
            ]);
        }

        function fetchVnTree() {
            var endpoints = [
                'https://provinces.open-api.vn/api/?depth=3',
                'https://provinces.open-api.vn/api/p/?depth=3'
            ];

            var chain = Promise.reject();
            endpoints.forEach(function(endpoint) {
                chain = chain.catch(function() {
                    return fetchWithTimeout(endpoint, 12000).then(function(response) {
                        if (!response.ok) {
                            throw new Error('HTTP ' + response.status);
                        }
                        return response.json();
                    });
                });
            });

            return chain;
        }

        function getSelectedProvince() {
            return vnTree.find(function(item) {
                return String(item.code) === String(provinceSelect.value);
            });
        }

        function getSelectedDistrict() {
            var province = getSelectedProvince();
            if (!province || !Array.isArray(province.districts)) {
                return null;
            }

            return province.districts.find(function(item) {
                return String(item.code) === String(districtSelect.value);
            }) || null;
        }

        function populateDistricts(selectedValue) {
            var province = getSelectedProvince();
            var districts = province && Array.isArray(province.districts) ? province.districts : [];
            fillSelect(districtSelect, districts, 'Chọn quận/huyện', 'code', 'name', selectedValue || '');
        }

        function populateWards(selectedValue) {
            var district = getSelectedDistrict();
            var wards = district && Array.isArray(district.wards) ? district.wards : [];
            fillSelect(wardSelect, wards, 'Chọn phường/xã', 'code', 'name', selectedValue || '');
        }

        provinceSelect.addEventListener('change', function() {
            selectedProvinceId = provinceSelect.value;
            selectedDistrictId = '';
            selectedWardId = '';
            populateDistricts('');
            populateWards('');
            updateSelectedNames();
        });

        districtSelect.addEventListener('change', function() {
            selectedDistrictId = districtSelect.value;
            selectedWardId = '';
            populateWards('');
            updateSelectedNames();
        });

        wardSelect.addEventListener('change', updateSelectedNames);

        fetchVnTree()
            .then(function(data) {
                vnTree = Array.isArray(data) ? data : [];
                fillSelect(provinceSelect, vnTree, 'Chọn tỉnh/thành phố', 'code', 'name', selectedProvinceId);
                populateDistricts(selectedDistrictId);
                populateWards(selectedWardId);
                updateSelectedNames();
            })
            .catch(function() {
                resetSelect(provinceSelect, 'Không tải được dữ liệu tỉnh/thành');
                resetSelect(districtSelect, 'Không tải được dữ liệu quận/huyện');
                resetSelect(wardSelect, 'Không tải được dữ liệu phường/xã');
                updateSelectedNames();
            });
    });
</script>


<?php require_once('footer.php'); ?>
