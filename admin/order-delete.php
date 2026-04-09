<?php require_once('header.php'); ?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	if( $total == 0 ) {
		header('location: logout.php');
		exit;
	} else {
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
		foreach ($result as $row) {
			$payment_id = $row['payment_id'];
			$payment_status = $row['payment_status'];
			$shipping_status = $row['shipping_status'];
		}
	}
}
?>

<?php
	
	if( ($payment_status == 'Completed') && ($shipping_status == 'Completed') ):
		// No return to stock
	else:
		// Return the stock
		$variant_table_exists = false;
		$statement_variant_check = $pdo->prepare("SHOW TABLES LIKE 'tbl_product_variant'");
		$statement_variant_check->execute();
		$variant_table_exists = ($statement_variant_check->rowCount() > 0);

		$size_id_cache = array();
		$color_id_cache = array();

		$statement = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id=?");
		$statement->execute(array($payment_id));
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
		foreach ($result as $row) {
			$statement1 = $pdo->prepare("SELECT * FROM tbl_product WHERE p_id=?");
			$statement1->execute(array($row['product_id']));
			$result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);							
			foreach ($result1 as $row1) {
				$p_qty = $row1['p_qty'];
			}
			$final = $p_qty + $row['quantity'];
			$statement1 = $pdo->prepare("UPDATE tbl_product SET p_qty=? WHERE p_id=?");
			$statement1->execute(array($final,$row['product_id']));

			if($variant_table_exists) {
				$size_name = isset($row['size']) ? trim((string)$row['size']) : '';
				$color_name = isset($row['color']) ? trim((string)$row['color']) : '';

				if($size_name !== '' && $color_name !== '') {
					if(array_key_exists($size_name, $size_id_cache)) {
						$size_id = (int)$size_id_cache[$size_name];
					} else {
						$statement_size = $pdo->prepare("SELECT size_id FROM tbl_size WHERE size_name=? LIMIT 1");
						$statement_size->execute(array($size_name));
						$size_row = $statement_size->fetch(PDO::FETCH_ASSOC);
						$size_id = $size_row ? (int)$size_row['size_id'] : 0;
						$size_id_cache[$size_name] = $size_id;
					}

					if(array_key_exists($color_name, $color_id_cache)) {
						$color_id = (int)$color_id_cache[$color_name];
					} else {
						$statement_color = $pdo->prepare("SELECT color_id FROM tbl_color WHERE color_name=? LIMIT 1");
						$statement_color->execute(array($color_name));
						$color_row = $statement_color->fetch(PDO::FETCH_ASSOC);
						$color_id = $color_row ? (int)$color_row['color_id'] : 0;
						$color_id_cache[$color_name] = $color_id;
					}

					if($size_id > 0 && $color_id > 0) {
						$statement_variant = $pdo->prepare("SELECT pv_qty FROM tbl_product_variant WHERE p_id=? AND size_id=? AND color_id=? LIMIT 1");
						$statement_variant->execute(array($row['product_id'], $size_id, $color_id));
						$variant_row = $statement_variant->fetch(PDO::FETCH_ASSOC);

						if($variant_row) {
							$next_variant_qty = ((int)$variant_row['pv_qty']) + (int)$row['quantity'];
							$statement_variant_update = $pdo->prepare("UPDATE tbl_product_variant SET pv_qty=? WHERE p_id=? AND size_id=? AND color_id=?");
							$statement_variant_update->execute(array($next_variant_qty, $row['product_id'], $size_id, $color_id));
						}
					}
				}
			}
		}	
	endif;	

	// Xóa from tbl_order
	$statement = $pdo->prepare("DELETE FROM tbl_order WHERE payment_id=?");
	$statement->execute(array($payment_id));

	// Xóa from tbl_payment
	$statement = $pdo->prepare("DELETE FROM tbl_payment WHERE id=?");
	$statement->execute(array($_REQUEST['id']));

	header('location: order.php');
?>
