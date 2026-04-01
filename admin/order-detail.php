<?php require_once('header.php'); ?>

<?php
if(!isset($_GET['id'])) {
	safe_redirect('order.php');
}

$order_id = (int)$_GET['id'];
if($order_id <= 0) {
	safe_redirect('order.php');
}

$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE id=?");
$statement->execute(array($order_id));
$payment = $statement->fetch(PDO::FETCH_ASSOC);
if(!$payment) {
	safe_redirect('order.php');
}

$customer = null;
$statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id=? LIMIT 1");
$statement->execute(array((int)$payment['customer_id']));
$customer = $statement->fetch(PDO::FETCH_ASSOC);

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
$receiver_phone = '';
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

if($customer) {
	if(isset($customer['cust_s_name']) && trim((string)$customer['cust_s_name']) !== '') {
		$receiver_name = trim((string)$customer['cust_s_name']);
	}
	if($receiver_phone === '' && isset($customer['cust_s_phone']) && trim((string)$customer['cust_s_phone']) !== '') {
		$receiver_phone = trim((string)$customer['cust_s_phone']);
	} elseif($receiver_phone === '' && isset($customer['cust_phone'])) {
		$receiver_phone = trim((string)$customer['cust_phone']);
	}

	if($address_line === '') {
		$address_line = isset($customer['cust_s_address']) ? trim((string)$customer['cust_s_address']) : '';
	}
	if($city === '') {
		$city = isset($customer['cust_s_city']) ? trim((string)$customer['cust_s_city']) : '';
	}
	if($state === '') {
		$state = isset($customer['cust_s_state']) ? trim((string)$customer['cust_s_state']) : '';
	}
	if($zip === '') {
		$zip = isset($customer['cust_s_zip']) ? trim((string)$customer['cust_s_zip']) : '';
	}

	if($address_line === '' && isset($customer['cust_address'])) {
		$address_line = trim((string)$customer['cust_address']);
	}
	if($city === '' && isset($customer['cust_city'])) {
		$city = trim((string)$customer['cust_city']);
	}
	if($state === '' && isset($customer['cust_state'])) {
		$state = trim((string)$customer['cust_state']);
	}
	if($zip === '' && isset($customer['cust_zip'])) {
		$zip = trim((string)$customer['cust_zip']);
	}
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

$items_subtotal = 0;
foreach($order_items as $item) {
	$qty = isset($item['quantity']) ? (int)$item['quantity'] : 0;
	$unit_price = isset($item['unit_price']) ? (float)$item['unit_price'] : 0;
	$items_subtotal += ($qty * $unit_price);
}

$order_total_display = isset($payment['order_total_amount']) ? (float)$payment['order_total_amount'] : (float)$payment['paid_amount'];
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Chi tiết đơn hàng</h1>
	</div>
	<div class="content-header-right">
		<a href="order.php" class="btn btn-primary btn-sm">Quay lại</a>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
				<div class="box-header with-border">
					<h3 class="box-title">Thông tin khách hàng</h3>
				</div>
				<div class="box-body">
					<table class="table table-bordered" style="margin-bottom:0;">
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
					</table>
				</div>
			</div>

			<div class="box box-info">
				<div class="box-header with-border">
					<h3 class="box-title">Sản phẩm</h3>
				</div>
				<div class="box-body table-responsive">
					<table class="table table-bordered table-hover">
						<thead>
							<tr>
								<th style="width:50px;">#</th>
								<th>Sản phẩm</th>
								<th style="width:160px;">Size</th>
								<th style="width:160px;">Màu</th>
								<th style="width:120px;">Số lượng</th>
								<th style="width:160px;">Đơn giá</th>
								<th style="width:170px;">Thành tiền</th>
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

			<div class="box box-info">
				<div class="box-header with-border">
					<h3 class="box-title">Tổng quan</h3>
				</div>
				<div class="box-body">
					<table class="table table-bordered" style="margin-bottom:0;">
						<tr>
							<th style="width:220px;">Phương thức thanh toán</th>
							<td><?php echo htmlspecialchars(format_payment_method_vi($payment['payment_method']), ENT_QUOTES, 'UTF-8'); ?></td>
						</tr>
						<tr>
							<th>Trạng thái giao hàng</th>
							<td><?php echo htmlspecialchars(format_shipping_status_vi($payment['shipping_status']), ENT_QUOTES, 'UTF-8'); ?></td>
						</tr>
						<tr>
							<th>Trạng thái thanh toán</th>
							<td><?php echo htmlspecialchars(format_payment_status_vi($payment['payment_status']), ENT_QUOTES, 'UTF-8'); ?></td>
						</tr>
						<tr>
							<th>Tổng tiền</th>
							<td><?php echo format_price_vnd($order_total_display); ?></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>

<?php require_once('footer.php'); ?>
