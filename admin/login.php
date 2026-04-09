<?php
ob_start();
session_start();
include("inc/config.php");
include("inc/functions.php");
include("inc/CSRF_Protect.php");
$csrf = new CSRF_Protect();
$error_message='';

if(isset($_POST['form1'])) {
        
    if(empty($_POST['email']) || empty($_POST['password'])) {
        $error_message = 'Email hoặc mật khẩu không được để trống<br>';
    } else {
		
		$email = strip_tags($_POST['email']);
		$password = strip_tags($_POST['password']);

	    	$statement = $pdo->prepare("SELECT * FROM tbl_user WHERE email=? AND status=? ORDER BY id DESC LIMIT 1");
	    	$statement->execute(array($email,'Active'));
		$row = $statement->fetch(PDO::FETCH_ASSOC);

		if(!$row) {
			$error_message .= 'Email không đúng<br>';
		} else {
			$stored_password = trim((string)$row['password']);
			$looks_like_password_hash = (
				strpos($stored_password, '$2y$') === 0 ||
				strpos($stored_password, '$2a$') === 0 ||
				strpos($stored_password, '$2b$') === 0 ||
				strpos($stored_password, '$argon2') === 0
			);

			$password_ok = false;
			if(function_exists('password_verify') && $looks_like_password_hash) {
				$password_ok = password_verify($password, $stored_password);
			} elseif(preg_match('/^[a-f0-9]{32}$/i', $stored_password)) {
				$password_ok = hash_equals(strtolower($stored_password), md5($password));
			} else {
				$password_ok = hash_equals($stored_password, $password);
			}

			if(!$password_ok) {
				$error_message .= 'Mật khẩu không đúng<br>';
			} else {
				// Auto-upgrade legacy MD5/plaintext and rehash old cost/algorithm.
				$should_update_hash = false;
				if($looks_like_password_hash) {
					if(function_exists('password_needs_rehash') && password_needs_rehash($stored_password, PASSWORD_DEFAULT)) {
						$should_update_hash = true;
					}
				} else {
					$should_update_hash = true;
				}

				if($should_update_hash) {
					$new_hash = password_hash($password, PASSWORD_DEFAULT);
					if($new_hash !== false) {
						$statement = $pdo->prepare("UPDATE tbl_user SET password=? WHERE id=?");
						$statement->execute(array($new_hash, (int)$row['id']));
						$row['password'] = $new_hash;
					}
				}

				$_SESSION['user'] = $row;
				header("location: index.php");
			}
		}
    }

    
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Đăng nhập</title>

	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<link rel="stylesheet" href="css/ionicons.min.css">
	<link rel="stylesheet" href="css/datepicker3.css">
	<link rel="stylesheet" href="css/all.css">
	<link rel="stylesheet" href="css/select2.min.css">
	<link rel="stylesheet" href="css/dataTables.bootstrap.css">
	<link rel="stylesheet" href="css/AdminLTE.min.css">
	<link rel="stylesheet" href="css/_all-skins.min.css">
	<link rel="stylesheet" href="css/bootstrap5-legacy-bridge.css">

	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="css/bootstrap-modern-admin.css">
</head>

<body class="hold-transition login-page sidebar-mini">

<div class="login-box">
	<div class="login-logo">
		<b>Đăng nhập - Trang quản trị</b>
	</div>
  	<div class="login-box-body">

	    <?php 
	    if( (isset($error_message)) && ($error_message!='') ):
	        echo '<div class="error">'.$error_message.'</div>';
	    endif;
	    ?>

		<form action="" method="post">
			<?php $csrf->echoInputField(); ?>
			<div class="form-group has-feedback">
				<input class="form-control" placeholder="Địa chỉ email" name="email" type="email" autocomplete="off" autofocus>
			</div>
			<div class="form-group has-feedback">
				<input class="form-control" placeholder="Mật khẩu" name="password" type="password" autocomplete="off" value="">
			</div>
			<div class="row">
				<div class="col-xs-8"></div>
				<div class="col-xs-4">
					<input type="submit" class="btn btn-success btn-block btn-flat login-button" name="form1" value="Đăng nhập">
				</div>
			</div>
		</form>
	</div>
</div>


<script src="js/jquery-2.2.3.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap5-legacy-bridge.js"></script>
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/dataTables.bootstrap.min.js"></script>
<script src="js/select2.full.min.js"></script>
<script src="js/jquery.inputmask.js"></script>
<script src="js/jquery.inputmask.date.extensions.js"></script>
<script src="js/jquery.inputmask.extensions.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/icheck.min.js"></script>
<script src="js/fastclick.js"></script>
<script src="js/jquery.sparkline.min.js"></script>
<script src="js/jquery.slimscroll.min.js"></script>
<script src="js/app.min.js"></script>
<script src="js/demo.js"></script>

</body>
</html>
