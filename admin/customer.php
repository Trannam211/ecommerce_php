<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Quản lý người dùng</h1>
	</div>
</section>

<style>
	.customer-actions {
		display: flex;
		gap: 6px;
		align-items: center;
		justify-content: center;
		white-space: nowrap;
	}
</style>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
				<div class="box-body table-responsive">
					<table id="example1" class="table table-bordered table-hover table-striped">
						<thead>
							<tr>
								<th width="10">#</th>
								<th width="180">Tên</th>
								<th width="150">Email</th>
								<th width="220">Tỉnh/Thành phố, Quận/Huyện, Phường/Xã</th>
								<th>Trạng thái</th>
								<th width="110">Khóa/Mở khóa</th>
								<th width="170">Thao tác</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$statement = $pdo->prepare("SELECT c.*, 
                                (SELECT ca.city FROM tbl_customer_address ca WHERE ca.cust_id=c.cust_id ORDER BY ca.is_default DESC, ca.address_id ASC LIMIT 1) AS default_city,
                                (SELECT ca.district FROM tbl_customer_address ca WHERE ca.cust_id=c.cust_id ORDER BY ca.is_default DESC, ca.address_id ASC LIMIT 1) AS default_district,
                                (SELECT ca.ward FROM tbl_customer_address ca WHERE ca.cust_id=c.cust_id ORDER BY ca.is_default DESC, ca.address_id ASC LIMIT 1) AS default_ward
                                FROM tbl_customer c");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);						
							foreach ($result as $row) {
								$i++;
								?>
								<tr class="<?php if($row['cust_status']==1) {echo 'bg-g';}else {echo 'bg-r';} ?>">
									<td><?php echo $i; ?></td>
									<td><?php echo $row['cust_name']; ?></td>
									<td><?php echo $row['cust_email']; ?></td>
									<td>
										<?php echo isset($row['default_city']) ? $row['default_city'] : ''; ?><br>
										<?php echo isset($row['default_district']) ? $row['default_district'] : ''; ?><br>
										<?php echo isset($row['default_ward']) ? $row['default_ward'] : ''; ?>
									</td>
									<td><?php if($row['cust_status']==1) {echo 'Hoạt động';} else {echo 'Tạm khóa';} ?></td>
									<td>
										<?php if($row['cust_status']==1): ?>
											<a href="customer-change-status.php?id=<?php echo $row['cust_id']; ?>" class="btn btn-warning btn-sm">Khóa</a>
										<?php else: ?>
											<a href="customer-change-status.php?id=<?php echo $row['cust_id']; ?>" class="btn btn-success btn-sm">Mở khóa</a>
										<?php endif; ?>
									</td>
									<td>
										<div class="customer-actions">
											<a href="customer-edit.php?id=<?php echo $row['cust_id']; ?>" class="btn btn-primary btn-sm">Sửa</a>
											<a href="#" class="btn btn-danger btn-sm" data-href="customer-delete.php?id=<?php echo $row['cust_id']; ?>" data-toggle="modal" data-target="#confirm-delete">Xóa</a>
										</div>
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
            </div>
            <div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                <a class="btn btn-danger btn-ok">Xóa</a>
            </div>
        </div>
    </div>
</div>


<?php require_once('footer.php'); ?>
