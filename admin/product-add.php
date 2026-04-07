<?php require_once('header.php'); ?>

<?php
function product_to_int_array($values) {
	if(!is_array($values)) {
		return array();
	}
	$result = array();
	foreach($values as $value) {
		$num = (int)$value;
		if($num > 0) {
			$result[$num] = $num;
		}
	}
	return array_values($result);
}

function product_collect_variant_rows($post_values) {
	$variant_size_ids = isset($post_values['variant_size_id']) && is_array($post_values['variant_size_id']) ? $post_values['variant_size_id'] : array();
	$variant_color_ids = isset($post_values['variant_color_id']) && is_array($post_values['variant_color_id']) ? $post_values['variant_color_id'] : array();
	$variant_qtys = isset($post_values['variant_qty']) && is_array($post_values['variant_qty']) ? $post_values['variant_qty'] : array();
	$variant_count = min(count($variant_size_ids), count($variant_color_ids), count($variant_qtys));

	$rows = array();
	$size_set = array();
	$color_set = array();
	$seen_keys = array();
	$error_message = '';

	for($i=0;$i<$variant_count;$i++) {
		$raw_size = isset($variant_size_ids[$i]) ? trim((string)$variant_size_ids[$i]) : '';
		$raw_color = isset($variant_color_ids[$i]) ? trim((string)$variant_color_ids[$i]) : '';
		$raw_qty = isset($variant_qtys[$i]) ? trim((string)$variant_qtys[$i]) : '';

		if($raw_size === '' && $raw_color === '' && $raw_qty === '') {
			continue;
		}

		$v_size_id = (int)$raw_size;
		$v_color_id = (int)$raw_color;
		$v_qty = (int)preg_replace('/[^0-9]/', '', $raw_qty);

		if($v_size_id <= 0 || $v_color_id <= 0) {
			$error_message .= 'Dòng #'.($i+1).' chưa chọn đủ kích cỡ và màu sắc<br>';
			continue;
		}

		$key = $v_size_id.'_'.$v_color_id;
		if(isset($seen_keys[$key])) {
			$error_message .= 'Dòng #'.($i+1).' bị trùng size-màu với dòng khác<br>';
			continue;
		}
		$seen_keys[$key] = 1;

		$rows[] = array(
			'size_id' => $v_size_id,
			'color_id' => $v_color_id,
			'qty' => $v_qty
		);
		$size_set[$v_size_id] = $v_size_id;
		$color_set[$v_color_id] = $v_color_id;
	}

	return array(
		'rows' => $rows,
		'size_ids' => array_values($size_set),
		'color_ids' => array_values($color_set),
		'error_message' => $error_message
	);
}

function product_save_photo_items($pdo, $product_id, $names, $tmp_names, $errors, $sizes = array(), $color_id = null) {
	$allowed_ext = array('png');
	$max_photo_size = 10 * 1024 * 1024;
	for($i=0;$i<count($names);$i++) {
		$origin_name = isset($names[$i]) ? trim((string)$names[$i]) : '';
		if($origin_name === '') {
			continue;
		}

		$error_code = isset($errors[$i]) ? (int)$errors[$i] : UPLOAD_ERR_OK;
		if($error_code !== UPLOAD_ERR_OK) {
			continue;
		}

		$file_size = isset($sizes[$i]) ? (int)$sizes[$i] : 0;
		if($file_size > $max_photo_size) {
			continue;
		}

		$ext = strtolower((string)pathinfo($origin_name, PATHINFO_EXTENSION));
		if(!in_array($ext, $allowed_ext, true)) {
			continue;
		}

		$tmp_path = isset($tmp_names[$i]) ? $tmp_names[$i] : '';
		if($tmp_path === '') {
			continue;
		}

		$file_name = 'product-photo-'.$product_id.'-'.date('YmdHis').'-'.mt_rand(1000,9999).'-'.$i.'.'.$ext;
		if(move_uploaded_file($tmp_path, '../assets/uploads/product_photos/'.$file_name)) {
			$statement = $pdo->prepare("INSERT INTO tbl_product_photo (photo,p_id,color_id) VALUES (?,?,?)");
			$statement->execute(array($file_name, $product_id, $color_id));
		}
	}
}

function product_files_are_png_only($filesBag) {
	if(!is_array($filesBag) || !isset($filesBag['name'])) {
		return true;
	}

	$stack = array($filesBag['name']);
	while(!empty($stack)) {
		$current = array_pop($stack);
		if(is_array($current)) {
			foreach($current as $value) {
				$stack[] = $value;
			}
			continue;
		}

		$file_name = trim((string)$current);
		if($file_name === '') {
			continue;
		}
		$ext = strtolower((string)pathinfo($file_name, PATHINFO_EXTENSION));
		if($ext !== 'png') {
			return false;
		}
	}

	return true;
}

function product_collect_uploaded_color_photo_count($filesBag) {
	$result = array();
	if(!is_array($filesBag) || !isset($filesBag['name']) || !is_array($filesBag['name'])) {
		return $result;
	}
	foreach($filesBag['name'] as $raw_color_id => $color_files) {
		$color_id = (int)$raw_color_id;
		if($color_id <= 0 || !is_array($color_files)) {
			continue;
		}
		$count = 0;
		for($i=0;$i<count($color_files);$i++) {
			$name = isset($color_files[$i]) ? trim((string)$color_files[$i]) : '';
			$err = isset($filesBag['error'][$raw_color_id][$i]) ? (int)$filesBag['error'][$raw_color_id][$i] : UPLOAD_ERR_NO_FILE;
			if($name !== '' && $err !== UPLOAD_ERR_NO_FILE) {
				$count++;
			}
		}
		if($count > 0) {
			$result[$color_id] = $count;
		}
	}
	return $result;
}

