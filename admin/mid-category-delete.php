<?php require_once('header.php'); ?>

<?php
// Preventing the direct access of this page.
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_mid_category WHERE mcat_id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	if( $total == 0 ) {
		header('location: logout.php');
		exit;
	}
}
?>

<?php
	$ecat_ids = [];
	$p_ids = [];


	// Getting all ecat ids
	$statement = $pdo->prepare("SELECT * FROM tbl_end_category WHERE mcat_id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
	foreach ($result as $row) {
		$ecat_ids[] = $row['ecat_id'];
	}
	$ecat_ids = array_values(array_unique($ecat_ids));

	if(!empty($ecat_ids)) {

		for($i=0;$i<count($ecat_ids);$i++) {
			$statement = $pdo->prepare("SELECT * FROM tbl_product WHERE ecat_id=?");
			$statement->execute(array($ecat_ids[$i]));
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
			foreach ($result as $row) {
				$p_ids[] = $row['p_id'];
			}
		}
		$p_ids = array_values(array_unique($p_ids));

		if(!empty($p_ids)) {

		for($i=0;$i<count($p_ids);$i++) {

			// Getting photo ID to unlink from folder
			$statement = $pdo->prepare("SELECT * FROM tbl_product WHERE p_id=?");
			$statement->execute(array($p_ids[$i]));
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
			foreach ($result as $row) {
				$p_featured_photo = $row['p_featured_photo'];
				if(!empty($p_featured_photo)) {
					$target = '../assets/uploads/'.$p_featured_photo;
					if(is_file($target)) {
						unlink($target);
					}
				}
			}

			// Getting other photo ID to unlink from folder
			$statement = $pdo->prepare("SELECT * FROM tbl_product_photo WHERE p_id=?");
			$statement->execute(array($p_ids[$i]));
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
			foreach ($result as $row) {
				$photo = $row['photo'];
				if(!empty($photo)) {
					$target = '../assets/uploads/product_photos/'.$photo;
					if(is_file($target)) {
						unlink($target);
					}
				}
			}

			// Xóa from tbl_photo
			$statement = $pdo->prepare("DELETE FROM tbl_product WHERE p_id=?");
			$statement->execute(array($p_ids[$i]));

			// Xóa from tbl_product_photo
			$statement = $pdo->prepare("DELETE FROM tbl_product_photo WHERE p_id=?");
			$statement->execute(array($p_ids[$i]));

			// Xóa from tbl_product_size
			$statement = $pdo->prepare("DELETE FROM tbl_product_size WHERE p_id=?");
			$statement->execute(array($p_ids[$i]));

			// Xóa from tbl_product_color
			$statement = $pdo->prepare("DELETE FROM tbl_product_color WHERE p_id=?");
			$statement->execute(array($p_ids[$i]));
			
			$statement = $pdo->prepare("SHOW TABLES LIKE 'tbl_product_variant'");
			$statement->execute();
			if($statement->rowCount() > 0) {
				$statement = $pdo->prepare("DELETE FROM tbl_product_variant WHERE p_id=?");
				$statement->execute(array($p_ids[$i]));
			}

			// Xóa from tbl_rating
			$statement = $pdo->prepare("DELETE FROM tbl_rating WHERE p_id=?");
			$statement->execute(array($p_ids[$i]));

			// Xóa from tbl_order (xóa trước, sau đó chỉ xóa payment nếu mồ côi)
			$statement = $pdo->prepare("SELECT DISTINCT payment_id FROM tbl_order WHERE product_id=?");
			$statement->execute(array($p_ids[$i]));
			$payment_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

			$statement = $pdo->prepare("DELETE FROM tbl_order WHERE product_id=?");
			$statement->execute(array($p_ids[$i]));

			if(!empty($payment_ids)) {
				foreach ($payment_ids as $payment_id) {
					$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_order WHERE payment_id=?");
					$statement->execute(array($payment_id));
					$remaining = (int)$statement->fetchColumn();
					if($remaining === 0) {
						$statement = $pdo->prepare("DELETE FROM tbl_payment WHERE payment_id=?");
						$statement->execute(array($payment_id));
					}
				}
			}
		}
		}

		// Xóa from tbl_end_category
		for($i=0;$i<count($ecat_ids);$i++) {
			$statement = $pdo->prepare("DELETE FROM tbl_end_category WHERE ecat_id=?");
			$statement->execute(array($ecat_ids[$i]));
		}

	}

	// Xóa from tbl_mid_category
	$statement = $pdo->prepare("DELETE FROM tbl_mid_category WHERE mcat_id=?");
	$statement->execute(array($_REQUEST['id']));

	header('location: mid-category.php');
?>
