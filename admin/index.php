<?php require_once('header.php'); ?>

<section class="content-header">
	<h1>Tổng quan</h1>
</section>

<?php
$count_query = function($sql, $params = array()) use ($pdo) {
	$stmt = $pdo->prepare($sql);
	$stmt->execute($params);
	return (int)$stmt->fetchColumn();
};

$scalar_query = function($sql, $params = array()) use ($pdo) {
	$stmt = $pdo->prepare($sql);
	$stmt->execute($params);
	return $stmt->fetchColumn();
};

$total_top_category = $count_query("SELECT COUNT(*) FROM tbl_top_category");
$total_mid_category = $count_query("SELECT COUNT(*) FROM tbl_mid_category");
$total_end_category = $count_query("SELECT COUNT(*) FROM tbl_end_category");
$total_category = $total_top_category + $total_mid_category + $total_end_category;

$total_product = $count_query("SELECT COUNT(*) FROM tbl_product");
$total_customers = $count_query("SELECT COUNT(*) FROM tbl_customer WHERE cust_status = ?", array(1));
$available_shipping = $count_query("SELECT COUNT(*) FROM tbl_shipping_cost");

$total_order_all = $count_query("SELECT COUNT(*) FROM tbl_payment");
$total_order_completed = $count_query("SELECT COUNT(*) FROM tbl_payment WHERE payment_status = ?", array('Completed'));
$total_order_pending = $count_query("SELECT COUNT(*) FROM tbl_payment WHERE payment_status = ?", array('Pending'));
$total_shipping_completed = $count_query("SELECT COUNT(*) FROM tbl_payment WHERE shipping_status = ?", array('Completed'));
$total_order_complete_shipping_pending = $count_query(
	"SELECT COUNT(*) FROM tbl_payment WHERE payment_status = ? AND shipping_status = ?",
	array('Completed', 'Pending')
);

$total_revenue_completed = (int)$scalar_query(
	"SELECT COALESCE(SUM(CASE WHEN payment_status = 'Completed' THEN paid_amount ELSE 0 END), 0) FROM tbl_payment"
);

$payment_completion_rate = ($total_order_all > 0)
	? round(($total_order_completed / $total_order_all) * 100, 1)
	: 0;

$shipping_completion_rate = ($total_order_completed > 0)
	? round(($total_shipping_completed / $total_order_completed) * 100, 1)
	: 0;

$pending_ratio = ($total_order_all > 0)
	? round(($total_order_pending / $total_order_all) * 100, 1)
	: 0;
?>

<style>
.admin-dashboard-overview {
	padding-top: 4px;
}