function product_collect_uploaded_color_photo_ids($filesBag) {
	return array_map('intval', array_keys(product_collect_uploaded_color_photo_count($filesBag)));
}

function product_parse_decimal_input($value) {
	$normalized = str_replace(',', '.', trim((string)$value));
	$normalized = preg_replace('/[^0-9.]/', '', $normalized);
	if($normalized === '') {
		return 0;
	}
	$parts = explode('.', $normalized);
	if(count($parts) > 2) {
		$normalized = array_shift($parts).'.'.implode('', $parts);
	}
	return (float)$normalized;
}

$variant_form_rows = array();
$photo_color_selected = array();

if(isset($_POST['form1'])) {
	$valid = 1;
	$_POST['p_code'] = isset($_POST['p_code']) ? strtoupper(trim((string)$_POST['p_code'])) : '';
	$_POST['p_unit'] = isset($_POST['p_unit']) ? trim((string)$_POST['p_unit']) : 'sp';
	$_POST['p_old_price'] = isset($_POST['p_old_price']) ? preg_replace('/[^0-9]/', '', $_POST['p_old_price']) : '';
	$_POST['p_current_price'] = isset($_POST['p_current_price']) ? preg_replace('/[^0-9]/', '', $_POST['p_current_price']) : '';
	$_POST['p_cost_price'] = isset($_POST['p_cost_price']) ? preg_replace('/[^0-9]/', '', $_POST['p_cost_price']) : '';
	$_POST['p_profit_percent'] = isset($_POST['p_profit_percent']) ? trim((string)$_POST['p_profit_percent']) : '30';
	$_POST['p_low_stock_threshold'] = isset($_POST['p_low_stock_threshold']) ? preg_replace('/[^0-9]/', '', $_POST['p_low_stock_threshold']) : '5';
	$variant_payload = product_collect_variant_rows($_POST);
	$variant_rows = $variant_payload['rows'];
	$variant_form_rows = $variant_rows;
	$photo_color_selected = product_to_int_array(isset($_POST['photo_color_selected']) ? $_POST['photo_color_selected'] : array());
	$selected_size_ids = $variant_payload['size_ids'];
	$selected_color_ids = $variant_payload['color_ids'];
	$profit_percent_value = product_parse_decimal_input($_POST['p_profit_percent']);
	if($profit_percent_value < 0) {
		$profit_percent_value = 0;
	}
	$_POST['p_profit_percent'] = number_format($profit_percent_value, 2, '.', '');

    if(empty($_POST['tcat_id'])) {
        $valid = 0;
		$error_message .= "Bạn phải chọn danh mục cấp 1<br>";
    }

    if(empty($_POST['mcat_id'])) {
        $valid = 0;
		$error_message .= "Bạn phải chọn danh mục cấp 2<br>";
    }

    if(empty($_POST['ecat_id'])) {
        $valid = 0;
		$error_message .= "Bạn phải chọn danh mục cấp 3<br>";
    }

    if(empty($_POST['p_name'])) {
        $valid = 0;
		$error_message .= "Tên sản phẩm không được để trống<br>";
    }

	if(empty($_POST['p_unit'])) {
		$_POST['p_unit'] = 'sp';
	}

	if(empty($_POST['p_cost_price'])) {
		$valid = 0;
		$error_message .= "Giá vốn không được để trống<br>";
	}

	if((float)$_POST['p_cost_price'] <= 0) {
		$valid = 0;
		$error_message .= "Giá vốn phải lớn hơn 0<br>";
	}

	if((float)$profit_percent_value < 0) {
		$valid = 0;
		$error_message .= "Tỉ lệ lợi nhuận không hợp lệ<br>";
	}

	if((float)$_POST['p_cost_price'] > 0) {
		$calculated_sale_price = (int)round((float)$_POST['p_cost_price'] * (1 + ($profit_percent_value / 100)));
		if($calculated_sale_price < 1) {
			$calculated_sale_price = 1;
		}
		$_POST['p_current_price'] = (string)$calculated_sale_price;
		if($_POST['p_old_price'] === '') {
			$_POST['p_old_price'] = $_POST['p_current_price'];
		}
	}

	if($_POST['p_code'] !== '') {
		$statement = $pdo->prepare("SELECT p_id FROM tbl_product WHERE p_code=? LIMIT 1");
		$statement->execute(array($_POST['p_code']));
		if($statement->fetch(PDO::FETCH_ASSOC)) {
			$valid = 0;
			$error_message .= "Mã sản phẩm đã tồn tại<br>";
		}
	}

	if($_POST['p_low_stock_threshold'] === '') {
		$_POST['p_low_stock_threshold'] = '0';
	}

	if($variant_payload['error_message'] !== '') {
		$valid = 0;
		$error_message .= $variant_payload['error_message'];
	}

	$uploaded_color_photo_count = product_collect_uploaded_color_photo_count(isset($_FILES['color_photo']) ? $_FILES['color_photo'] : array());
	$uploaded_color_photo_ids = product_collect_uploaded_color_photo_ids(isset($_FILES['color_photo']) ? $_FILES['color_photo'] : array());
	$effective_photo_color_ids = array_values(array_unique(array_merge($photo_color_selected, $uploaded_color_photo_ids)));

	for($v=0;$v<count($variant_rows);$v++) {
		$row_color_id = (int)$variant_rows[$v]['color_id'];
		if($row_color_id > 0 && !in_array($row_color_id, $effective_photo_color_ids, true)) {
			$valid = 0;
			$error_message .= 'Màu ở biến thể phải thuộc danh sách màu đã chọn trong bảng Ảnh theo màu<br>';
			break;
		}
	}

	for($i=0;$i<count($effective_photo_color_ids);$i++) {
		$cid = (int)$effective_photo_color_ids[$i];
		if($cid <= 0) {
			continue;
		}
		$uploadedCount = isset($uploaded_color_photo_count[$cid]) ? (int)$uploaded_color_photo_count[$cid] : 0;
		if($uploadedCount <= 0) {
			$valid = 0;
			$error_message .= 'Mỗi màu trong bảng Ảnh theo màu phải có ít nhất 1 ảnh (màu ID: '.$cid.')<br>';
		}
	}

    $path = $_FILES['p_featured_photo']['name'];
    $path_tmp = $_FILES['p_featured_photo']['tmp_name'];

    if($path!='') {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( strtolower((string)$ext) != 'png' ) {
            $valid = 0;
			$error_message .= 'Ảnh đại diện chỉ chấp nhận định dạng PNG<br>';
        }
    } else {
    	$valid = 0;
		$error_message .= 'Bạn phải chọn ảnh đại diện<br>';
    }

	if(!product_files_are_png_only(isset($_FILES['photo']) ? $_FILES['photo'] : array())) {
		$valid = 0;
		$error_message .= 'Ảnh mô tả chỉ chấp nhận định dạng PNG<br>';
	}

	if(!product_files_are_png_only(isset($_FILES['color_photo']) ? $_FILES['color_photo'] : array())) {
		$valid = 0;
		$error_message .= 'Ảnh theo màu chỉ chấp nhận định dạng PNG<br>';
	}


    if($valid == 1) {
		$statement = $pdo->prepare("SHOW TABLES LIKE 'tbl_product_variant'");
		$statement->execute();
		if($statement->rowCount() === 0) {
			$valid = 0;
			$error_message .= "CSDL chưa có bảng tbl_product_variant. Vui lòng chạy migration trong thư mục database trước.<br>";
		}

		$statement = $pdo->prepare("SHOW COLUMNS FROM tbl_product_photo LIKE 'color_id'");
		$statement->execute();
		if($statement->rowCount() === 0) {
			$valid = 0;
			$error_message .= "CSDL chưa có cột color_id trong tbl_product_photo. Vui lòng chạy migration trong thư mục database trước.<br>";
		}
	}

	if($valid == 1) {
		$p_description = normalize_admin_richtext(isset($_POST['p_description']) ? $_POST['p_description'] : '');
		$p_short_description = normalize_admin_richtext(isset($_POST['p_short_description']) ? $_POST['p_short_description'] : '');
		$p_feature = normalize_admin_richtext(isset($_POST['p_feature']) ? $_POST['p_feature'] : '');
		$p_condition = normalize_admin_richtext(isset($_POST['p_condition']) ? $_POST['p_condition'] : '');
		$p_return_policy = normalize_admin_richtext(isset($_POST['p_return_policy']) ? $_POST['p_return_policy'] : '');
		$variant_total_qty = 0;
		for($v=0;$v<count($variant_rows);$v++) {
			$variant_total_qty += (int)$variant_rows[$v]['qty'];
		}

    	$statement = $pdo->prepare("SHOW TABLE STATUS LIKE 'tbl_product'");
		$statement->execute();
		$result = $statement->fetchAll();
		foreach($result as $row) {
			$ai_id=$row[10];
		}

		if($_POST['p_code'] === '') {
			$_POST['p_code'] = 'SP'.str_pad((string)$ai_id, 5, '0', STR_PAD_LEFT);
		}

		$final_name = 'product-featured-'.$ai_id.'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

		if( isset($_FILES['photo']["name"]) && isset($_FILES['photo']["tmp_name"]) ) {
			$photo_names = is_array($_FILES['photo']["name"]) ? array_values($_FILES['photo']["name"]) : array();
			$photo_tmp = is_array($_FILES['photo']["tmp_name"]) ? array_values($_FILES['photo']["tmp_name"]) : array();
			$photo_errors = is_array($_FILES['photo']["error"]) ? array_values($_FILES['photo']["error"]) : array();
			$photo_sizes = is_array($_FILES['photo']["size"]) ? array_values($_FILES['photo']["size"]) : array();
			product_save_photo_items($pdo, $ai_id, $photo_names, $photo_tmp, $photo_errors, $photo_sizes, null);
		}

		//Saving data into the main table tbl_product
		$statement = $pdo->prepare("INSERT INTO tbl_product(
										p_name,
										p_code,
										p_unit,
										p_old_price,
										p_cost_price,
										p_profit_percent,
										p_low_stock_threshold,
										p_current_price,
										p_qty,
										p_featured_photo,
										p_description,
										p_short_description,
										p_feature,
										p_condition,
										p_return_policy,
										p_total_view,
										p_is_featured,
										p_is_active,
										ecat_id
									) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
		$statement->execute(array(
										$_POST['p_name'],
										$_POST['p_code'],
										$_POST['p_unit'],
										$_POST['p_old_price'],
										$_POST['p_cost_price'],
										$_POST['p_profit_percent'],
										$_POST['p_low_stock_threshold'],
										$_POST['p_current_price'],
									$variant_total_qty,
										$final_name,
									$p_description,
									$p_short_description,
									$p_feature,
									$p_condition,
									$p_return_policy,
										0,
										$_POST['p_is_featured'],
										$_POST['p_is_active'],
										$_POST['ecat_id']
									));

		foreach($selected_size_ids as $size_id) {
			$statement = $pdo->prepare("INSERT INTO tbl_product_size (size_id,p_id) VALUES (?,?)");
			$statement->execute(array($size_id,$ai_id));
		}

		$final_color_ids = array_values(array_unique(array_merge($selected_color_ids, $effective_photo_color_ids)));
		foreach($final_color_ids as $color_id) {
			$statement = $pdo->prepare("INSERT INTO tbl_product_color (color_id,p_id) VALUES (?,?)");
			$statement->execute(array($color_id,$ai_id));
		}

		for($i=0;$i<count($variant_rows);$i++) {
			$v_size_id = (int)$variant_rows[$i]['size_id'];
			$v_color_id = (int)$variant_rows[$i]['color_id'];
			$v_qty = (int)$variant_rows[$i]['qty'];
			$statement = $pdo->prepare("INSERT INTO tbl_product_variant (p_id,size_id,color_id,pv_qty) VALUES (?,?,?,?)");
			$statement->execute(array($ai_id, $v_size_id, $v_color_id, $v_qty));
		}

		if(isset($_FILES['color_photo']) && isset($_FILES['color_photo']['name']) && is_array($_FILES['color_photo']['name'])) {
			foreach($_FILES['color_photo']['name'] as $raw_color_id => $color_files) {
				$color_id = (int)$raw_color_id;
				if($color_id <= 0 || !is_array($color_files)) {
					continue;
				}
				$tmp_color_files = isset($_FILES['color_photo']['tmp_name'][$raw_color_id]) && is_array($_FILES['color_photo']['tmp_name'][$raw_color_id]) ? $_FILES['color_photo']['tmp_name'][$raw_color_id] : array();
				$err_color_files = isset($_FILES['color_photo']['error'][$raw_color_id]) && is_array($_FILES['color_photo']['error'][$raw_color_id]) ? $_FILES['color_photo']['error'][$raw_color_id] : array();
				$size_color_files = isset($_FILES['color_photo']['size'][$raw_color_id]) && is_array($_FILES['color_photo']['size'][$raw_color_id]) ? $_FILES['color_photo']['size'][$raw_color_id] : array();
				product_save_photo_items($pdo, $ai_id, array_values($color_files), array_values($tmp_color_files), array_values($err_color_files), array_values($size_color_files), $color_id);
			}
		}
	
	    $success_message = 'Thêm sản phẩm thành công.';
    }
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Thêm sản phẩm</h1>
	</div>
	<div class="content-header-right">
		<a href="product.php" class="btn btn-primary btn-sm">Xem danh sách</a>
	</div>
</section>

<style>
.product-add-form .box {
	border-top: 0;
	box-shadow: 0 10px 28px rgba(17, 24, 39, 0.08);
	border-radius: 0;
}

.product-add-form .form-group {
	margin-bottom: 18px;
}

.product-add-form .control-label {
	font-weight: 600;
	color: #2f3b52;
}

.product-add-form .form-section-card {
	background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
	border: 1px solid #e5eaf2;
	border-radius: 0;
	padding: 18px;
	margin-bottom: 18px;
}

.product-add-form .section-title {
	margin: 0 0 14px;
	font-size: 17px;
	font-weight: 700;
	color: #243447;
	padding-bottom: 10px;
	border-bottom: 1px solid #e9edf5;
}

.product-add-form .form-control,
.product-add-form .select2-container--default .select2-selection--single,
.product-add-form .select2-container--default .select2-selection--multiple,
.product-add-form .btn,
.product-add-form input[type="file"] {
	border-radius: 0 !important;
}

.product-add-form .form-control {
	min-height: 42px;
	border-color: #d8e0ec;
	box-shadow: none;
}

.product-add-form .product-multiselect {
	min-height: 220px !important;
	padding: 6px 10px;
	overflow-y: auto;
}

.product-add-form .product-multiselect option {
	padding: 4px 6px;
}

.product-add-form .hint-text {
	display: inline-block;
	font-size: 11px;
	font-weight: 500;
	color: #6c757d;
}

.product-add-form .status-select {
	max-width: 190px;
}

.product-add-form .featured-preview,
.product-add-form .other-photo-preview {
	border: 1px solid #dee2e6;
	border-radius: 0;
	padding: 3px;
	background: #fff;
}

.product-add-form .featured-preview {
	width: 150px;
}

.product-add-form .other-photo-preview {
	width: 120px;
	margin-bottom: 6px;
}

.product-add-form .variant-check-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
	gap: 8px;
	border: 1px solid #dde3ec;
	padding: 10px;
	background: #fcfdff;
}

