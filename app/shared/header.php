<?php
if(ob_get_level() === 0) {
	ob_start();
}

// Ensure output buffering is effective for redirects.
ob_implicit_flush(false);
if(session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}
require_once(__DIR__ . '/../../admin/inc/config.php');
require_once(__DIR__ . '/../../admin/inc/functions.php');
require_once(__DIR__ . '/../../admin/inc/CSRF_Protect.php');
$csrf = new CSRF_Protect();
$error_message = '';
$success_message = '';
$error_message1 = '';
$success_message1 = '';

$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row)
{
	$logo = $row['logo'];
	$favicon = $row['favicon'];
	$contact_email = $row['contact_email'];
	$contact_phone = $row['contact_phone'];
	$meta_title_home = $row['meta_title_home'];
    $meta_keyword_home = $row['meta_keyword_home'];
    $meta_description_home = $row['meta_description_home'];
}

// Checking the order table and removing the pending transaction that are 24 hours+ old. Very important
$current_date_time = date('Y-m-d H:i:s');
$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE payment_status=?");
$statement->execute(array('Pending'));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
foreach ($result as $row) {
	$ts1 = strtotime($row['payment_date']);
	$ts2 = strtotime($current_date_time);     
	$diff = $ts2 - $ts1;
	$time = $diff/(3600);
	if($time>24) {
		$variant_table_exists = false;
		$statement_check_variant = $pdo->prepare("SHOW TABLES LIKE 'tbl_product_variant'");
		$statement_check_variant->execute();
		$variant_table_exists = $statement_check_variant->rowCount() > 0;

		// Return back the stock amount
		$statement1 = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id=?");
		$statement1->execute(array($row['payment_id']));
		$result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result1 as $row1) {
			$statement2 = $pdo->prepare("SELECT * FROM tbl_product WHERE p_id=?");
			$statement2->execute(array($row1['product_id']));
			$result2 = $statement2->fetchAll(PDO::FETCH_ASSOC);							
			foreach ($result2 as $row2) {
				$p_qty = $row2['p_qty'];
			}
			$final = $p_qty+$row1['quantity'];

			$statement = $pdo->prepare("UPDATE tbl_product SET p_qty=? WHERE p_id=?");
			$statement->execute(array($final,$row1['product_id']));

			if($variant_table_exists) {
				$restore_size_id = 0;
				$restore_color_id = 0;
				if(isset($row1['size']) && trim((string)$row1['size']) !== '') {
					$statement_size = $pdo->prepare("SELECT size_id FROM tbl_size WHERE size_name=? LIMIT 1");
					$statement_size->execute(array($row1['size']));
					$size_row = $statement_size->fetch(PDO::FETCH_ASSOC);
					if($size_row) {
						$restore_size_id = (int)$size_row['size_id'];
					}
				}
				if(isset($row1['color']) && trim((string)$row1['color']) !== '') {
					$statement_color = $pdo->prepare("SELECT color_id FROM tbl_color WHERE color_name=? LIMIT 1");
					$statement_color->execute(array($row1['color']));
					$color_row = $statement_color->fetch(PDO::FETCH_ASSOC);
					if($color_row) {
						$restore_color_id = (int)$color_row['color_id'];
					}
				}

				if($restore_size_id > 0 && $restore_color_id > 0) {
					$statement_variant = $pdo->prepare("SELECT pv_qty FROM tbl_product_variant WHERE p_id=? AND size_id=? AND color_id=? LIMIT 1");
					$statement_variant->execute(array($row1['product_id'], $restore_size_id, $restore_color_id));
					$variant_row = $statement_variant->fetch(PDO::FETCH_ASSOC);
					if($variant_row) {
						$next_variant_qty = (int)$variant_row['pv_qty'] + (int)$row1['quantity'];
						$statement_variant_update = $pdo->prepare("UPDATE tbl_product_variant SET pv_qty=? WHERE p_id=? AND size_id=? AND color_id=?");
						$statement_variant_update->execute(array($next_variant_qty, $row1['product_id'], $restore_size_id, $restore_color_id));
					}
				}
			}
		}
		
		// Deleting data from table
		$statement1 = $pdo->prepare("DELETE FROM tbl_order WHERE payment_id=?");
		$statement1->execute(array($row['payment_id']));

		$statement1 = $pdo->prepare("DELETE FROM tbl_payment WHERE id=?");
		$statement1->execute(array($row['id']));
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Meta Tags -->
	<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>

	<!-- Favicon -->
	<link rel="icon" type="image/png" href="../assets/uploads/<?php echo $favicon; ?>">

	<!-- Stylesheets -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="../assets/css/font-awesome.min.css">
	<link rel="stylesheet" href="../assets/css/owl.carousel.min.css">
	<link rel="stylesheet" href="../assets/css/owl.theme.default.min.css">
	<link rel="stylesheet" href="../assets/css/jquery.bxslider.min.css">
    <link rel="stylesheet" href="../assets/css/magnific-popup.css">
    <link rel="stylesheet" href="../assets/css/rating.css">
	<link rel="stylesheet" href="../assets/css/spacing.css">
	<link rel="stylesheet" href="../assets/css/bootstrap-touch-slider.css">
	<link rel="stylesheet" href="../assets/css/animate.min.css">
	<link rel="stylesheet" href="../assets/css/tree-menu.css">
	<link rel="stylesheet" href="../assets/css/select2.min.css">
	<link rel="stylesheet" href="../assets/css/main.css">
	<link rel="stylesheet" href="../assets/css/responsive.css">
	<link rel="stylesheet" href="../assets/css/bootstrap5-legacy-bridge.css">
	<link rel="stylesheet" href="../assets/css/modern-ui.css">

	<?php

	$statement = $pdo->prepare("SELECT * FROM tbl_page WHERE id=1");
	$statement->execute();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
	foreach ($result as $row) {
		$about_meta_title = $row['about_meta_title'];
		$about_meta_keyword = $row['about_meta_keyword'];
		$about_meta_description = $row['about_meta_description'];
		$blog_meta_title = $row['blog_meta_title'];
		$blog_meta_keyword = $row['blog_meta_keyword'];
		$blog_meta_description = $row['blog_meta_description'];
		$contact_meta_title = $row['contact_meta_title'];
		$contact_meta_keyword = $row['contact_meta_keyword'];
		$contact_meta_description = $row['contact_meta_description'];
		$pgallery_meta_title = $row['pgallery_meta_title'];
		$pgallery_meta_keyword = $row['pgallery_meta_keyword'];
		$pgallery_meta_description = $row['pgallery_meta_description'];
		$vgallery_meta_title = $row['vgallery_meta_title'];
		$vgallery_meta_keyword = $row['vgallery_meta_keyword'];
		$vgallery_meta_description = $row['vgallery_meta_description'];
	}

	$cur_page = substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
	
	if($cur_page == 'index.php' || $cur_page == 'login.php' || $cur_page == 'registration.php' || $cur_page == 'cart.php' || $cur_page == 'checkout.php' || $cur_page == 'forget-password.php' || $cur_page == 'reset-password.php' || $cur_page == 'product-category.php' || $cur_page == 'product.php') {
		?>
		<title><?php echo $meta_title_home; ?></title>
		<meta name="keywords" content="<?php echo $meta_keyword_home; ?>">
		<meta name="description" content="<?php echo $meta_description_home; ?>">
		<?php
	}

	if($cur_page == 'about.php') {
		?>
		<title><?php echo $about_meta_title; ?></title>
		<meta name="keywords" content="<?php echo $about_meta_keyword; ?>">
		<meta name="description" content="<?php echo $about_meta_description; ?>">
		<?php
	}
	if($cur_page == 'contact.php') {
		?>
		<title><?php echo $contact_meta_title; ?></title>
		<meta name="keywords" content="<?php echo $contact_meta_keyword; ?>">
		<meta name="description" content="<?php echo $contact_meta_description; ?>">
		<?php
	}
	if($cur_page == 'product.php')
	{
		$statement = $pdo->prepare("SELECT * FROM tbl_product WHERE p_id=?");
		$statement->execute(array($_REQUEST['id']));
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
		foreach ($result as $row) 
		{
		    $og_photo = $row['p_featured_photo'];
		    $og_title = $row['p_name'];
		    $og_slug = 'product.php?id='.$_REQUEST['id'];
			$og_description = substr(strip_tags($row['p_description']),0,200).'...';
		}
	}

	if($cur_page == 'dashboard.php') {
		?>
		<title>Trang tài khoản - <?php echo $meta_title_home; ?></title>
		<meta name="keywords" content="<?php echo $meta_keyword_home; ?>">
		<meta name="description" content="<?php echo $meta_description_home; ?>">
		<?php
	}
	if($cur_page == 'customer-profile-update.php') {
		?>
		<title>Cập nhật hồ sơ - <?php echo $meta_title_home; ?></title>
		<meta name="keywords" content="<?php echo $meta_keyword_home; ?>">
		<meta name="description" content="<?php echo $meta_description_home; ?>">
		<?php
	}
	if($cur_page == 'customer-billing-shipping-update.php') {
		?>
		<title>Địa chỉ của tôi - <?php echo $meta_title_home; ?></title>
		<meta name="keywords" content="<?php echo $meta_keyword_home; ?>">
		<meta name="description" content="<?php echo $meta_description_home; ?>">
		<?php
	}
	if($cur_page == 'customer-password-update.php') {
		?>
		<title>Đổi mật khẩu - <?php echo $meta_title_home; ?></title>
		<meta name="keywords" content="<?php echo $meta_keyword_home; ?>">
		<meta name="description" content="<?php echo $meta_description_home; ?>">
		<?php
	}
	if($cur_page == 'customer-order.php') {
		?>
		<title>Đơn hàng của tôi - <?php echo $meta_title_home; ?></title>
		<meta name="keywords" content="<?php echo $meta_keyword_home; ?>">
		<meta name="description" content="<?php echo $meta_description_home; ?>">
		<?php
	}
	?>
	
	<?php if($cur_page == 'blog-single.php'): ?>
		<meta property="og:title" content="<?php echo $og_title; ?>">
		<meta property="og:type" content="website">
		<meta property="og:url" content="<?php echo BASE_URL.'frontend/'.$og_slug; ?>">
		<meta property="og:description" content="<?php echo $og_description; ?>">
		<meta property="og:image" content="../assets/uploads/<?php echo $og_photo; ?>">
	<?php endif; ?>

	<?php if($cur_page == 'product.php'): ?>
		<meta property="og:title" content="<?php echo $og_title; ?>">
		<meta property="og:type" content="website">
		<meta property="og:url" content="<?php echo BASE_URL.'frontend/'.$og_slug; ?>">
		<meta property="og:description" content="<?php echo $og_description; ?>">
		<meta property="og:image" content="../assets/uploads/<?php echo $og_photo; ?>">
	<?php endif; ?>

</head>
<body>
<!--
<div id="preloader">
	<div id="status"></div>
</div>-->

<!-- top bar -->
<div class="top">
	<div class="container">
		<div class="row">
			<div class="col-md-12 col-sm-12 col-xs-12">
				<div class="left">
					<ul>
						<li><i class="fa fa-phone"></i> <?php echo $contact_phone; ?></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>


<div class="header">
	<div class="container">
		<div class="row inner">
			<div class="col-md-3 logo">
					<a href="index.php"><img src="../assets/uploads/<?php echo $logo; ?>" alt="logo image"></a>
			</div>

			<div class="col-md-6 search-area">
				<form class="navbar-form navbar-left" role="search" action="search-result.php" method="get">
					<?php $csrf->echoInputField(); ?>
					<div class="form-group">
						<input type="text" class="form-control search-top" placeholder="Tìm sản phẩm" name="search_text">
					</div>
					<button type="submit" class="btn btn-danger">Tìm kiếm</button>
				</form>
			</div>

			<div class="col-md-3 right">
				<ul>
					<li><a href="cart.php"><i class="fa fa-shopping-basket"></i> Giỏ hàng (<?php
					if(isset($_SESSION['cart_p_id'])) {
						$table_total_price = 0;
						$i=0;
	                    foreach($_SESSION['cart_p_qty'] as $key => $value) 
	                    {
	                        $i++;
	                        $arr_cart_p_qty[$i] = $value;
	                    }                    $i=0;
	                    foreach($_SESSION['cart_p_current_price'] as $key => $value) 
	                    {
	                        $i++;
	                        $arr_cart_p_current_price[$i] = $value;
	                    }
	                    for($i=1;$i<=count($arr_cart_p_qty);$i++) {
	                    	$row_total_price = $arr_cart_p_current_price[$i]*$arr_cart_p_qty[$i];
	                        $table_total_price = $table_total_price + $row_total_price;
	                    }
						echo format_price_vnd($table_total_price);
					} else {
						echo '0đ';
					}
					?>)</a></li>
					
					<?php
					if(isset($_SESSION['customer'])) {
						$header_avatar = 'default-avatar.svg';
						if(isset($_SESSION['customer']['cust_photo']) && $_SESSION['customer']['cust_photo'] !== '') {
							$candidate_avatar = basename($_SESSION['customer']['cust_photo']);
							$avatar_disk_path = __DIR__ . '/../../assets/uploads/' . $candidate_avatar;
							if(file_exists($avatar_disk_path)) {
								$header_avatar = $candidate_avatar;
							}
						}
						?>
						<li class="account-menu">
							<a href="#">
								<span class="account-avatar"><img src="../assets/uploads/<?php echo htmlspecialchars($header_avatar, ENT_QUOTES, 'UTF-8'); ?>" alt="Avatar"></span>
								<?php echo $_SESSION['customer']['cust_name']; ?>
								<i class="fa fa-angle-down"></i>
							</a>
							<ul class="account-dropdown">
								<li><a href="dashboard.php"><i class="fa fa-user"></i> Tài khoản của tôi</a></li>
								<li><a href="logout.php"><i class="fa fa-sign-out"></i> Đăng xuất</a></li>
							</ul>
						</li>
						<?php
					} else {
						?>
						<li class="account-menu">
							<a href="#"><i class="fa fa-user-circle"></i> Tài khoản <i class="fa fa-angle-down"></i></a>
							<ul class="account-dropdown">
								<li><a href="login.php"><i class="fa fa-sign-in"></i> Đăng nhập</a></li>
								<li><a href="registration.php"><i class="fa fa-user-plus"></i> Đăng ký</a></li>
							</ul>
						</li>
						<?php	
					}
					?>
				</ul>
			</div>
		</div>
	</div>
</div>

<div class="nav">
	<div class="container">
		<div class="row">
			<div class="col-md-12 pl_0 pr_0">
				<div class="menu-container">
					<div class="menu">
						<ul>
							<li><a href="index.php">Trang chủ</a></li>
							
							<?php
							$statement = $pdo->prepare("SELECT DISTINCT t1.tcat_id, t1.tcat_name
								FROM tbl_top_category t1
								JOIN tbl_mid_category t2 ON t2.tcat_id = t1.tcat_id
								JOIN tbl_end_category t3 ON t3.mcat_id = t2.mcat_id
								JOIN tbl_product p ON p.ecat_id = t3.ecat_id
								WHERE t1.show_on_menu=1 AND p.p_is_active=1
								ORDER BY t1.tcat_id ASC");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);
							foreach ($result as $row) {
								?>
								<li><a href="product-category.php?id=<?php echo $row['tcat_id']; ?>&type=top-category"><?php echo $row['tcat_name']; ?></a>
									<ul>
										<?php
										$statement1 = $pdo->prepare("SELECT DISTINCT t2.mcat_id, t2.mcat_name
											FROM tbl_mid_category t2
											JOIN tbl_end_category t3 ON t3.mcat_id = t2.mcat_id
											JOIN tbl_product p ON p.ecat_id = t3.ecat_id
											WHERE t2.tcat_id=? AND p.p_is_active=1
											ORDER BY t2.mcat_id ASC");
										$statement1->execute(array($row['tcat_id']));
										$result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);
										foreach ($result1 as $row1) {
											?>
											<li><a href="product-category.php?id=<?php echo $row1['mcat_id']; ?>&type=mid-category"><?php echo $row1['mcat_name']; ?></a>
												<ul>
													<?php
													$statement2 = $pdo->prepare("SELECT DISTINCT t3.ecat_id, t3.ecat_name
														FROM tbl_end_category t3
														JOIN tbl_product p ON p.ecat_id = t3.ecat_id
														WHERE t3.mcat_id=? AND p.p_is_active=1
														ORDER BY t3.ecat_id ASC");
													$statement2->execute(array($row1['mcat_id']));
													$result2 = $statement2->fetchAll(PDO::FETCH_ASSOC);
													foreach ($result2 as $row2) {
														?>
														<li><a href="product-category.php?id=<?php echo $row2['ecat_id']; ?>&type=end-category"><?php echo $row2['ecat_name']; ?></a></li>
														<?php
													}
													?>
												</ul>
											</li>
											<?php
										}
										?>
									</ul>
								</li>
								<?php
							}
							?>

							<?php
							$statement = $pdo->prepare("SELECT * FROM tbl_page WHERE id=1");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);		
							foreach ($result as $row) {
								$about_title = $row['about_title'];
								$blog_title = $row['blog_title'];
								$contact_title = $row['contact_title'];
								$pgallery_title = $row['pgallery_title'];
								$vgallery_title = $row['vgallery_title'];
							}
							?>

							<li><a href="about.php">Giới thiệu</a></li>
							<li><a href="contact.php">Liên hệ</a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>