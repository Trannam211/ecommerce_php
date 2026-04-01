<?php require_once('header.php'); ?>

<?php
if(!isset($_REQUEST['id'])) {
	safe_redirect('logout.php');
}

$cust_id = (int)$_REQUEST['id'];
if($cust_id <= 0) {
	safe_redirect('logout.php');
}

$statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id=? LIMIT 1");
$statement->execute(array($cust_id));
$customer = $statement->fetch(PDO::FETCH_ASSOC);
if(!$customer) {
	safe_redirect('logout.php');
}

$address_table_available = true;
$default_address = null;
$address_notice = '';

try {
	$statement = $pdo->prepare("SELECT * FROM tbl_customer_address WHERE cust_id=? ORDER BY is_default DESC, address_id ASC LIMIT 1");
	$statement->execute(array($cust_id));
	$default_address = $statement->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
	$address_table_available = false;
	$address_notice = 'CSDL hiện chưa có bảng địa chỉ khách hàng, nên chỉ có thể cập nhật thông tin cơ bản.';
}

if(isset($_POST['form1'])) {
	$valid = 1;

	$cust_name = trim(strip_tags(isset($_POST['cust_name']) ? $_POST['cust_name'] : ''));
	$cust_email = trim(strip_tags(isset($_POST['cust_email']) ? $_POST['cust_email'] : ''));
	$cust_phone = trim(strip_tags(isset($_POST['cust_phone']) ? $_POST['cust_phone'] : ''));
	$cust_status = (isset($_POST['cust_status']) && (int)$_POST['cust_status'] === 0) ? 0 : 1;

	$city = trim(strip_tags(isset($_POST['city']) ? $_POST['city'] : ''));
	$district = trim(strip_tags(isset($_POST['district']) ? $_POST['district'] : ''));
	$ward = trim(strip_tags(isset($_POST['ward']) ? $_POST['ward'] : ''));
	$address_line = trim(strip_tags(isset($_POST['address_line']) ? $_POST['address_line'] : ''));

	if($cust_name === '') {
		$valid = 0;
		$error_message .= "Tên người dùng không được để trống<br>";
	}

	if($cust_email === '') {
		$valid = 0;
		$error_message .= "Email không được để trống<br>";
	} elseif(filter_var($cust_email, FILTER_VALIDATE_EMAIL) === false) {
		$valid = 0;
		$error_message .= "Email không hợp lệ<br>";
	} else {
		$statement = $pdo->prepare("SELECT cust_id FROM tbl_customer WHERE cust_email=? AND cust_id!=?");
		$statement->execute(array($cust_email, $cust_id));
		if($statement->rowCount() > 0) {
			$valid = 0;
			$error_message .= "Email này đã tồn tại<br>";
		}
	}

	if($cust_phone === '') {
		$valid = 0;
		$error_message .= "Số điện thoại không được để trống<br>";
	}

	$has_address_input = ($city !== '' || $district !== '' || $ward !== '' || $address_line !== '');
	if($address_table_available && $has_address_input) {
		if($city === '' || $district === '' || $ward === '' || $address_line === '') {
			$valid = 0;
			$error_message .= "Nếu nhập địa chỉ, bạn cần điền đủ Tỉnh/Thành phố, Quận/Huyện, Phường/Xã và Địa chỉ chi tiết<br>";
		}
	}

	if($valid == 1) {
		$statement = $pdo->prepare("UPDATE tbl_customer SET cust_name=?, cust_email=?, cust_phone=?, cust_status=? WHERE cust_id=?");
		$statement->execute(array(
			$cust_name,
			$cust_email,
			$cust_phone,
			$cust_status,
			$cust_id
		));

		if($address_table_available && $has_address_input) {
			if($default_address && isset($default_address['address_id'])) {
				$statement = $pdo->prepare("UPDATE tbl_customer_address SET receiver_name=?, receiver_phone=?, address_line=?, city=?, district=?, ward=? WHERE address_id=? AND cust_id=?");
				$statement->execute(array(
					$cust_name,
					$cust_phone,
					$address_line,
					$city,
					$district,
					$ward,
					(int)$default_address['address_id'],
					$cust_id
				));
			} else {
				$statement = $pdo->prepare("INSERT INTO tbl_customer_address (cust_id, receiver_name, receiver_phone, address_line, city, district, ward, is_default) VALUES (?,?,?,?,?,?,?,1)");
				$statement->execute(array(
					$cust_id,
					$cust_name,
					$cust_phone,
					$address_line,
					$city,
					$district,
					$ward
				));
			}
		}

		$success_message = 'Đã cập nhật thông tin người dùng thành công.';

		$statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id=? LIMIT 1");
		$statement->execute(array($cust_id));
		$customer = $statement->fetch(PDO::FETCH_ASSOC);

		if($address_table_available) {
			$statement = $pdo->prepare("SELECT * FROM tbl_customer_address WHERE cust_id=? ORDER BY is_default DESC, address_id ASC LIMIT 1");
			$statement->execute(array($cust_id));
			$default_address = $statement->fetch(PDO::FETCH_ASSOC);
		}
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Sửa người dùng</h1>
	</div>
	<div class="content-header-right">
		<a href="customer.php" class="btn btn-primary btn-sm">Xem danh sách</a>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">

			<?php if($error_message): ?>
			<div class="callout callout-danger">
				<p><?php echo $error_message; ?></p>
			</div>
			<?php endif; ?>

			<?php if($success_message): ?>
			<div class="callout callout-success">
				<p><?php echo $success_message; ?></p>
			</div>
			<?php endif; ?>

			<?php if($address_notice !== ''): ?>
			<div class="callout callout-warning">
				<p><?php echo $address_notice; ?></p>
			</div>
			<?php endif; ?>

			<form class="form-horizontal" action="" method="post">
				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label class="col-sm-2 control-label">Tên người dùng <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" class="form-control" name="cust_name" value="<?php echo htmlspecialchars($customer['cust_name'], ENT_QUOTES, 'UTF-8'); ?>">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">Email <span>*</span></label>
							<div class="col-sm-6">
								<input type="email" class="form-control" name="cust_email" value="<?php echo htmlspecialchars($customer['cust_email'], ENT_QUOTES, 'UTF-8'); ?>">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">Số điện thoại <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" class="form-control" name="cust_phone" value="<?php echo htmlspecialchars($customer['cust_phone'], ENT_QUOTES, 'UTF-8'); ?>">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">Trạng thái</label>
							<div class="col-sm-3">
								<select class="form-control" name="cust_status">
									<option value="1" <?php echo ((int)$customer['cust_status'] === 1) ? 'selected' : ''; ?>>Hoạt động</option>
									<option value="0" <?php echo ((int)$customer['cust_status'] === 0) ? 'selected' : ''; ?>>Tạm khóa</option>
								</select>
							</div>
						</div>

						<?php if($address_table_available): ?>
						<div class="form-group">
							<label class="col-sm-2 control-label">Tỉnh/Thành phố</label>
							<div class="col-sm-4">
								<input type="text" class="form-control" name="city" value="<?php echo htmlspecialchars($default_address ? $default_address['city'] : '', ENT_QUOTES, 'UTF-8'); ?>">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">Quận/Huyện</label>
							<div class="col-sm-4">
								<input type="text" class="form-control" name="district" value="<?php echo htmlspecialchars($default_address ? $default_address['district'] : '', ENT_QUOTES, 'UTF-8'); ?>">
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">Phường/Xã</label>
							<div class="col-sm-4">
								<input type="text" class="form-control" name="ward" value="<?php echo htmlspecialchars($default_address ? $default_address['ward'] : '', ENT_QUOTES, 'UTF-8'); ?>">
							</div>
						</div>

						<div class="form-group form-group-top">
							<label class="col-sm-2 control-label">Địa chỉ chi tiết</label>
							<div class="col-sm-6">
								<textarea class="form-control" name="address_line" rows="3"><?php echo htmlspecialchars($default_address ? $default_address['address_line'] : '', ENT_QUOTES, 'UTF-8'); ?></textarea>
							</div>
						</div>
						<?php endif; ?>

						<div class="form-group">
							<label class="col-sm-2 control-label"></label>
							<div class="col-sm-6" style="margin-top:12px;">
								<button type="submit" class="btn btn-success" name="form1">Cập nhật người dùng</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</section>

<?php require_once('footer.php'); ?>
