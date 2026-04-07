<?php
function get_ext($pdo,$fname)
{

	$up_filename=$_FILES[$fname]["name"];
	$file_basename = substr($up_filename, 0, strripos($up_filename, '.')); // strip extention
	$file_ext = substr($up_filename, strripos($up_filename, '.')); // strip name
	return $file_ext;
}

function ext_check($pdo,$allowed_ext,$my_ext) 
{

	$arr1 = array();
	$arr1 = explode("|",$allowed_ext);	
	$count_arr1 = count(explode("|",$allowed_ext));	

	for($i=0;$i<$count_arr1;$i++)
	{
		$arr1[$i] = '.'.$arr1[$i];
	}
	

	$str = '';
	$stat = 0;
	for($i=0;$i<$count_arr1;$i++)
	{
		if($my_ext == $arr1[$i])
		{
			$stat = 1;
			break;
		}
	}

	if($stat == 1)
		return true; // file extension match
	else
		return false; // file extension not match
}


function get_ai_id($pdo,$tbl_name) 
{
	$statement = $pdo->prepare("SHOW TABLE STATUS LIKE '$tbl_name'");
	$statement->execute();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	foreach($result as $row)
	{
		$next_id = $row['Auto_increment'];
	}
	return $next_id;
}

function format_price_vnd($price)
{
	if($price === null) {
		return '0đ';
	}

	if(is_int($price) || is_float($price)) {
		$value = (float)$price;
	} else {
		$text = trim((string)$price);
		if($text === '') {
			return '0đ';
		}

		// Keep digits and common separators only.
		$text = preg_replace('/[^0-9,\.\-]/', '', $text);
		if($text === '' || $text === '-' || $text === '.' || $text === ',') {
			return '0đ';
		}

		$has_comma = strpos($text, ',') !== false;
		$has_dot = strpos($text, '.') !== false;
		$normalized = $text;

		if($has_comma && $has_dot) {
			$last_comma = strrpos($normalized, ',');
			$last_dot = strrpos($normalized, '.');
			if($last_dot !== false && $last_comma !== false && $last_dot > $last_comma) {
				// 1,234.56 -> remove thousand commas.
				$normalized = str_replace(',', '', $normalized);
			} else {
				// 1.234,56 -> remove thousand dots and convert decimal comma.
				$normalized = str_replace('.', '', $normalized);
				$normalized = str_replace(',', '.', $normalized);
			}
		} elseif($has_comma) {
			$comma_count = substr_count($normalized, ',');
			if($comma_count > 1) {
				// 1,234,567 -> thousands.
				$normalized = str_replace(',', '', $normalized);
			} else {
				$pos = strrpos($normalized, ',');
				$digits_after = ($pos === false) ? 0 : (strlen($normalized) - $pos - 1);
				if($digits_after === 3) {
					// 300,000 -> thousands.
					$normalized = str_replace(',', '', $normalized);
				} else {
					// 1234,56 -> decimal comma.
					$normalized = str_replace(',', '.', $normalized);
				}
			}
		} elseif($has_dot) {
			$dot_count = substr_count($normalized, '.');
			if($dot_count > 1) {
				// 1.234.567 -> thousands.
				$normalized = str_replace('.', '', $normalized);
			}
			// Single dot is treated as decimal separator.
		}

		$value = (float)$normalized;
	}

	// Avoid NaN/INF displays.
	if($value !== $value || $value === INF || $value === -INF) {
		return '0đ';
	}

	$value = round($value);
	if($value == -0.0) {
		$value = 0;
	}
	return number_format((float)$value, 0, '.', ',').'đ';
}