.product-add-form .variant-check-item {
	display: flex;
	align-items: center;
	gap: 8px;
	font-weight: 500;
	margin: 0;
}

.product-add-form .variant-table-wrap {
	border: 1px solid #dde3ec;
	background: #fff;
	padding: 10px;
	overflow-x: auto;
	border-radius: 0;
}

.product-add-form .status-inline-grid {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 12px;
}

.product-add-form .status-inline-item label {
	display: block;
	margin-bottom: 6px;
	font-size: 12px;
	font-weight: 600;
	color: #5a667a;
}

.product-add-form .variant-table {
	width: 100%;
	min-width: 560px;
	margin: 0;
}

.product-add-form .variant-table th,
.product-add-form .variant-table td {
	vertical-align: middle;
	white-space: nowrap;
}

.product-add-form .variant-empty {
	margin: 0;
	padding: 10px;
	border: 1px dashed #c9d3e1;
	background: #f7f9fc;
	color: #5b6980;
}

.product-add-form .color-photo-block {
	border: 1px solid #dde3ec;
	background: #fcfdff;
	padding: 10px;
	margin-bottom: 10px;
}

.product-add-form .color-photo-title {
	font-weight: 700;
	margin-bottom: 6px;
}

.product-add-form .product-form-columns {
	display: block;
}

