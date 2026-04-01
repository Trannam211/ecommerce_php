<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form_all'])) {
	$valid = 1;
	$change_password = false;

	$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE id=?");
	$statement->execute(array($_SESSION['user']['id']));
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	foreach($result as $row) {
		$current_email = $row['email'];
		$current_password_hash = $row['password'];
	}

	if(empty($_POST['full_name'])) {
		$valid = 0;
		$error_message .= "Tên không được để trống<br>";
	}

	if(empty($_POST['email'])) {
		$valid = 0;
		$error_message .= 'Địa chỉ email không được để trống<br>';
	} else {
		if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === false) {
			$valid = 0;
			$error_message .= 'Địa chỉ email không hợp lệ<br>';
		} else {
			$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE email=? and email!=?");
			$statement->execute(array($_POST['email'],$current_email));
			$total = $statement->rowCount();
			if($total) {
				$valid = 0;
				$error_message .= 'Địa chỉ email đã tồn tại<br>';
			}
		}
	}

	if(empty($_POST['role'])) {
		$valid = 0;
		$error_message .= 'Vai trò không được để trống<br>';
	}

	$path = $_FILES['photo']['name'];
	$path_tmp = $_FILES['photo']['tmp_name'];
	$path_size = isset($_FILES['photo']['size']) ? (int)$_FILES['photo']['size'] : 0;
	$ext = '';
	if($path != '') {
		$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
		if($ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif') {
			$valid = 0;
			$error_message .= 'Bạn phải tải lên tệp jpg, jpeg, gif hoặc png<br>';
		}

		if($path_size > 2 * 1024 * 1024) {
			$valid = 0;
			$error_message .= 'Dung lượng ảnh tối đa là 2MB<br>';
		}
	}

	$current_password = isset($_POST['current_password']) ? trim($_POST['current_password']) : '';
	$password = isset($_POST['password']) ? trim($_POST['password']) : '';
	$re_password = isset($_POST['re_password']) ? trim($_POST['re_password']) : '';
	if($current_password !== '' || $password !== '' || $re_password !== '') {
		if($current_password === '' || $password === '' || $re_password === '') {
			$valid = 0;
			$error_message .= 'Vui lòng nhập đầy đủ mật khẩu hiện tại, mật khẩu mới và xác nhận mật khẩu mới<br>';
		} elseif(md5($current_password) != $current_password_hash) {
			$valid = 0;
			$error_message .= 'Mật khẩu hiện tại không đúng<br>';
		} elseif($password != $re_password) {
			$valid = 0;
			$error_message .= 'Mật khẩu xác nhận không khớp<br>';
		} else {
			$change_password = true;
		}
	}

	if($valid == 1) {
		$_SESSION['user']['full_name'] = $_POST['full_name'];
		$_SESSION['user']['email'] = $_POST['email'];
		$_SESSION['user']['phone'] = $_POST['phone'];
		$_SESSION['user']['role'] = $_POST['role'];

		$statement = $pdo->prepare("UPDATE tbl_user SET full_name=?, email=?, phone=?, role=? WHERE id=?");
		$statement->execute(array($_POST['full_name'],$_POST['email'],$_POST['phone'],$_POST['role'],$_SESSION['user']['id']));

		if($path != '') {
			if($_SESSION['user']['photo'] != '') {
				unlink('../assets/uploads/'.$_SESSION['user']['photo']);
			}

			$final_name = 'user-'.$_SESSION['user']['id'].'.'.$ext;
			move_uploaded_file($path_tmp, '../assets/uploads/'.$final_name);
			$_SESSION['user']['photo'] = $final_name;

			$statement = $pdo->prepare("UPDATE tbl_user SET photo=? WHERE id=?");
			$statement->execute(array($final_name,$_SESSION['user']['id']));
		}

		if($change_password) {
			$_SESSION['user']['password'] = md5($password);
			$statement = $pdo->prepare("UPDATE tbl_user SET password=? WHERE id=?");
			$statement->execute(array(md5($password),$_SESSION['user']['id']));
		}

		$success_message = 'Đã cập nhật hồ sơ thành công.';
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Chỉnh sửa hồ sơ</h1>
	</div>
</section>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE id=?");
$statement->execute(array($_SESSION['user']['id']));
$statement->rowCount();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
foreach ($result as $row) {
	$full_name = $row['full_name'];
	$email     = $row['email'];
	$phone     = $row['phone'];
	$photo     = $row['photo'];
	$status    = $row['status'];
	$role      = $row['role'];
}

$role_options = array();
$statement = $pdo->prepare("SELECT DISTINCT role FROM tbl_user ORDER BY role ASC");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
	if(!empty($row['role'])) {
		$role_options[] = $row['role'];
	}
}

if(empty($role_options)) {
	$role_options[] = $role;
}
?>

<style>
.profile-edit-box {
	border: 1px solid #d5dbe3;
	background: #ffffff;
	border-radius: 0;
}

.profile-edit-box .box-body {
	padding: 22px 20px;
}

.profile-section-title {
	font-size: 16px;
	font-weight: 700;
	margin: 0 0 14px;
	padding-bottom: 8px;
	border-bottom: 1px solid #e6eaf0;
}

.profile-section-divider {
	margin: 20px 0;
	border-top: 1px solid #e8edf3;
}

.profile-edit-box .form-group {
	margin-bottom: 14px;
}