function admin_get_category_breadcrumb(PDO $pdo, $end_category_id)
{
	$end_category_id = (int)$end_category_id;
	if($end_category_id <= 0) {
		return '';
	}

	static $breadcrumb_map = null;
	if($breadcrumb_map === null) {
		$breadcrumb_map = array();
		try {
			$statement = $pdo->prepare("SELECT 
				t2.ecat_id,
				t2.ecat_name,
				t3.mcat_name,
				t4.tcat_name
			FROM tbl_end_category t2
			JOIN tbl_mid_category t3 ON t2.mcat_id = t3.mcat_id
			JOIN tbl_top_category t4 ON t3.tcat_id = t4.tcat_id");
			$statement->execute();
			$rows = $statement->fetchAll(PDO::FETCH_ASSOC);
			foreach($rows as $row) {
				$parts = array();
				$tcat = isset($row['tcat_name']) ? trim((string)$row['tcat_name']) : '';
				$mcat = isset($row['mcat_name']) ? trim((string)$row['mcat_name']) : '';
				$ecat = isset($row['ecat_name']) ? trim((string)$row['ecat_name']) : '';

				if($tcat !== '') { $parts[] = $tcat; }
				if($mcat !== '') { $parts[] = $mcat; }
				if($ecat !== '') { $parts[] = $ecat; }

				$breadcrumb_map[(int)$row['ecat_id']] = implode(' > ', $parts);
			}
		} catch(Exception $exception) {
			$breadcrumb_map = array();
		}
	}

	return isset($breadcrumb_map[$end_category_id]) ? (string)$breadcrumb_map[$end_category_id] : '';
}

function normalize_admin_richtext($value)
{
	$text = trim((string)$value);
	if($text === '') {
		return '';
	}

	$text = str_replace("\r\n", "\n", $text);
	$text = str_replace("\r", "\n", $text);

	// Keep existing HTML content unchanged when users paste from editor.
	if(preg_match('/<\s*\/?\s*[a-z][^>]*>/i', $text)) {
		return $text;
	}

	$paragraphs = preg_split('/\n{2,}/', $text);
	$output = array();
	foreach($paragraphs as $paragraph) {
		$paragraph = trim($paragraph);
		if($paragraph === '') {
			continue;
		}

		$output[] = '<p>'.nl2br(htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8')).'</p>';
	}

	return implode('', $output);
}

function admin_richtext_to_plaintext($value)
{
	$text = trim((string)$value);
	if($text === '') {
		return '';
	}

	// Convert common block tags to line breaks before stripping tags.
	$text = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $text);
	$text = preg_replace('/<\s*\/\s*p\s*>/i', "\n\n", $text);
	$text = preg_replace('/<\s*p[^>]*>/i', '', $text);
	$text = preg_replace('/<\s*\/\s*div\s*>/i', "\n", $text);
	$text = preg_replace('/<\s*div[^>]*>/i', '', $text);
	$text = preg_replace('/<\s*\/\s*li\s*>/i', "\n", $text);
	$text = preg_replace('/<\s*li[^>]*>/i', '- ', $text);
	$text = preg_replace('/<\s*\/\s*(ul|ol)\s*>/i', "\n", $text);
	$text = preg_replace('/<\s*(ul|ol)[^>]*>/i', '', $text);

	$text = html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');
	$text = str_replace("\r\n", "\n", $text);
	$text = str_replace("\r", "\n", $text);
	$text = preg_replace('/\n{3,}/', "\n\n", $text);

	return trim($text);
}

function safe_redirect($url)
{
	$target = (string)$url;

	if(!headers_sent()) {
		header('location: '.$target);
		exit;
	}

	echo '<script>window.location.href=' . json_encode($target, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ';</script>';
	echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . '"></noscript>';
	exit;
}

function format_payment_status_vi($status)
{
	$value = trim((string)$status);
	$map = array(
		'Pending' => 'Chưa thanh toán',
		'Unpaid' => 'Chưa thanh toán',
		'Completed' => 'Đã thanh toán',
		'Paid' => 'Đã thanh toán',
		'Processing' => 'Đang xử lý',
		'Failed' => 'Thất bại',
		'Canceled' => 'Đã hủy',
		'Cancelled' => 'Đã hủy',
		'Refunded' => 'Hoàn tiền'
	);

	return isset($map[$value]) ? $map[$value] : $value;
}

function format_shipping_status_vi($status)
{
	$value = normalize_shipping_status_code($status);
	$map = array(
		'Pending' => 'Chờ xác nhận',
		'Shipping' => 'Đang giao',
		'Completed' => 'Đã giao',
		'Failed' => 'Thất bại',
		'Canceled' => 'Đã hủy',
		'Cancelled' => 'Đã hủy'
	);

	return isset($map[$value]) ? $map[$value] : $value;
}

function format_shipping_status_admin_vi($status)
{
	$value = normalize_shipping_status_code($status);
	$map = array(
		'Pending' => 'Đơn mới',
		'Shipping' => 'Đang giao',
		'Completed' => 'Đã giao',
		'Canceled' => 'Đã hủy'
	);

	return isset($map[$value]) ? $map[$value] : $value;
}

function normalize_shipping_status_code($status)
{
	$value = trim((string)$status);
	if($value === 'Processing' || $value === 'Preparing') {
		return 'Pending';
	}
	if($value === 'Shipped') {
		return 'Shipping';
	}
	if($value === 'Delivered') {
		return 'Completed';
	}
	if($value === 'Cancelled') {
		return 'Canceled';
	}

	return $value;
}

function format_payment_method_vi($method)
{
	$value = trim((string)$method);
	$map = array(
		'Cash On Delivery' => 'Thanh toán khi nhận hàng (COD)',
		'Bank Deposit' => 'Chuyển khoản ngân hàng',
		'Online Payment' => 'Thanh toán trực tuyến (chờ xử lý)',
		'Stripe' => 'Thẻ (Stripe)',
		'PayPal' => 'PayPal'
	);

	return isset($map[$value]) ? $map[$value] : $value;
}

function schema_table_exists(PDO $pdo, $table_name)
{
	$statement = $pdo->prepare('SHOW TABLES LIKE ?');
	$statement->execute(array($table_name));
	return $statement->rowCount() > 0;
}

function schema_column_exists(PDO $pdo, $table_name, $column_name)
{
	$statement = $pdo->prepare("SHOW COLUMNS FROM `{$table_name}` LIKE ?");
	$statement->execute(array($column_name));
	return $statement->rowCount() > 0;
}

function ensure_customer_password_storage(PDO $pdo)
{
	static $checked = false;
	if($checked) {
		return;
	}
	$checked = true;

	try {
		if(!schema_table_exists($pdo, 'tbl_customer')) {
			return;
		}
		if(!schema_column_exists($pdo, 'tbl_customer', 'cust_password')) {
			return;
		}

		$statement = $pdo->prepare("SHOW COLUMNS FROM `tbl_customer` LIKE 'cust_password'");
		$statement->execute();
		$column = $statement->fetch(PDO::FETCH_ASSOC);
		if(!$column || !isset($column['Type'])) {
			return;
		}

		$type = strtolower(trim((string)$column['Type']));
		if(preg_match('/varchar\((\d+)\)/', $type, $matches)) {
			$length = (int)$matches[1];
			if($length < 255) {
				$pdo->exec("ALTER TABLE `tbl_customer` MODIFY COLUMN `cust_password` VARCHAR(255) NOT NULL");
			}
		}
	} catch(Exception $exception) {
		// Ignore schema adjustment errors to avoid breaking the storefront.
	}
}

function ensure_project_schema(PDO $pdo)
{
	static $schema_checked = false;
	if($schema_checked) {
		return;
	}
	$schema_checked = true;

	ensure_customer_password_storage($pdo);

	try {
		if(schema_table_exists($pdo, 'tbl_product')) {
			if(!schema_column_exists($pdo, 'tbl_product', 'p_code')) {
				$pdo->exec("ALTER TABLE `tbl_product` ADD COLUMN `p_code` VARCHAR(80) NOT NULL DEFAULT '' AFTER `p_name`");
			}
			if(!schema_column_exists($pdo, 'tbl_product', 'p_unit')) {
				$pdo->exec("ALTER TABLE `tbl_product` ADD COLUMN `p_unit` VARCHAR(30) NOT NULL DEFAULT 'sp' AFTER `p_code`");
			}
			if(!schema_column_exists($pdo, 'tbl_product', 'p_cost_price')) {
				$pdo->exec("ALTER TABLE `tbl_product` ADD COLUMN `p_cost_price` DECIMAL(15,4) NOT NULL DEFAULT 0 AFTER `p_old_price`");
			}
			if(!schema_column_exists($pdo, 'tbl_product', 'p_profit_percent')) {
				$pdo->exec("ALTER TABLE `tbl_product` ADD COLUMN `p_profit_percent` DECIMAL(8,2) NOT NULL DEFAULT 30 AFTER `p_cost_price`");
			}
			if(!schema_column_exists($pdo, 'tbl_product', 'p_low_stock_threshold')) {
				$pdo->exec("ALTER TABLE `tbl_product` ADD COLUMN `p_low_stock_threshold` INT(11) NOT NULL DEFAULT 5 AFTER `p_profit_percent`");
			}

			$pdo->exec("UPDATE `tbl_product` SET `p_code` = CONCAT('SP', LPAD(`p_id`, 5, '0')) WHERE `p_code` IS NULL OR `p_code` = ''");
			$pdo->exec("UPDATE `tbl_product` SET `p_unit` = 'sp' WHERE `p_unit` IS NULL OR `p_unit` = ''");
			$pdo->exec("UPDATE `tbl_product` SET `p_cost_price` = IF(`p_cost_price` <= 0, `p_current_price`, `p_cost_price`)");
			$pdo->exec("UPDATE `tbl_product` SET `p_profit_percent` = IF(`p_profit_percent` < 0, 0, `p_profit_percent`)");
		}

		if(schema_table_exists($pdo, 'tbl_settings') && !schema_column_exists($pdo, 'tbl_settings', 'bank_detail')) {
			$pdo->exec("ALTER TABLE `tbl_settings` ADD COLUMN `bank_detail` TEXT NULL AFTER `cod_on_off`");
		}

		if(!schema_table_exists($pdo, 'tbl_import_receipt')) {
			$pdo->exec(
				"CREATE TABLE `tbl_import_receipt` (
					`receipt_id` INT(11) NOT NULL AUTO_INCREMENT,
					`receipt_code` VARCHAR(50) NOT NULL,
					`import_date` DATETIME NOT NULL,
					`status` VARCHAR(20) NOT NULL DEFAULT 'Draft',
					`note` TEXT NULL,
					`created_by` INT(11) NOT NULL DEFAULT 0,
					`created_at` DATETIME NOT NULL,
					`updated_at` DATETIME NOT NULL,
					`completed_at` DATETIME NULL,
					PRIMARY KEY (`receipt_id`),
					UNIQUE KEY `uk_receipt_code` (`receipt_code`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
			);
		}

		if(!schema_table_exists($pdo, 'tbl_import_receipt_item')) {
			$pdo->exec(
				"CREATE TABLE `tbl_import_receipt_item` (
					`item_id` INT(11) NOT NULL AUTO_INCREMENT,
					`receipt_id` INT(11) NOT NULL,
					`p_id` INT(11) NOT NULL,
					`size_id` INT(11) NOT NULL DEFAULT 0,
					`color_id` INT(11) NOT NULL DEFAULT 0,
					`import_qty` INT(11) NOT NULL DEFAULT 0,
					`import_price` DECIMAL(15,4) NOT NULL DEFAULT 0,
					PRIMARY KEY (`item_id`),
					KEY `idx_receipt_id` (`receipt_id`),
					KEY `idx_product_id` (`p_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
			);
		}

		// Variant support for import receipts (optional): size/color per line item.
		if(schema_table_exists($pdo, 'tbl_import_receipt_item')) {
			if(!schema_column_exists($pdo, 'tbl_import_receipt_item', 'size_id')) {
				$pdo->exec("ALTER TABLE `tbl_import_receipt_item` ADD COLUMN `size_id` INT(11) NOT NULL DEFAULT 0 AFTER `p_id`");
			}
			if(!schema_column_exists($pdo, 'tbl_import_receipt_item', 'color_id')) {
				$pdo->exec("ALTER TABLE `tbl_import_receipt_item` ADD COLUMN `color_id` INT(11) NOT NULL DEFAULT 0 AFTER `size_id`");
			}

			// Some older installs may have a UNIQUE index on (receipt_id, p_id) to prevent duplicate lines.
			// With size/color variants, we must allow multiple rows per product and index by variant.
			try {
				$statement = $pdo->prepare("SHOW INDEX FROM `tbl_import_receipt_item`");
				$statement->execute();
				$index_rows = $statement->fetchAll(PDO::FETCH_ASSOC);

				$indexes = array();
				foreach($index_rows as $row) {
					$name = isset($row['Key_name']) ? (string)$row['Key_name'] : '';
					if($name === '') {
						continue;
					}
					if(!isset($indexes[$name])) {
						$indexes[$name] = array(
							'non_unique' => isset($row['Non_unique']) ? (int)$row['Non_unique'] : 1,
							'columns' => array()
						);
					}
					$seq = isset($row['Seq_in_index']) ? (int)$row['Seq_in_index'] : 0;
					$col = isset($row['Column_name']) ? (string)$row['Column_name'] : '';
					if($seq > 0 && $col !== '') {
						$indexes[$name]['columns'][$seq] = $col;
					}
				}

				foreach($indexes as $name => $info) {
					ksort($indexes[$name]['columns']);
					$indexes[$name]['columns'] = array_values($indexes[$name]['columns']);
				}

				// Drop legacy UNIQUE(receipt_id, p_id)
				foreach($indexes as $name => $info) {
					if($name === 'PRIMARY') {
						continue;
					}
					if(isset($info['non_unique']) && (int)$info['non_unique'] !== 0) {
						continue;
					}
					$cols = isset($info['columns']) && is_array($info['columns']) ? $info['columns'] : array();
					if(count($cols) === 2) {
						$set = array_flip($cols);
						if(isset($set['receipt_id']) && isset($set['p_id'])) {
							$idx_name = str_replace('`', '``', $name);
							$pdo->exec("ALTER TABLE `tbl_import_receipt_item` DROP INDEX `{$idx_name}`");
							break;
						}
					}
				}

				// Ensure helpful composite index for merge queries.
				$want_cols = array('receipt_id', 'p_id', 'size_id', 'color_id');
				$has_composite = false;
				foreach($indexes as $name => $info) {
					$cols = isset($info['columns']) && is_array($info['columns']) ? $info['columns'] : array();
					if(count($cols) !== count($want_cols)) {
						continue;
					}
					$match = true;
					for($i=0;$i<count($want_cols);$i++) {
						if(!isset($cols[$i]) || $cols[$i] !== $want_cols[$i]) {
							$match = false;
							break;
						}
					}
					if($match) {
						$has_composite = true;
						break;
					}
				}
				if(!$has_composite) {
					$pdo->exec("ALTER TABLE `tbl_import_receipt_item` ADD INDEX `idx_receipt_product_variant` (`receipt_id`, `p_id`, `size_id`, `color_id`)");
				}
			} catch(Exception $e) {
				// Ignore index adjustment errors.
			}
		}
	} catch(PDOException $e) {
		// Keep pages alive on shared/legacy hosts where DDL may be restricted.
		error_log('ensure_project_schema warning: '.$e->getMessage());
	}
}