.product-add-form .product-richtext-grid {
	display: block;
}

@media (min-width: 1200px) {
	.product-add-form .product-form-columns {
		display: flex;
		gap: 24px;
		align-items: flex-start;
	}

	.product-add-form .product-form-col-main {
		flex: 1 1 0;
		min-width: 0;
	}

	.product-add-form .product-form-col-side {
		flex: 1 1 0;
		min-width: 0;
	}

	.product-add-form .product-form-col .form-group {
		margin-left: 0;
		margin-right: 0;
	}

	.product-add-form .product-form-col .col-sm-2,
	.product-add-form .product-form-col .col-sm-3,
	.product-add-form .product-form-col .col-sm-4,
	.product-add-form .product-form-col .col-sm-6,
	.product-add-form .product-form-col .col-sm-8 {
		float: none;
		width: 100%;
		padding-left: 0;
		padding-right: 0;
	}

	.product-add-form .product-form-col .control-label {
		text-align: left;
		padding-top: 0;
		margin-bottom: 6px;
	}

	.product-add-form .product-form-col .status-select {
		max-width: 100%;
	}

	.product-add-form .product-form-actions {
		border-top: 0;
		padding-top: 0;
		margin-top: 8px;
	}

	.product-add-form .product-richtext-grid {
		display: grid;
		grid-template-columns: 1fr;
		gap: 14px 0;
	}

	.product-add-form .status-inline-grid {
		grid-template-columns: 1fr;
	}

	.product-add-form .product-richtext-grid .form-group {
		margin-bottom: 0;
	}
}

