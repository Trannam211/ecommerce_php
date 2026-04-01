<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Danh sách quốc gia</h1>
	</div>
	<div class="content-header-right">
		<a href="country-add.php" class="btn btn-primary btn-sm">Thêm mới</a>
	</div>
</section>

<style>
.country-table td:last-child,
.country-table th:last-child {
	white-space: nowrap;
	width: 140px;
}

.country-table .btn {
	min-width: 44px;
}

.country-table .btn + .btn {
	margin-left: 4px;
}
</style>


<section class="content">

  <div class="row">
    <div class="col-md-12">


      <div class="box box-info">
        
        <div class="box-body table-responsive">
		  <table id="example1" class="table table-bordered table-hover table-striped country-table">
			<thead>
			    <tr>
			        <th>#</th>
			        <th>Tên quốc gia</th>
			        <th>Thao tác</th>
			    </tr>
			</thead>
            <tbody>
            	<?php
            	$i=0;
            	$statement = $pdo->prepare("SELECT * FROM tbl_country ORDER BY country_id ASC");
            	$statement->execute();
            	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
            	foreach ($result as $row) {
            		$i++;
            		?>
					<tr>
	                    <td><?php echo $i; ?></td>
	                    <td><?php echo $row['country_name']; ?></td>
	                    <td>
	                        <a href="country-edit.php?id=<?php echo $row['country_id']; ?>" class="btn btn-primary btn-xs">Sửa</a>
	                        <a href="#" class="btn btn-danger btn-xs" data-href="country-delete.php?id=<?php echo $row['country_id']; ?>" data-toggle="modal" data-target="#confirm-delete">Xóa</a>
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
