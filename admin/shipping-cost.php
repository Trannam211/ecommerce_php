<?php require_once('header.php'); ?>

<?php

if(isset($_POST['form1'])) {
    $valid = 1;

    if($_POST['amount'] == '') {
        $valid = 0;
        $error_message .= 'Số tiền không được để trống.<br>';
    } else {
        if(!is_numeric($_POST['amount'])) {
            $valid = 0;
            $error_message .= 'Vui lòng nhập số hợp lệ.<br>';
        }
    }

    if($valid == 1) {

        if(!schema_table_exists($pdo, 'tbl_shipping_cost_all')) {
            $pdo->exec("CREATE TABLE `tbl_shipping_cost_all` (
                `sca_id` int(11) NOT NULL AUTO_INCREMENT,
                `amount` varchar(20) NOT NULL,
                PRIMARY KEY (`sca_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }

        $statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_shipping_cost_all WHERE sca_id=1");
        $statement->execute();
        $exists = (int)$statement->fetchColumn();

        if($exists === 0) {
            $statement = $pdo->prepare("INSERT INTO tbl_shipping_cost_all (sca_id, amount) VALUES (1,?)");
            $statement->execute(array($_POST['amount']));
        } else {
            $statement = $pdo->prepare("UPDATE tbl_shipping_cost_all SET amount=? WHERE sca_id=1");
            $statement->execute(array($_POST['amount']));
        }

        $success_message = 'Đã cập nhật phí vận chuyển chung thành công.';

    }
}

$amount = '0';
if(schema_table_exists($pdo, 'tbl_shipping_cost_all')) {
    $statement = $pdo->prepare("SELECT amount FROM tbl_shipping_cost_all WHERE sca_id=1");
    $statement->execute();
    $value = $statement->fetchColumn();
    if($value !== false && $value !== null && $value !== '') {
        $amount = (string)$value;
    }
}
?>


<section class="content-header">
    <div class="content-header-left">
        <h1>Phí vận chuyển chung</h1>
    </div>
</section>

<section class="content">

    <div class="row">
        <div class="col-md-12">

            <?php if($error_message): ?>
            <div class="callout callout-danger">
            
            <p>
            <?php echo $error_message; ?>
            </p>
            </div>
            <?php endif; ?>

            <?php if($success_message): ?>
            <div class="callout callout-success">
            
            <p><?php echo $success_message; ?></p>
            </div>
            <?php endif; ?>

            <form class="form-horizontal" action="" method="post">
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Amount <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="amount" value="<?php echo $amount; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label"></label>
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-success pull-left" name="form1">Cập nhật</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

			<div class="box box-warning">
				<div class="box-body">
					Hệ thống hiện dùng một mức phí vận chuyển chung cho tất cả khách hàng.
					Chức năng cấu hình theo quốc gia đã được loại bỏ.
				</div>
			</div>

        </div>
    </div>
</section>


<?php require_once('footer.php'); ?>