@media (min-width: 768px) {
	.product-add-form .box-body {
		max-width: 1280px;
		margin: 0 auto;
	}

	.product-add-form .col-sm-4 {
		width: 78%;
	}

	.product-add-form .col-sm-8 {
		width: 78%;
	}
}
</style>


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

			<form class="form-horizontal product-add-form" action="" method="post" enctype="multipart/form-data">

				<div class="box box-info">
					<div class="box-body">
						<div class="product-form-columns">
							<div class="product-form-col product-form-col-main">
								<div class="form-section-card">
									<h3 class="section-title">Thông tin sản phẩm</h3>
									<div class="form-group">
									<label for="" class="col-sm-3 control-label">Danh mục cấp 1 <span>*</span></label>
									<div class="col-sm-4">
										<select name="tcat_id" class="form-control select2 top-cat">
											<option value="">Chọn danh mục cấp 1</option>
											<?php
											$statement = $pdo->prepare("SELECT * FROM tbl_top_category ORDER BY tcat_name ASC");
											$statement->execute();
											$result = $statement->fetchAll(PDO::FETCH_ASSOC);
											foreach ($result as $row) {
												?>
												<option value="<?php echo $row['tcat_id']; ?>"><?php echo $row['tcat_name']; ?></option>
												<?php
											}
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label for="" class="col-sm-3 control-label">Danh mục cấp 2 <span>*</span></label>
									<div class="col-sm-4">
										<select name="mcat_id" class="form-control select2 mid-cat">
											<option value="">Chọn danh mục cấp 2</option>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label for="" class="col-sm-3 control-label">Danh mục cấp 3 <span>*</span></label>
									<div class="col-sm-4">
										<select name="ecat_id" class="form-control select2 end-cat">
											<option value="">Chọn danh mục cấp 3</option>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label for="" class="col-sm-3 control-label">Tên sản phẩm <span>*</span></label>
									<div class="col-sm-4">
										<input type="text" name="p_name" class="form-control" value="<?php echo isset($_POST['p_name']) ? htmlspecialchars((string)$_POST['p_name'], ENT_QUOTES, 'UTF-8') : ''; ?>">
									</div>
								</div>
								<div class="form-group">
									<label for="" class="col-sm-3 control-label">Mã sản phẩm</label>
									<div class="col-sm-4">
										<input type="text" name="p_code" class="form-control" value="<?php echo isset($_POST['p_code']) ? htmlspecialchars((string)$_POST['p_code'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="Để trống để tự sinh mã">
									</div>
								</div>
								<div class="form-group">
									<label for="" class="col-sm-3 control-label">Đơn vị tính</label>
									<div class="col-sm-4">
										<input type="text" name="p_unit" class="form-control" value="<?php echo isset($_POST['p_unit']) ? htmlspecialchars((string)$_POST['p_unit'], ENT_QUOTES, 'UTF-8') : 'sp'; ?>" placeholder="Ví dụ: chiếc, hộp, kg">
									</div>
								</div>
								</div>

								<div class="form-section-card">
									<h3 class="section-title">Ảnh đại diện</h3>
									<div class="form-group">
										<label for="" class="col-sm-3 control-label"></label>
										<div class="col-sm-4" style="padding-top:4px;">
											<input type="file" name="p_featured_photo" accept=".png">
										</div>
									</div>
								</div>

								<div class="form-section-card">
									<h3 class="section-title">Giá</h3>
								<div class="form-group">
									<label for="" class="col-sm-3 control-label">Giá vốn <span>*</span></label>
									<div class="col-sm-4">
										<input type="text" name="p_cost_price" class="form-control currency-input" inputmode="numeric" value="<?php echo (isset($_POST['p_cost_price']) && $_POST['p_cost_price'] !== '' ? number_format((float)$_POST['p_cost_price'],0,'.',',') : ''); ?>" oninput="this.value=this.value.replace(/[^0-9]/g,'').replace(/\B(?=(\d{3})+(?!\d))/g,',');">
									</div>
								</div>
								<div class="form-group">
									<label for="" class="col-sm-3 control-label">% lợi nhuận <span>*</span></label>
									<div class="col-sm-4">
										<input type="text" name="p_profit_percent" class="form-control" value="<?php echo isset($_POST['p_profit_percent']) ? htmlspecialchars((string)$_POST['p_profit_percent'], ENT_QUOTES, 'UTF-8') : '30'; ?>" inputmode="decimal">
									</div>
								</div>
								<div class="form-group">
									<label for="" class="col-sm-3 control-label">Giá bán <span>*</span></label>
									<div class="col-sm-4">
										<input type="text" name="p_current_price" class="form-control currency-input" inputmode="numeric" value="<?php echo (isset($_POST['p_current_price']) && $_POST['p_current_price'] !== '' ? number_format((float)$_POST['p_current_price'],0,'.',',') : ''); ?>" readonly>
										<p class="help-block" style="margin-bottom:0;">Tự động tính theo công thức: Giá vốn x (1 + % lợi nhuận/100).</p>
									</div>
								</div>
								<div class="form-group">
									<label for="" class="col-sm-3 control-label">Giá cũ</label>
									<div class="col-sm-4">
										<input type="text" name="p_old_price" class="form-control currency-input" inputmode="numeric" value="<?php echo (isset($_POST['p_old_price']) && $_POST['p_old_price'] !== '' ? number_format((float)$_POST['p_old_price'],0,'.',',') : ''); ?>" oninput="this.value=this.value.replace(/[^0-9]/g,'').replace(/\B(?=(\d{3})+(?!\d))/g,',');">
									</div>
								</div>
								<div class="form-group">
									<label for="" class="col-sm-3 control-label">Ngưỡng tồn kho thấp</label>
									<div class="col-sm-4">
										<input type="number" min="0" name="p_low_stock_threshold" class="form-control" value="<?php echo isset($_POST['p_low_stock_threshold']) ? (int)$_POST['p_low_stock_threshold'] : 5; ?>">
									</div>
								</div>
								<div class="form-group">
									<label for="" class="col-sm-3 control-label"></label>
									<div class="col-sm-8" style="padding-top:8px;">
										<input type="hidden" name="p_qty" value="0">
									</div>
								</div>
								</div>

								<div class="form-section-card">
									<h3 class="section-title">Ảnh theo màu</h3>
									<div class="variant-table-wrap" id="colorPhotoContainer"></div>
								</div>

								<div class="form-section-card">
									<h3 class="section-title">Biến thể</h3>
								<div class="form-group">
									<label for="" class="col-sm-3 control-label"></label>
									<div class="col-sm-8">
										<div class="variant-table-wrap">
											<table class="table table-bordered variant-table" id="variantTable">
												<thead>
													<tr>
														<th>Size</th>
														<th>Màu</th>
														<th>Số lượng</th>
														<th>Thao tác</th>
													</tr>
												</thead>
												<tbody id="variantTableBody"></tbody>
											</table>
										</div>
										<div style="text-align:right;margin-top:8px;">
											<button type="button" class="btn btn-primary btn-sm" id="variantAddRowBtn">Thêm</button>
										</div>
									</div>
								</div>
								</div>

								<div class="form-section-card">
									<h3 class="section-title">Hiển thị</h3>
									<div class="form-group">
										<label for="" class="col-sm-3 control-label"></label>
										<div class="col-sm-8">
											<div class="status-inline-grid">
												<div class="status-inline-item">
													<label>Nổi bật</label>
													<select name="p_is_featured" class="form-control status-select" style="width:100%;">
														<option value="0">Không</option>
														<option value="1">Có</option>
													</select>
												</div>
												<div class="status-inline-item">
													<label>Trạng thái</label>
													<select name="p_is_active" class="form-control status-select" style="width:100%;">
														<option value="0">Không</option>
														<option value="1">Có</option>
													</select>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="product-form-col product-form-col-side">
								<div class="form-section-card">
									<h3 class="section-title">Mô tả</h3>
									<div class="product-richtext-grid">
										<div class="form-group">
											<label for="" class="col-sm-3 control-label">Mô tả chi tiết</label>
											<div class="col-sm-8">
												<textarea name="p_description" class="form-control" cols="30" rows="10" id="editor1"></textarea>
											</div>
										</div>
										<div class="form-group">
											<label for="" class="col-sm-3 control-label">Mô tả ngắn</label>
											<div class="col-sm-8">
												<textarea name="p_short_description" class="form-control" cols="30" rows="10" id="editor2"></textarea>
											</div>
										</div>
										<div class="form-group">
											<label for="" class="col-sm-3 control-label">Đặc điểm nổi bật</label>
											<div class="col-sm-8">
												<textarea name="p_feature" class="form-control" cols="30" rows="10" id="editor3"></textarea>
											</div>
										</div>
										<div class="form-group">
											<label for="" class="col-sm-3 control-label">Hướng dẫn chọn size</label>
											<div class="col-sm-8">
												<textarea name="p_condition" class="form-control" cols="30" rows="10" id="editor4"></textarea>
											</div>
										</div>
										<div class="form-group">
											<label for="" class="col-sm-3 control-label">Chính sách đổi trả</label>
											<div class="col-sm-8">
												<textarea name="p_return_policy" class="form-control" cols="30" rows="10" id="editor5"></textarea>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div style="text-align:right; margin-top: 6px;">
							<button type="submit" class="btn btn-success" name="form1">Thêm sản phẩm</button>
							</div>
					</div>
				</div>

			</form>


		</div>
	</div>

