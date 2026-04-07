<?php require_once('header.php'); ?>

<?php
// Flash alerts for delete actions
$delete_error = (isset($_GET['error']) && $_GET['error'] === 'delete_failed');
$delete_success = (isset($_GET['success']) && $_GET['success'] === 'deleted');
$hide_imported_success = (isset($_GET['success']) && $_GET['success'] === 'hidden_imported');
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Quản lý sản phẩm</h1>
	</div>
	<div class="content-header-right">
		<a href="import-receipt.php" class="btn btn-info btn-sm" style="margin-right:6px;">Phiếu nhập</a>
		<a href="product-add.php" class="btn btn-primary btn-sm">Thêm mới</a>
	</div>
</section>

<style>
.product-table td:last-child,
.product-table th:last-child {
	white-space: nowrap;
	width: 140px;
}

.product-table .btn {
	min-width: 44px;
}

.product-table .btn + .btn {
	margin-left: 4px;
}

.product-table .product-thumb {
	width: 76px;
	height: 76px;
	object-fit: cover;
	border-radius: 4px;
	border: 1px solid #ddd;
}

.product-table .status-badge {
	min-width: 62px;
	display: inline-block;
	text-align: center;
	padding: 4px 10px;
	border-radius: 999px;
	font-weight: 600;
	font-size: 12px;
}


.product-table th.category-col,
.product-table td.category-col {
	width: 220px;
}

.product-table .category-breadcrumb {
	display: block;
	white-space: normal;
	overflow-wrap: anywhere;
	word-break: break-word;
	line-height: 1.4;
}

.product-table .qty-col,
.product-table .qty-value {
	white-space: nowrap;
	text-align: center;
	width: 90px;
}

.product-table .status-yes {
	background-color: #2e7d32;
}

.product-table .status-no {
	background-color: #c62828;
}
</style>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<?php if($delete_success): ?>
				<div class="alert alert-success">Đã xóa sản phẩm thành công.</div>
			<?php endif; ?>
			<?php if($delete_error): ?>
				<div class="alert alert-danger">Xóa sản phẩm thất bại. Vui lòng thử lại hoặc kiểm tra dữ liệu liên quan (đơn hàng/thanh toán).</div>
			<?php endif; ?>
			<?php if($hide_imported_success): ?>
				<div class="alert alert-warning">Sản phẩm đã từng nhập hàng nên không xóa cứng. Hệ thống đã chuyển sang trạng thái ẩn.</div>
			<?php endif; ?>
			<div class="box box-info">
				<div class="box-body table-responsive">
					<table id="example1" class="table table-bordered table-hover table-striped product-table">
					<thead>
							<tr>
								<th width="10">#</th>
								<th width="90">Mã SP</th>
								<th>Ảnh</th>
								<th width="160">Tên sản phẩm</th>
								<th width="60">Giá cũ</th>
								<th width="60">Giá vốn</th>
								<th width="55">% lãi</th>
								<th width="60">Giá bán</th>
								<th class="qty-col">Tồn kho</th>
								<th width="70">Ngưỡng</th>
								<th>Nổi bật</th>
								<th>Hiển thị</th>
								<th class="category-col">Danh mục</th>
								<th width="80">Thao tác</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$statement = $pdo->prepare("SELECT
								p_id,
								p_code,
								p_name,
								p_old_price,
								p_cost_price,
								p_profit_percent,
								p_low_stock_threshold,
								p_unit,
								p_current_price,
								p_qty,
								p_featured_photo,
								p_is_featured,
								p_is_active,
								ecat_id
							FROM tbl_product
							ORDER BY p_id DESC");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);
							foreach ($result as $row) {
								$i++;
								$category_breadcrumb = admin_get_category_breadcrumb($pdo, (int)$row['ecat_id']);
								?>
								<tr>
									<td><?php echo $i; ?></td>
									<td><?php echo htmlspecialchars((string)$row['p_code'], ENT_QUOTES, 'UTF-8'); ?></td>
									<td><img src="../assets/uploads/<?php echo $row['p_featured_photo']; ?>" alt="<?php echo $row['p_name']; ?>" class="product-thumb"></td>
									<td><?php echo $row['p_name']; ?></td>
									<td><?php echo format_price_vnd($row['p_old_price']); ?></td>
									<td><?php echo format_price_vnd($row['p_cost_price']); ?></td>
									<td><?php echo number_format((float)$row['p_profit_percent'], 2); ?>%</td>
									<td><?php echo format_price_vnd($row['p_current_price']); ?></td>
									<td class="qty-value">
										<?php
										$qty_value = (int)$row['p_qty'];
										$low_threshold = (int)$row['p_low_stock_threshold'];
										echo $qty_value;
										if($low_threshold > 0 && $qty_value <= $low_threshold) {
											echo '<br><span class="badge badge-danger" style="margin-top:4px;">Sắp hết</span>';
										}
										?>
									</td>
									<td><?php echo (int)$row['p_low_stock_threshold']; ?></td>
									<td>
										<?php if($row['p_is_featured'] == 1) {echo '<span class="badge badge-success status-badge status-yes">Có</span>';} else {echo '<span class="badge badge-danger status-badge status-no">Không</span>';} ?>
									</td>
									<td>
										<?php if($row['p_is_active'] == 1) {echo '<span class="badge badge-success status-badge status-yes">Có</span>';} else {echo '<span class="badge badge-danger status-badge status-no">Không</span>';} ?>
									</td>
									<td class="category-col"><span class="category-breadcrumb"><?php echo htmlspecialchars($category_breadcrumb, ENT_QUOTES, 'UTF-8'); ?></span></td>
									<td>										
										<a href="product-edit.php?id=<?php echo $row['p_id']; ?>" class="btn btn-primary btn-xs">Sửa</a>
										<a href="#" class="btn btn-danger btn-xs" data-href="product-delete.php?id=<?php echo $row['p_id']; ?>" data-toggle="modal" data-target="#confirm-delete" data-bs-toggle="modal" data-bs-target="#confirm-delete">Xóa</a>  
									</td>
								</tr>
								<?php
							}
							?>							
						</tbody>
					</table>
				</div>
			</div>
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
				<p>Bạn có chắc chắn muốn xóa mục này không?</p>
				<p style="color:red;">Lưu ý! Sản phẩm này cũng sẽ bị xóa khỏi bảng đơn hàng, bảng thanh toán, bảng kích thước, bảng màu và bảng đánh giá.</p>
            </div>
            <div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                <a class="btn btn-danger btn-ok">Xóa</a>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
