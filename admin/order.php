<?php require_once('header.php'); ?>

<?php
$error_message = '';
if(isset($_POST['form1'])) {
    $valid = 1;
    if(empty($_POST['subject_text'])) {
        $valid = 0;
        $error_message .= 'Tiêu đề không được để trống\n';
    }
    if(empty($_POST['message_text'])) {
        $valid = 0;
        $error_message .= 'Nội dung không được để trống\n';
    }
    if($valid == 1) {

        $subject_text = strip_tags($_POST['subject_text']);
        $message_text = strip_tags($_POST['message_text']);

        // Getting Customer Email Thêmress
        $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id=?");
        $statement->execute(array($_POST['cust_id']));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
        foreach ($result as $row) {
            $cust_email = $row['cust_email'];
        }

        // Getting Admin Email Thêmress
        $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
        foreach ($result as $row) {
            $admin_email = $row['contact_email'];
        }

        $order_detail = '';
        $statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE payment_id=?");
        $statement->execute(array($_POST['payment_id']));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
        foreach ($result as $row) {
        	
            if($row['payment_method'] == 'Cash On Delivery'):
                $payment_details = 'Thanh toán khi nhận hàng (COD)';
            elseif(!empty($row['bank_transaction_info'])):
                $payment_details = 'Chi tiết giao dịch: <br>'.$row['bank_transaction_info'];
            elseif(!empty($row['card_number']) || !empty($row['card_cvv']) || !empty($row['card_month']) || !empty($row['card_year'])):
                $payment_details = '
Số thẻ: '.$row['card_number'].'<br>
Mã CVV: '.$row['card_cvv'].'<br>
Tháng hết hạn: '.$row['card_month'].'<br>
Năm hết hạn: '.$row['card_year'].'<br>
                ';
            else:
                $payment_details = 'Phương thức thanh toán: '.$row['payment_method'];
        	endif;

            $order_detail .= '
Tên khách hàng: '.$row['customer_name'].'<br>
Email khách hàng: '.$row['customer_email'].'<br>
Phương thức thanh toán: '.format_payment_method_vi($row['payment_method']).'<br>
Ngày thanh toán: '.$row['payment_date'].'<br>
Số tiền đã thanh toán: '.format_price_vnd($row['paid_amount']).'<br>
Trạng thái thanh toán: '.format_payment_status_vi($row['payment_status']).'<br>
Trạng thái giao hàng: '.format_shipping_status_vi($row['shipping_status']).'<br>
Mã thanh toán: '.$row['payment_id'].'<br>
            ';
        }

        $i=0;
        $statement = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id=?");
        $statement->execute(array($_POST['payment_id']));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
        foreach ($result as $row) {
            $i++;
            $order_detail .= '
<br><b><u>Sản phẩm '.$i.'</u></b><br>
Tên sản phẩm: '.$row['product_name'].'<br>
Kích cỡ: '.$row['size'].'<br>
Màu sắc: '.$row['color'].'<br>
Số lượng: '.$row['quantity'].'<br>
            ';
        }

        $statement = $pdo->prepare("INSERT INTO tbl_customer_message (subject,message,order_detail,cust_id) VALUES (?,?,?,?)");
        $statement->execute(array($subject_text,$message_text,$order_detail,$_POST['cust_id']));

        // sending email
        $to_customer = $cust_email;
        $message = '
<html><body>
    <h3>Nội dung tin nhắn: </h3>
'.$message_text.'
    <h3>Chi tiết đơn hàng: </h3>
'.$order_detail.'
</body></html>
';
        $headers = 'From: ' . $admin_email . "\r\n" .
                   'Reply-To: ' . $admin_email . "\r\n" .
                   'X-Mailer: PHP/' . phpversion() . "\r\n" . 
                   "MIME-Version: 1.0\r\n" . 
                   "Content-Type: text/html; charset=UTF-8\r\n";

        // Sending email to admin                  
        mail($to_customer, $subject_text, $message, $headers);
        
        $success_message = 'Đã gửi email cho khách hàng thành công.';

    }
}
?>
<?php
if($error_message != '') {
    echo "<script>alert('".$error_message."')</script>";
}
if($success_message != '') {
    echo "<script>alert('".$success_message."')</script>";
}