.dashboard-overview-head {
	display: flex;
	justify-content: space-between;
	align-items: flex-end;
	gap: 10px;
	margin-bottom: 14px;
	padding: 14px 16px;
	border: 1px solid #d8e3f1;
	background: linear-gradient(135deg, #f7fbff 0%, #edf4fd 100%);
}

.dashboard-overview-head h2 {
	margin: 0 0 4px;
	font-size: 22px;
	font-weight: 700;
	color: #1f3653;
}

.dashboard-overview-head p {
	margin: 0;
	font-size: 13px;
	color: #4e6785;
}

.dashboard-updated-at {
	font-size: 12px;
	font-weight: 600;
	color: #35577f;
	white-space: nowrap;
}

.overview-kpi-card {
	position: relative;
	min-height: 140px;
	margin-bottom: 16px;
	padding: 16px;
	border: 1px solid #d9e4f2;
	background: #fff;
	box-shadow: 0 4px 10px rgba(18, 45, 88, 0.06);
	overflow: hidden;
}

.overview-kpi-card:after {
	content: '';
	position: absolute;
	left: 0;
	bottom: 0;
	width: 100%;
	height: 4px;
	background: #3c8dbc;
}

.overview-kpi-card .kpi-title {
	font-size: 12px;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.4px;
	color: #547096;
	margin-bottom: 8px;
}

.overview-kpi-card .kpi-value {
	font-size: 34px;
	line-height: 1;
	font-weight: 700;
	color: #1f3653;
	margin-bottom: 8px;
}

.overview-kpi-card .kpi-sub {
	font-size: 13px;
	color: #4f6784;
}

.overview-kpi-card .kpi-icon {
	position: absolute;
	right: 14px;
	top: 14px;
	font-size: 28px;
	color: rgba(60, 141, 188, 0.22);
}

.overview-kpi-card.kpi-warning:after {
	background: #f39c12;
}

.overview-kpi-card.kpi-success:after {
	background: #00a65a;
}

.overview-kpi-card.kpi-danger:after {
	background: #dd4b39;
}

.overview-detail-card {
	margin-bottom: 16px;
	padding: 16px;
	border: 1px solid #d9e4f2;
	background: #fff;
	box-shadow: 0 4px 10px rgba(18, 45, 88, 0.06);
}

.overview-detail-card h3 {
	margin: 0 0 12px;
	font-size: 16px;
	font-weight: 700;
	color: #1f3653;
}

.overview-metric-row {
	margin-bottom: 10px;
}

.overview-metric-row:last-child {
	margin-bottom: 0;
}

.overview-metric-label {
	display: flex;
	justify-content: space-between;
	font-size: 13px;
	font-weight: 600;
	color: #365579;
	margin-bottom: 5px;
}

.overview-metric-value {
	color: #1f3653;
}

.overview-metric-bar {
	height: 8px;
	background: #e8eef6;
	border-radius: 999px;
	overflow: hidden;
}

.overview-metric-bar span {
	display: block;
	height: 100%;
	background: linear-gradient(90deg, #2f7fb8 0%, #49a4df 100%);
}

.overview-list {
	margin: 0;
	padding: 0;
	list-style: none;
}

.overview-list li {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 8px 0;
	border-bottom: 1px dashed #d6e2f0;
	font-size: 13px;
	color: #3f5f83;
}

.overview-list li:last-child {
	border-bottom: 0;
	padding-bottom: 0;
}

.overview-list li strong {
	font-size: 18px;
	line-height: 1;
	color: #1f3653;
}

.overview-actions a {
	display: block;
	margin-bottom: 8px;
	border-radius: 0;
	text-align: left;
	font-weight: 600;
	font-size: 13px;
}

.overview-actions a:last-child {
	margin-bottom: 0;
}

@media (max-width: 767px) {
	.dashboard-overview-head {
		flex-direction: column;
		align-items: flex-start;
	}

	.overview-kpi-card {
		min-height: 128px;
	}

	.overview-kpi-card .kpi-value {
		font-size: 30px;
	}
}
</style>

<section class="content admin-dashboard-overview">
	<div class="dashboard-overview-head">
		<div>
			<h2>Bảng điều khiển vận hành</h2>
			<p>Tập trung các chỉ số quan trọng của đơn hàng, sản phẩm và người dùng.</p>
		</div>
		<div class="dashboard-updated-at">Cập nhật: <?php echo date('d/m/Y H:i'); ?></div>
	</div>

	<div class="row">
		<div class="col-lg-3 col-sm-6">
			<div class="overview-kpi-card kpi-warning">
				<div class="kpi-icon"><i class="fa fa-hourglass-half"></i></div>
				<div class="kpi-title">Đơn hàng chờ xử lý</div>
				<div class="kpi-value"><?php echo number_format($total_order_pending); ?></div>
				<div class="kpi-sub">Chiếm <?php echo $pending_ratio; ?>% tổng đơn</div>
			</div>
		</div>
		<div class="col-lg-3 col-sm-6">
			<div class="overview-kpi-card kpi-success">
				<div class="kpi-icon"><i class="fa fa-check-circle"></i></div>
				<div class="kpi-title">Đơn đã thanh toán</div>
				<div class="kpi-value"><?php echo number_format($total_order_completed); ?></div>
				<div class="kpi-sub">Doanh thu: <?php echo format_price_vnd($total_revenue_completed); ?></div>
			</div>
		</div>
		<div class="col-lg-3 col-sm-6">
			<div class="overview-kpi-card">
				<div class="kpi-icon"><i class="fa fa-truck"></i></div>
				<div class="kpi-title">Giao hàng chờ xử lý</div>
				<div class="kpi-value"><?php echo number_format($total_order_complete_shipping_pending); ?></div>
				<div class="kpi-sub">Đơn đã thanh toán chưa giao xong</div>
			</div>
		</div>
		<div class="col-lg-3 col-sm-6">
			<div class="overview-kpi-card kpi-danger">
				<div class="kpi-icon"><i class="fa fa-users"></i></div>
				<div class="kpi-title">Người dùng hoạt động</div>
				<div class="kpi-value"><?php echo number_format($total_customers); ?></div>
				<div class="kpi-sub">Tài khoản đang được kích hoạt</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-lg-8">
			<div class="overview-detail-card">
				<h3>Hiệu suất vận hành</h3>
				<div class="overview-metric-row">
					<div class="overview-metric-label">
						<span>Tỷ lệ thanh toán thành công</span>
						<span class="overview-metric-value"><?php echo $payment_completion_rate; ?>%</span>
					</div>
					<div class="overview-metric-bar"><span style="width: <?php echo min(100, $payment_completion_rate); ?>%;"></span></div>
				</div>
				<div class="overview-metric-row">
					<div class="overview-metric-label">
						<span>Tỷ lệ giao hàng hoàn tất (trên đơn đã thanh toán)</span>
						<span class="overview-metric-value"><?php echo $shipping_completion_rate; ?>%</span>
					</div>
					<div class="overview-metric-bar"><span style="width: <?php echo min(100, $shipping_completion_rate); ?>%;"></span></div>
				</div>
			</div>

			<div class="overview-detail-card">
				<h3>Tổng quan danh mục và kho sản phẩm</h3>
				<ul class="overview-list">
					<li><span>Tổng sản phẩm</span><strong><?php echo number_format($total_product); ?></strong></li>
					<li><span>Tổng danh mục (3 cấp)</span><strong><?php echo number_format($total_category); ?></strong></li>
					<li><span>Danh mục cấp 1 / 2 / 3</span><strong><?php echo number_format($total_top_category); ?> / <?php echo number_format($total_mid_category); ?> / <?php echo number_format($total_end_category); ?></strong></li>
					<li><span>Tuyến giao hàng khả dụng</span><strong><?php echo number_format($available_shipping); ?></strong></li>
				</ul>
			</div>
		</div>

		<div class="col-lg-4">
			<div class="overview-detail-card">
				<h3>Điều hướng nhanh</h3>
				<div class="overview-actions">
					<a href="order.php" class="btn btn-primary btn-sm"><i class="fa fa-sticky-note"></i> Quản lý đơn hàng</a>
					<a href="product.php" class="btn btn-success btn-sm"><i class="fa fa-shopping-bag"></i> Quản lý sản phẩm</a>
					<a href="customer.php" class="btn btn-info btn-sm"><i class="fa fa-user"></i> Quản lý người dùng</a>
					<a href="shipping-cost.php" class="btn btn-warning btn-sm"><i class="fa fa-truck"></i> Cấu hình phí vận chuyển</a>
				</div>
			</div>

			<div class="overview-detail-card">
				<h3>Trạng thái đơn hàng</h3>
				<ul class="overview-list">
					<li><span>Tổng đơn hàng</span><strong><?php echo number_format($total_order_all); ?></strong></li>
					<li><span>Đơn chờ xử lý</span><strong><?php echo number_format($total_order_pending); ?></strong></li>
					<li><span>Đơn thanh toán hoàn tất</span><strong><?php echo number_format($total_order_completed); ?></strong></li>
					<li><span>Giao hàng hoàn tất</span><strong><?php echo number_format($total_shipping_completed); ?></strong></li>
				</ul>
			</div>
		</div>
	</div>
</section>

<?php require_once('footer.php'); ?>
