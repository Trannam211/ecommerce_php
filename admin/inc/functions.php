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
	$clean_price = preg_replace('/[^0-9]/', '', (string)$price);
	if($clean_price === '') {
		return '0đ';
	}

	return number_format((float)$clean_price, 0, '.', ',').'đ';
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
		'Stripe' => 'Thẻ (Stripe)',
		'PayPal' => 'PayPal'
	);

	return isset($map[$value]) ? $map[$value] : $value;
}