$admin_order_filter = isset($_GET['status']) ? trim((string)$_GET['status']) : 'all';
$allowed_admin_filters = array('all', 'pending', 'shipping', 'completed', 'canceled');
if(!in_array($admin_order_filter, $allowed_admin_filters, true)) {
    $admin_order_filter = 'all';
}

$statement = $pdo->prepare("SELECT * FROM tbl_payment ORDER BY id DESC");
$statement->execute();
$all_admin_orders = $statement->fetchAll(PDO::FETCH_ASSOC);

$admin_order_counts = array(
    'all' => count($all_admin_orders),
    'pending' => 0,
    'shipping' => 0,
    'completed' => 0,
    'canceled' => 0
);
$filtered_admin_orders = array();

foreach($all_admin_orders as $admin_order_row) {
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
    margin-bottom: 10px;
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

.btn-message-customer {
    width: 100%;
    border-radius: 6px;
    margin-bottom: 4px;
    font-size: 12px;
    padding: 6px 8px;
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
.btn-message-customer,
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
                        <div class="order-filter-nav">
                    <a href="order.php?status=all" class="<?php echo ($admin_order_filter === 'all') ? 'active' : ''; ?>"><span>Tất cả</span><span class="order-filter-count"><?php echo $admin_order_counts['all']; ?></span></a>
                    <a href="order.php?status=pending" class="<?php echo ($admin_order_filter === 'pending') ? 'active' : ''; ?>"><span>Đơn mới</span><span class="order-filter-count"><?php echo $admin_order_counts['pending']; ?></span></a>
                    <a href="order.php?status=shipping" class="<?php echo ($admin_order_filter === 'shipping') ? 'active' : ''; ?>"><span>Đang giao</span><span class="order-filter-count"><?php echo $admin_order_counts['shipping']; ?></span></a>
                    <a href="order.php?status=completed" class="<?php echo ($admin_order_filter === 'completed') ? 'active' : ''; ?>"><span>Đã giao</span><span class="order-filter-count"><?php echo $admin_order_counts['completed']; ?></span></a>
                    <a href="order.php?status=canceled" class="<?php echo ($admin_order_filter === 'canceled') ? 'active' : ''; ?>"><span>Đã hủy</span><span class="order-filter-count"><?php echo $admin_order_counts['canceled']; ?></span></a>
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
                       <a href="#" data-toggle="modal" data-target="#model-<?php echo $i; ?>" class="btn btn-warning btn-sm btn-message-customer"><i class="fa fa-envelope-o"></i> Gửi tin nhắn</a>
                            <div id="model-<?php echo $i; ?>" class="modal fade" role="dialog">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title" style="font-weight: bold;">Gửi tin nhắn</h4>
										</div>
										<div class="modal-body" style="font-size: 14px">
											<form action="" method="post">
                                                <input type="hidden" name="cust_id" value="<?php echo $row['customer_id']; ?>">
                                                <input type="hidden" name="payment_id" value="<?php echo $row['payment_id']; ?>">
												<table class="table table-bordered">
													<tr>
                                                        <td>Tiêu đề</td>
														<td>
                                                            <input type="text" name="subject_text" class="form-control" style="width: 100%;">
														</td>
													</tr>
                                                    <tr>
                                                        <td>Nội dung</td>
                                                        <td>
                                                            <textarea name="message_text" class="form-control" cols="30" rows="10" style="width:100%;height: 200px;"></textarea>
                                                        </td>
                                                    </tr>
													<tr>
														<td></td>
                                                        <td><input type="submit" value="Gửi tin nhắn" name="form1" class="btn btn-warning btn-sm"></td>
													</tr>
												</table>
											</form>
										</div>
										<div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
										</div>
									</div>
								</div>
							</div>
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
                            <div class="status-badge <?php echo $shipping_badge_class; ?>"><?php echo $shipping_status_label; ?></div>

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


<?php require_once('footer.php'); ?>