.profile-edit-box input,
.profile-edit-box textarea,
.profile-edit-box select,
.profile-edit-box .btn {
	border-radius: 0 !important;
}

.profile-edit-box .existing-photo {
	border: 1px solid #cfd6df;
	padding: 4px;
	background: #fff;
}

.password-field-row {
	display: flex;
	align-items: stretch;
	width: 100%;
}

.password-field-row .password-field {
	flex: 1 1 auto;
	min-width: 0;
	height: 40px;
	border-right: 0;
}

.password-field-row .toggle-password-btn {
	border-radius: 0 !important;
	border: 1px solid #d2d6de;
	border-left: 0;
	background: #f7f9fc;
	color: #4b5563;
	width: 42px;
	height: 40px;
	padding: 0;
	display: inline-flex;
	align-items: center;
	justify-content: center;
}

.password-field-row .toggle-password-btn:hover,
.password-field-row .toggle-password-btn:focus {
	background: #eef2ff;
	color: #1f3d7a;
}
</style>

<section class="content" style="min-height:auto;margin-bottom: -30px;">
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
		</div>
	</div>
</section>


<section class="content">

	<div class="row">
		<div class="col-md-12">
			<form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
				<div class="box box-info profile-edit-box">
					<div class="box-body">
						<h3 class="profile-section-title">Thông tin tài khoản</h3>

						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Tên <span>*</span></label>
							<div class="col-sm-5">
								<input type="text" class="form-control" name="full_name" value="<?php echo $full_name; ?>">
							</div>
						</div>

						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Email <span>*</span></label>
							<div class="col-sm-5">
								<input type="email" class="form-control" name="email" value="<?php echo $email; ?>">
							</div>
						</div>

						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Số điện thoại</label>
							<div class="col-sm-5">
								<input type="text" class="form-control" name="phone" value="<?php echo $phone; ?>">
							</div>
						</div>

						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Vai trò</label>
							<div class="col-sm-5">
								<select name="role" class="form-control">
									<?php foreach($role_options as $item_role) { ?>
									<option value="<?php echo $item_role; ?>" <?php if($item_role == $role) {echo 'selected';} ?>><?php echo $item_role; ?></option>
									<?php } ?>
								</select>
							</div>
						</div>

						<div class="profile-section-divider"></div>
						<h3 class="profile-section-title">Ảnh đại diện</h3>

						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Ảnh hiện tại</label>
							<div class="col-sm-6" style="padding-top:6px;">
								<img src="../assets/uploads/<?php echo $photo; ?>" class="existing-photo" width="140">
							</div>
						</div>

						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Ảnh mới</label>
							<div class="col-sm-6" style="padding-top:6px;">
								<input type="file" name="photo">
							</div>
						</div>

						<div class="profile-section-divider"></div>
						<h3 class="profile-section-title">Đổi mật khẩu</h3>

						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Mật khẩu hiện tại</label>
							<div class="col-sm-5">
								<div class="password-field-row">
									<input type="password" class="form-control password-field" name="current_password">
									<button type="button" class="btn btn-default toggle-password-btn" aria-label="Hiện hoặc ẩn mật khẩu"><i class="fa fa-eye"></i></button>
								</div>
							</div>
						</div>

						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Mật khẩu mới</label>
							<div class="col-sm-5">
								<div class="password-field-row">
									<input type="password" class="form-control password-field" name="password">
									<button type="button" class="btn btn-default toggle-password-btn" aria-label="Hiện hoặc ẩn mật khẩu"><i class="fa fa-eye"></i></button>
								</div>
							</div>
						</div>

						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Xác nhận mật khẩu mới</label>
							<div class="col-sm-5">
								<div class="password-field-row">
									<input type="password" class="form-control password-field" name="re_password">
									<button type="button" class="btn btn-default toggle-password-btn" aria-label="Hiện hoặc ẩn mật khẩu"><i class="fa fa-eye"></i></button>
								</div>
							</div>
						</div>

						<div class="form-group">
							<label for="" class="col-sm-2 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success pull-left" name="form_all">Lưu toàn bộ thay đổi</button>
							</div>
						</div>
					</div>
				</div>
			</form>

		</div>
	</div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
	<?php if($success_message != ''): ?>
	var headerName = document.querySelector('.navbar-nav > .user-menu .hidden-xs');
	if (headerName) {
		headerName.textContent = '<?php echo addslashes($_SESSION['user']['full_name']); ?>';
	}

	var userImages = document.querySelectorAll('.navbar-nav > .user-menu .user-image');
	userImages.forEach(function(img) {
		img.src = '../assets/uploads/<?php echo addslashes($_SESSION['user']['photo']); ?>?v=<?php echo time(); ?>';
	});
	<?php endif; ?>

	var toggleButtons = document.querySelectorAll('.toggle-password-btn');
	toggleButtons.forEach(function(button) {
		button.addEventListener('click', function() {
			var group = this.closest('.password-field-row');
			if (!group) {
				return;
			}

			var input = group.querySelector('.password-field');
			var icon = this.querySelector('i');
			if (!input || !icon) {
				return;
			}

			if (input.type === 'password') {
				input.type = 'text';
				icon.classList.remove('fa-eye');
				icon.classList.add('fa-eye-slash');
			} else {
				input.type = 'password';
				icon.classList.remove('fa-eye-slash');
				icon.classList.add('fa-eye');
			}
		});
	});
});
</script>

<?php require_once('footer.php'); ?>
