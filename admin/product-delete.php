<?php require_once('header.php'); ?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_product WHERE p_id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	if( $total == 0 ) {
		header('location: logout.php');
		exit;
	}
}
?>

<?php
	try {
		// Getting photo ID to unlink from folder
		$statement = $pdo->prepare("SELECT * FROM tbl_product WHERE p_id=?");
		$statement->execute(array($_REQUEST['id']));
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as $row) {
			$p_featured_photo = $row['p_featured_photo'];
			$featured_path = '../assets/uploads/'.$p_featured_photo;
			if($p_featured_photo !== '' && file_exists($featured_path)) {
				unlink($featured_path);
			}
		}

		// Getting other photo ID to unlink from folder
		$statement = $pdo->prepare("SELECT * FROM tbl_product_photo WHERE p_id=?");
		$statement->execute(array($_REQUEST['id']));
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as $row) {
			$photo = $row['photo'];
			$photo_path = '../assets/uploads/product_photos/'.$photo;
			if($photo !== '' && file_exists($photo_path)) {
				unlink($photo_path);
			}
		}

		$pdo->beginTransaction();

		// Thu thập payment_id liên quan để dọn payment mồ côi sau khi xóa order dòng sản phẩm này
		$statement = $pdo->prepare("SELECT DISTINCT payment_id FROM tbl_order WHERE product_id=?");
		$statement->execute(array($_REQUEST['id']));
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		$product_payment_ids = array();
		foreach ($result as $row) {
			$payment_id = isset($row['payment_id']) ? trim((string)$row['payment_id']) : '';
			if($payment_id !== '') {
				$product_payment_ids[] = $payment_id;
			}
		}

		// Xóa from tbl_order
		$statement = $pdo->prepare("DELETE FROM tbl_order WHERE product_id=?");
		$statement->execute(array($_REQUEST['id']));

		// Chỉ xóa payment nếu không còn order nào tham chiếu payment_id đó.
		for($i=0;$i<count($product_payment_ids);$i++) {
			$payment_id = $product_payment_ids[$i];
			$statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_order WHERE payment_id=?");
			$statement->execute(array($payment_id));
			$order_count_for_payment = (int)$statement->fetchColumn();
			if($order_count_for_payment === 0) {
				$statement = $pdo->prepare("DELETE FROM tbl_payment WHERE payment_id=?");
				$statement->execute(array($payment_id));
			}
		}

		// Xóa from tbl_product_photo
		$statement = $pdo->prepare("DELETE FROM tbl_product_photo WHERE p_id=?");
		$statement->execute(array($_REQUEST['id']));

		// Xóa from tbl_product_size
		$statement = $pdo->prepare("DELETE FROM tbl_product_size WHERE p_id=?");
		$statement->execute(array($_REQUEST['id']));

		// Xóa from tbl_product_color
		$statement = $pdo->prepare("DELETE FROM tbl_product_color WHERE p_id=?");
		$statement->execute(array($_REQUEST['id']));

		$statement = $pdo->prepare("SHOW TABLES LIKE 'tbl_product_variant'");
		$statement->execute();
		if($statement->rowCount() > 0) {
			$statement = $pdo->prepare("DELETE FROM tbl_product_variant WHERE p_id=?");
			$statement->execute(array($_REQUEST['id']));
		}

		// Xóa from tbl_rating
		$statement = $pdo->prepare("DELETE FROM tbl_rating WHERE p_id=?");
		$statement->execute(array($_REQUEST['id']));

		// Xóa from tbl_product ở cuối cùng để tránh lỗi FK
		$statement = $pdo->prepare("DELETE FROM tbl_product WHERE p_id=?");
		$statement->execute(array($_REQUEST['id']));

		$pdo->commit();
		header('location: product.php?success=deleted');
		exit;
	} catch (Exception $e) {
		if($pdo->inTransaction()) {
			$pdo->rollBack();
		}
		error_log('product-delete failed for p_id=' . (isset($_REQUEST['id']) ? $_REQUEST['id'] : 'unknown') . ': ' . $e->getMessage());
		header('location: product.php?error=delete_failed');
		exit;
	}
?>