</section>

<script>
(function() {
	var sizeOptions = <?php
		$size_js_rows = array();
		$statement = $pdo->prepare("SELECT size_id, size_name FROM tbl_size ORDER BY size_id ASC");
		$statement->execute();
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		foreach($result as $row) {
			$size_js_rows[] = array('id' => (int)$row['size_id'], 'name' => $row['size_name']);
		}
		echo json_encode($size_js_rows, JSON_UNESCAPED_UNICODE);
	?>;

	var colorOptions = <?php
		$color_js_rows = array();
		$statement = $pdo->prepare("SELECT color_id, color_name FROM tbl_color ORDER BY color_id ASC");
		$statement->execute();
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		foreach($result as $row) {
			$color_js_rows[] = array('id' => (int)$row['color_id'], 'name' => $row['color_name']);
		}
		echo json_encode($color_js_rows, JSON_UNESCAPED_UNICODE);
	?>;

	var variantSeedRows = <?php echo json_encode($variant_form_rows, JSON_UNESCAPED_UNICODE); ?>;
	var selectedPhotoColorSeed = <?php echo json_encode($photo_color_selected, JSON_UNESCAPED_UNICODE); ?>;

	function escapeHtml(text) {
		var div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

	function renderOptionList(options, selectedId) {
		var html = '<option value="">Chọn</option>';
		for(var i=0;i<options.length;i++) {
			var selected = (selectedId === options[i].id) ? ' selected' : '';
			html += '<option value="' + options[i].id + '"' + selected + '>' + escapeHtml(options[i].name) + '</option>';
		}
		return html;
	}

	function getAllowedVariantColors() {
		var ids = [];
		var selects = document.querySelectorAll('select[name="photo_color_selected[]"]');
		for(var i=0;i<selects.length;i++) {
			var cid = parseInt(selects[i].value || '0', 10);
			if(cid > 0) {
				ids.push(cid);
			}
		}
		return ids;
	}

	function renderPhotoColorOptionList(currentValue) {
		var selectedInRows = getAllowedVariantColors();
		var usedMap = {};
		for(var i=0;i<selectedInRows.length;i++) {
			usedMap[selectedInRows[i]] = true;
		}

		var html = '<option value="">Chọn</option>';
		for(var j=0;j<colorOptions.length;j++) {
			var cid = colorOptions[j].id;
			if(cid !== currentValue && usedMap[cid]) {
				continue;
			}
			var selected = (currentValue === cid) ? ' selected' : '';
			html += '<option value="' + cid + '"' + selected + '>' + escapeHtml(colorOptions[j].name) + '</option>';
		}
		return html;
	}

	function renderVariantColorOptionList(selectedId) {
		var allowed = getAllowedVariantColors();
		var allowMap = {};
		for(var i=0;i<allowed.length;i++) {
			allowMap[allowed[i]] = true;
		}

		var html = '<option value="">Chọn</option>';
		for(var j=0;j<colorOptions.length;j++) {
			if(!allowMap[colorOptions[j].id]) {
				continue;
			}
			var selected = (selectedId === colorOptions[j].id) ? ' selected' : '';
			html += '<option value="' + colorOptions[j].id + '"' + selected + '>' + escapeHtml(colorOptions[j].name) + '</option>';
		}
		return html;
	}

	function addVariantRow(seed) {
		var tbody = document.getElementById('variantTableBody');
		if(!tbody) {
			return;
		}

		var sizeId = seed && seed.size_id ? parseInt(seed.size_id, 10) : 0;
		var colorId = seed && seed.color_id ? parseInt(seed.color_id, 10) : 0;
		var qty = seed && (seed.qty || seed.qty === 0) ? String(seed.qty) : '';

		var row = document.createElement('tr');
		row.innerHTML = '' +
			'<td><select name="variant_size_id[]" class="form-control">' + renderOptionList(sizeOptions, sizeId) + '</select></td>' +
			'<td><select name="variant_color_id[]" class="form-control variant-color-select">' + renderVariantColorOptionList(colorId) + '</select></td>' +
			'<td><input type="number" name="variant_qty[]" class="form-control" inputmode="numeric" min="0" value="' + escapeHtml(qty) + '"></td>' +
			'<td><button type="button" class="btn btn-danger btn-xs variant-remove-btn">Xóa</button></td>';
		tbody.appendChild(row);
	}

	function updatePhotoRowInput(row) {
		if(!row) {
			return;
		}
		var select = row.querySelector('select[name="photo_color_selected[]"]');
		var cell = row.querySelector('.photo-file-cell');
		if(!select || !cell) {
			return;
		}

		var colorId = parseInt(select.value || '0', 10);
		var prevColorId = parseInt(row.getAttribute('data-photo-color-id') || '0', 10);
		if(colorId <= 0) {
			cell.innerHTML = '';
			row.setAttribute('data-photo-color-id', '0');
			return;
		}

		// Do not recreate the same input when color is unchanged; otherwise browser clears selected files.
		if(prevColorId === colorId && cell.querySelector('input.variant-color-photo-input')) {
			return;
		}

		cell.innerHTML = '' +
			'<input type="file" name="color_photo[' + colorId + '][]" class="variant-color-photo-input" accept=".png" multiple>';
		row.setAttribute('data-photo-color-id', String(colorId));
	}

	function refreshPhotoColorSelects() {
		var selects = document.querySelectorAll('select[name="photo_color_selected[]"]');
		for(var i=0;i<selects.length;i++) {
			var oldValue = parseInt(selects[i].value || '0', 10);
			selects[i].innerHTML = renderPhotoColorOptionList(oldValue);
			selects[i].value = oldValue > 0 ? String(oldValue) : '';
		}
	}

	function addColorPhotoRow(seedColorId) {
		var tbody = document.getElementById('colorPhotoTableBody');
		if(!tbody) {
			return;
		}

		var colorId = seedColorId ? parseInt(seedColorId, 10) : 0;
		var tr = document.createElement('tr');
		tr.setAttribute('data-photo-color-id', colorId > 0 ? String(colorId) : '0');
		tr.innerHTML = '' +
			'<td style="min-width:220px;"><select name="photo_color_selected[]" class="form-control photo-color-select" style="min-width:190px;">' + renderPhotoColorOptionList(colorId) + '</select></td>' +
			'<td class="photo-file-cell"></td>' +
			'<td style="width:90px;text-align:center;"><button type="button" class="btn btn-danger btn-xs color-photo-remove-btn">Xóa</button></td>';
		tbody.appendChild(tr);
		updatePhotoRowInput(tr);
	}

	function renderColorPhotoBlocks() {
		var container = document.getElementById('colorPhotoContainer');
		if(!container) {
			return;
		}

		var html = '' +
			'<table class="table table-bordered variant-table" id="colorPhotoTable">' +
				'<thead><tr><th>Màu</th><th>Ảnh theo màu</th><th>Thao tác</th></tr></thead>' +
				'<tbody id="colorPhotoTableBody"></tbody>' +
			'</table>' +
			'<div style="text-align:right;margin-top:8px;">' +
				'<button type="button" class="btn btn-primary btn-sm" id="colorPhotoAddRowBtn">Thêm</button>' +
			'</div>';
		container.innerHTML = html;

		if(selectedPhotoColorSeed.length > 0) {
			for(var i=0;i<selectedPhotoColorSeed.length;i++) {
				addColorPhotoRow(selectedPhotoColorSeed[i]);
			}
		} else {
			addColorPhotoRow(null);
		}

		refreshPhotoColorSelects();
	}

	function refreshVariantColorSelects() {
		var selects = document.querySelectorAll('select[name="variant_color_id[]"]');
		for(var i=0;i<selects.length;i++) {
			var oldValue = parseInt(selects[i].value || '0', 10);
			selects[i].innerHTML = renderVariantColorOptionList(oldValue);
			var hasValue = false;
			for(var j=0;j<selects[i].options.length;j++) {
				if(parseInt(selects[i].options[j].value || '0', 10) === oldValue) {
					hasValue = true;
					break;
				}
			}
			if(!hasValue) {
				selects[i].value = '';
			}
		}
	}

	function normalizeCurrencyInput(target) {
		if(!target || !target.value) {
			return;
		}
		target.value = target.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',');
	}

	function parseCurrencyToInt(value) {
		var digits = String(value || '').replace(/[^0-9]/g, '');
		return digits ? parseInt(digits, 10) : 0;
	}

	function parsePercentValue(value) {
		var normalized = String(value || '').trim().replace(',', '.').replace(/[^0-9.]/g, '');
		var parts = normalized.split('.');
		if(parts.length > 2) {
			normalized = parts.shift() + '.' + parts.join('');
		}
		var num = parseFloat(normalized);
		if(isNaN(num) || num < 0) {
			return 0;
		}
		return num;
	}

	function formatCurrency(num) {
		var val = Math.max(0, parseInt(num || 0, 10));
		return val > 0 ? String(val).replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '';
	}

	function updateAutoSalePrice() {
		var costInput = document.querySelector('input[name="p_cost_price"]');
		var profitInput = document.querySelector('input[name="p_profit_percent"]');
		var saleInput = document.querySelector('input[name="p_current_price"]');
		if(!costInput || !profitInput || !saleInput) {
			return;
		}

		var cost = parseCurrencyToInt(costInput.value);
		var profit = parsePercentValue(profitInput.value);
		var sale = 0;
		if(cost > 0) {
			sale = Math.round(cost * (1 + (profit / 100)));
			if(sale < 1) {
				sale = 1;
			}
		}
		saleInput.value = formatCurrency(sale);
	}

	document.addEventListener('input', function(e) {
		if(!e.target) {
			return;
		}
		if(e.target.name === 'p_cost_price') {
			normalizeCurrencyInput(e.target);
			updateAutoSalePrice();
		}
		if(e.target.name === 'p_profit_percent') {
			e.target.value = e.target.value.replace(/[^0-9.,]/g, '');
			updateAutoSalePrice();
		}
	});

	document.addEventListener('change', function(e) {
		if(e.target && e.target.classList.contains('photo-color-select')) {
			updatePhotoRowInput(e.target.closest('tr'));
			refreshPhotoColorSelects();
			refreshVariantColorSelects();
		}
		if(e.target && e.target.classList.contains('variant-color-photo-input')) {
			if(e.target.files && e.target.files.length > 0) {
				for(var i=0;i<e.target.files.length;i++) {
					if(!/\.png$/i.test(e.target.files[i].name || '')) {
						alert('Chỉ chấp nhận ảnh PNG.');
						e.target.value = '';
						break;
					}
					if(e.target.files[i].size > 10485760) {
						alert('Mỗi ảnh không được vượt quá 10MB.');
						e.target.value = '';
						break;
					}
				}
			}
		}
	});

	document.addEventListener('click', function(e) {
		if(e.target && e.target.id === 'colorPhotoAddRowBtn') {
			addColorPhotoRow(null);
			refreshPhotoColorSelects();
			refreshVariantColorSelects();
		}
		if(e.target && e.target.classList.contains('color-photo-remove-btn')) {
			var photoRow = e.target.closest('tr');
			if(photoRow) {
				photoRow.remove();
				var tbody = document.getElementById('colorPhotoTableBody');
				if(tbody && tbody.children.length === 0) {
					addColorPhotoRow(null);
				}
				refreshPhotoColorSelects();
				refreshVariantColorSelects();
			}
		}
		if(e.target && e.target.id === 'variantAddRowBtn') {
			addVariantRow(null);
		}
		if(e.target && e.target.classList.contains('variant-remove-btn')) {
			var tr = e.target.closest('tr');
			if(tr) {
				tr.remove();
			}
		}
	});

	if(variantSeedRows.length > 0) {
		for(var i=0;i<variantSeedRows.length;i++) {
			addVariantRow(variantSeedRows[i]);
		}
	} else {
		addVariantRow(null);
	}

	renderColorPhotoBlocks();
	refreshVariantColorSelects();
	updateAutoSalePrice();
})();
</script>

<?php require_once('footer.php'); ?>
