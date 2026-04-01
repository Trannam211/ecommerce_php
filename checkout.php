<?php require_once('header.php'); ?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
    $banner_checkout = $row['banner_checkout'];
    $cod_on_off = isset($row['cod_on_off']) ? (int)$row['cod_on_off'] : 1;
}
?>

<?php
if(!isset($_SESSION['cart_p_id'])) {
    safe_redirect('cart.php');
}

$default_address = null;
$selected_address = null;
$address_list = array();
$address_book_enabled = true;
if(isset($_SESSION['customer'])) {
    $cust_id = (int)$_SESSION['customer']['cust_id'];

    try {
        $statement = $pdo->prepare("SELECT * FROM tbl_customer_address WHERE cust_id=? ORDER BY is_default DESC, address_id ASC");
        $statement->execute(array($cust_id));
        $address_list = $statement->fetchAll(PDO::FETCH_ASSOC);

        if(count($address_list) > 0) {
            $default_address = $address_list[0];
        }

        if(isset($_POST['select_address_submit'])) {
            $candidate_id = isset($_POST['selected_address_id']) ? (int)$_POST['selected_address_id'] : 0;
            if($candidate_id > 0) {
                $statement = $pdo->prepare("SELECT * FROM tbl_customer_address WHERE address_id=? AND cust_id=? LIMIT 1");
                $statement->execute(array($candidate_id, $cust_id));
                $candidate = $statement->fetch(PDO::FETCH_ASSOC);
                if($candidate) {
                    $_SESSION['selected_checkout_address_id'] = (int)$candidate['address_id'];
                }
            }
        }

        if(isset($_SESSION['selected_checkout_address_id'])) {
            $statement = $pdo->prepare("SELECT * FROM tbl_customer_address WHERE address_id=? AND cust_id=? LIMIT 1");
            $statement->execute(array((int)$_SESSION['selected_checkout_address_id'], $cust_id));
            $selected_address = $statement->fetch(PDO::FETCH_ASSOC);
        }

        if(!$selected_address) {
            $selected_address = $default_address;
            if($selected_address) {
                $_SESSION['selected_checkout_address_id'] = (int)$selected_address['address_id'];
            } else {
                unset($_SESSION['selected_checkout_address_id']);
            }
        }
    } catch(PDOException $e) {
        // Keep checkout working for old databases that do not have tbl_customer_address.
        $address_book_enabled = false;
        $selected_address = array(
            'receiver_name' => isset($_SESSION['customer']['cust_name']) ? $_SESSION['customer']['cust_name'] : '',
            'receiver_phone' => isset($_SESSION['customer']['cust_phone']) ? $_SESSION['customer']['cust_phone'] : '',
            'address_line' => isset($_SESSION['customer']['cust_address']) ? $_SESSION['customer']['cust_address'] : '',
            'city' => isset($_SESSION['customer']['cust_city']) ? $_SESSION['customer']['cust_city'] : '',
            'district' => isset($_SESSION['customer']['cust_state']) ? $_SESSION['customer']['cust_state'] : '',
            'ward' => isset($_SESSION['customer']['cust_zip']) ? $_SESSION['customer']['cust_zip'] : ''
        );
        unset($_SESSION['selected_checkout_address_id']);
    }
}

$checkout_access = 1;
if(!isset($_SESSION['customer'])) {
    $checkout_access = 0;
} else {
    $required_address_fields = array('receiver_name', 'receiver_phone', 'address_line', 'city');
    if($address_book_enabled) {
        $required_address_fields[] = 'district';
        $required_address_fields[] = 'ward';
    }

    foreach($required_address_fields as $field) {
        if(
            !$selected_address ||
            !isset($selected_address[$field]) ||
            trim((string)$selected_address[$field]) === ''
        ) {
            $checkout_access = 0;
            break;
        }
    }
}

$cart_selected_keys = array();
$cart_all_keys = isset($_SESSION['cart_p_id']) ? array_keys($_SESSION['cart_p_id']) : array();
if(isset($_SESSION['checkout_selected_item_keys']) && is_array($_SESSION['checkout_selected_item_keys'])) {
    foreach($_SESSION['checkout_selected_item_keys'] as $selected_key_raw) {
        $selected_key = (int)$selected_key_raw;
        if(in_array($selected_key, $cart_all_keys, true) && !in_array($selected_key, $cart_selected_keys, true)) {
            $cart_selected_keys[] = $selected_key;
        }
    }
}
if(count($cart_selected_keys) === 0) {
    $cart_selected_keys = $cart_all_keys;
    $_SESSION['checkout_selected_item_keys'] = $cart_selected_keys;
}

$cart_p_id_values = array();
$cart_size_name_values = array();
$cart_color_name_values = array();
$cart_qty_values = array();
$cart_price_values = array();
$cart_name_values = array();
$cart_photo_values = array();
foreach($cart_selected_keys as $selected_key) {
    if(!isset($_SESSION['cart_p_id'][$selected_key])) {
        continue;
    }
    $cart_p_id_values[] = $_SESSION['cart_p_id'][$selected_key];
    $cart_size_name_values[] = isset($_SESSION['cart_size_name'][$selected_key]) ? $_SESSION['cart_size_name'][$selected_key] : '';
    $cart_color_name_values[] = isset($_SESSION['cart_color_name'][$selected_key]) ? $_SESSION['cart_color_name'][$selected_key] : '';
    $cart_qty_values[] = isset($_SESSION['cart_p_qty'][$selected_key]) ? $_SESSION['cart_p_qty'][$selected_key] : 1;
    $cart_price_values[] = isset($_SESSION['cart_p_current_price'][$selected_key]) ? $_SESSION['cart_p_current_price'][$selected_key] : 0;
    $cart_name_values[] = isset($_SESSION['cart_p_name'][$selected_key]) ? $_SESSION['cart_p_name'][$selected_key] : '';
    $cart_photo_values[] = isset($_SESSION['cart_p_featured_photo'][$selected_key]) ? $_SESSION['cart_p_featured_photo'][$selected_key] : '';
}

$table_total_price = 0;
$cart_count = count($cart_p_id_values);
$total_items_qty = 0;

$product_stock_map = array();
if($cart_count > 0) {
    $stock_placeholders = implode(',', array_fill(0, $cart_count, '?'));
    $statement = $pdo->prepare("SELECT p_id, p_qty FROM tbl_product WHERE p_id IN ($stock_placeholders)");
    $statement->execute($cart_p_id_values);
    while($stock_row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $product_stock_map[(int)$stock_row['p_id']] = (int)$stock_row['p_qty'];
    }
}

for($ci=0;$ci<$cart_count;$ci++) {
    $qty_tmp = isset($cart_qty_values[$ci]) ? (int)$cart_qty_values[$ci] : 0;
    $price_tmp = isset($cart_price_values[$ci]) ? (float)$cart_price_values[$ci] : 0;
    $total_items_qty += $qty_tmp;
    $table_total_price += ($qty_tmp * $price_tmp);
}

$shipping_cost = 0;
$statement = $pdo->prepare("SELECT * FROM tbl_shipping_cost_all WHERE sca_id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $shipping_cost = (float)$row['amount'];
}
$final_total = $table_total_price + $shipping_cost;

$selected_full_address = '';
if($selected_address) {
    $address_parts = array();
    if(isset($selected_address['address_line']) && trim($selected_address['address_line']) !== '') {
        $address_parts[] = trim($selected_address['address_line']);
    }
    if(isset($selected_address['ward']) && trim($selected_address['ward']) !== '') {
        $address_parts[] = trim($selected_address['ward']);
    }
    if(isset($selected_address['district']) && trim($selected_address['district']) !== '') {
        $address_parts[] = trim($selected_address['district']);
    }
    if(isset($selected_address['city']) && trim($selected_address['city']) !== '') {
        $address_parts[] = trim($selected_address['city']);
    }
    $selected_full_address = implode(', ', $address_parts);
}
?>

<div class="page-banner" style="background-image: url(assets/uploads/<?php echo $banner_checkout; ?>)">
    <div class="overlay"></div>
    <div class="page-banner-inner">
        <h1>Thanh toán</h1>
    </div>
</div>

<style>
    .checkout-layout {
        display: grid;
        grid-template-columns: 7fr 3fr;
        gap: 20px;
        align-items: start;
    }

    .checkout-block,
    .checkout-summary-card {
        background: #fff;
        border: 1px solid #e7eaf0;
        border-radius: 0;
        padding: 18px;
        margin-bottom: 18px;
        box-shadow: 0 2px 8px rgba(30, 44, 76, 0.04);
    }

    .checkout-summary-card,
    .checkout-summary-card .form-control,
    .checkout-summary-card .checkout-place-btn,
    .checkout-summary-card .checkout-bank-box {
        border-radius: 0;
    }

    .checkout-block-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }

    .checkout-block-title {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        color: #1f2d3d;
    }

    .checkout-link-btn {
        color: #2b6fb0;
        font-weight: 700;
        text-decoration: none;
        border: 1px solid #c5d9ec;
        padding: 6px 12px;
        border-radius: 0;
        background: #f2f8ff;
    }

    .checkout-address-card {
        border: 1px solid #e4e9f2;
        border-radius: 0;
        padding: 14px;
        background: #fcfdff;
    }

    .checkout-address-top {
        font-size: 17px;
        color: #1d2b3a;
        margin-bottom: 8px;
    }

    .checkout-address-line {
        color: #4a5a6b;
        line-height: 1.6;
    }

    .checkout-address-picker {
        border: 1px solid #e4e9f2;
        border-radius: 0;
        padding: 12px;
        margin-bottom: 12px;
        background: #fff;
    }

    .checkout-address-picker label {
        display: block;
        margin-bottom: 8px;
        cursor: pointer;
        color: #243446;
        font-weight: 400;
    }

    .checkout-product-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .checkout-product-item {
        display: flex;
        align-items: center;
        gap: 14px;
        border: 1px solid #edf1f7;
        border-radius: 0;
        padding: 12px;
        background: #fff;
    }

    .checkout-product-thumb img {
        width: 72px;
        height: 72px;
        object-fit: cover;
        border: 1px solid #e9edf4;
        border-radius: 0;
    }

    .checkout-product-meta {
        flex: 1;
        min-width: 0;
    }

    .checkout-product-name {
        margin: 0 0 6px;
        color: #1f2d3d;
        font-size: 15px;
        font-weight: 600;
    }

    .checkout-product-variant {
        color: #637487;
        font-size: 13px;
    }

    .checkout-product-price {
        text-align: right;
        min-width: 160px;
    }

    .checkout-product-qty {
        display: inline-block;
        margin-bottom: 4px;
        padding: 2px 10px;
        border: 1px solid #e5eaf2;
        font-size: 12px;
        color: #3f5266;
        background: #f9fbff;
    }

    .checkout-product-price .line {
        color: #34495e;
        font-size: 14px;
    }

    .checkout-product-price .total {
        display: block;
        color: #ee4d2d;
        font-weight: 700;
        margin-top: 4px;
    }

    .checkout-summary-wrap {
        position: sticky;
        top: 16px;
    }

    .checkout-summary-title {
        margin: 0 0 12px;
        font-size: 20px;
        color: #1f2d3d;
        font-weight: 700;
    }

    .checkout-summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
        color: #44586d;
    }

    .checkout-summary-total {
        border-top: 1px dashed #dbe3ee;
        margin-top: 10px;
        padding-top: 12px;
        font-size: 17px;
        font-weight: 700;
        color: #ee4d2d;
    }

    .checkout-pay-label {
        margin: 14px 0 8px;
        font-weight: 700;
        color: #1f2d3d;
    }

    .checkout-place-btn {
        width: 100%;
        background: #337ab7;
        border-color: #2e6da4;
        color: #fff;
        font-weight: 700;
        min-height: 44px;
        border-radius: 0;
    }

    .checkout-place-btn:hover,
    .checkout-place-btn:focus {
        background: #286090;
        border-color: #204d74;
        color: #fff;
    }

    .checkout-bank-box {
        border: 1px solid #e8eef7;
        border-radius: 0;
        padding: 10px;
        background: #fbfdff;
        margin-bottom: 10px;
        max-height: 170px;
        overflow: auto;
    }

    @media (max-width: 991px) {
        .checkout-layout {
            grid-template-columns: 1fr;
        }

        .checkout-summary-wrap {
            position: static;
        }

        .checkout-product-item {
            flex-wrap: wrap;
        }

        .checkout-product-price {
            width: 100%;
            text-align: left;
            min-width: 0;
        }
    }
</style>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                
                <?php if(!isset($_SESSION['customer'])): ?>
                    <p style="margin:0;">
                        <a href="login.php" class="btn btn-md btn-danger">Vui lòng đăng nhập tài khoản khách hàng để thanh toán</a>
                        <a href="registration.php?redirect=checkout" class="btn btn-md btn-primary" style="margin-left:8px;">Chưa có tài khoản? Đăng ký ngay</a>
                    </p>
                <?php else: ?>

                <div class="checkout-layout">
                    <div class="checkout-left">
                        <div class="checkout-block">
                            <div class="checkout-block-header">
                                <h3 class="checkout-block-title">Địa chỉ nhận hàng</h3>
                                <?php if($address_book_enabled): ?>
                                    <a class="checkout-link-btn" href="customer-billing-shipping-update.php">Thay đổi / Cập nhật</a>
                                <?php else: ?>
                                    <a class="checkout-link-btn" href="customer-profile-update.php">Cập nhật</a>
                                <?php endif; ?>
                            </div>

                            <?php if(!$address_book_enabled): ?>
                                <div style="margin-bottom:12px;padding:10px 12px;background:#fff8e5;border:1px solid #f3d48a;color:#8a6d3b;border-radius:0;">
                                    Hệ thống đang dùng địa chỉ từ hồ sơ khách hàng vì cơ sở dữ liệu chưa có sổ địa chỉ mới.
                                </div>
                            <?php endif; ?>

                            <?php if($address_book_enabled && count($address_list) > 1): ?>
                                <form action="" method="post" class="checkout-address-picker">
                                    <?php foreach($address_list as $addr): ?>
                                        <label>
                                            <input
                                                type="radio"
                                                name="selected_address_id"
                                                value="<?php echo (int)$addr['address_id']; ?>"
                                                <?php echo ($selected_address && (int)$selected_address['address_id'] === (int)$addr['address_id']) ? 'checked' : ''; ?>
                                            >
                                            <strong><?php echo htmlspecialchars($addr['receiver_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                            - <?php echo htmlspecialchars($addr['receiver_phone'], ENT_QUOTES, 'UTF-8'); ?>
                                            (<?php echo htmlspecialchars($addr['ward'], ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($addr['district'], ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($addr['city'], ENT_QUOTES, 'UTF-8'); ?>)
                                            <?php if((int)$addr['is_default'] === 1): ?>
                                                <span style="display:inline-block;margin-left:8px;border:1px solid #ee4d2d;color:#ee4d2d;padding:1px 6px;font-size:11px;border-radius:0;">Mặc định</span>
                                            <?php endif; ?>
                                        </label>
                                    <?php endforeach; ?>
                                    <button type="submit" name="select_address_submit" class="btn btn-default" style="border-radius:0;">Sử dụng địa chỉ đã chọn</button>
                                </form>
                            <?php endif; ?>

                            <div class="checkout-address-card">
                                <div class="checkout-address-top">
                                    <strong><?php echo isset($selected_address['receiver_name']) ? htmlspecialchars($selected_address['receiver_name'], ENT_QUOTES, 'UTF-8') : ''; ?></strong>
                                    -
                                    <strong><?php echo isset($selected_address['receiver_phone']) ? htmlspecialchars($selected_address['receiver_phone'], ENT_QUOTES, 'UTF-8') : ''; ?></strong>
                                </div>
                                <div class="checkout-address-line">
                                    <?php echo htmlspecialchars($selected_full_address, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="checkout-block">
                            <div class="checkout-block-header">
                                <h3 class="checkout-block-title">Sản phẩm đặt mua</h3>
                                <a class="checkout-link-btn" href="cart.php">Quay lại giỏ hàng</a>
                            </div>

                            <div class="checkout-product-list">
                                <?php for($ci=0;$ci<$cart_count;$ci++): ?>
                                    <?php
                                    $p_id = isset($cart_p_id_values[$ci]) ? (int)$cart_p_id_values[$ci] : 0;
                                    $p_name = isset($cart_name_values[$ci]) ? $cart_name_values[$ci] : '';
                                    $p_size = isset($cart_size_name_values[$ci]) ? $cart_size_name_values[$ci] : '';
                                    $p_color = isset($cart_color_name_values[$ci]) ? $cart_color_name_values[$ci] : '';
                                    $p_qty = isset($cart_qty_values[$ci]) ? (int)$cart_qty_values[$ci] : 0;
                                    $p_price = isset($cart_price_values[$ci]) ? (float)$cart_price_values[$ci] : 0;
                                    $p_photo = isset($cart_photo_values[$ci]) ? $cart_photo_values[$ci] : '';
                                    $p_stock = isset($product_stock_map[$p_id]) ? $product_stock_map[$p_id] : null;
                                    $p_line_total = $p_qty * $p_price;
                                    ?>
                                    <div class="checkout-product-item">
                                        <div class="checkout-product-thumb">
                                            <img src="assets/uploads/<?php echo htmlspecialchars($p_photo, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($p_name, ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                        <div class="checkout-product-meta">
                                            <p class="checkout-product-name"><?php echo htmlspecialchars($p_name, ENT_QUOTES, 'UTF-8'); ?></p>
                                            <div class="checkout-product-variant">
                                                Kích thước: <strong><?php echo htmlspecialchars($p_size, ENT_QUOTES, 'UTF-8'); ?></strong>
                                                | Màu sắc: <strong><?php echo htmlspecialchars($p_color, ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                                Số lượng mua: <strong><?php echo $p_qty; ?></strong>
                                                <?php if($p_stock !== null): ?>
                                                    | Tồn kho: <strong><?php echo $p_stock; ?></strong>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="checkout-product-price">
                                            <span class="checkout-product-qty">Số lượng: <?php echo $p_qty; ?></span>
                                            <span class="line"><?php echo format_price_vnd($p_price); ?> x <?php echo $p_qty; ?></span>
                                            <span class="total"><?php echo format_price_vnd($p_line_total); ?></span>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <aside class="checkout-right">
                        <div class="checkout-summary-wrap">
                            <div class="checkout-summary-card">
                                <h3 class="checkout-summary-title">Tổng thanh toán</h3>

                                <div class="checkout-summary-row">
                                    <span>Tổng số lượng</span>
                                    <strong><?php echo $total_items_qty; ?> sản phẩm</strong>
                                </div>
                                <div class="checkout-summary-row">
                                    <span>Tạm tính</span>
                                    <strong><?php echo format_price_vnd($table_total_price); ?></strong>
                                </div>
                                <div class="checkout-summary-row">
                                    <span>Phí vận chuyển</span>
                                    <strong><?php echo format_price_vnd($shipping_cost); ?></strong>
                                </div>
                                <div class="checkout-summary-row checkout-summary-total">
                                    <span>Tổng tiền</span>
                                    <span><?php echo format_price_vnd($final_total); ?></span>
                                </div>

                                <div class="checkout-pay-label">Phương thức thanh toán *</div>
                                <div class="form-group" style="margin-bottom:12px;">
                                    <select name="payment_method" class="form-control select2" id="advFieldsStatus">
                                        <option value="">Chọn phương thức thanh toán</option>
                                        <?php if((int)$cod_on_off === 1): ?>
                                            <option value="Cash On Delivery">Thanh toán khi nhận hàng</option>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <?php if($checkout_access == 0): ?>
                                    <div style="margin-top:8px;color:#d9534f;font-size:15px;line-height:1.6;">
                                        Bạn cần cập nhật đầy đủ thông tin nhận hàng trước khi đặt hàng.<br>
                                        <?php if($address_book_enabled): ?>
                                            Vui lòng cập nhật tại <a href="customer-billing-shipping-update.php" style="color:#d9534f;text-decoration:underline;">đường dẫn này</a>.
                                        <?php else: ?>
                                            Vui lòng cập nhật tại <a href="customer-profile-update.php" style="color:#d9534f;text-decoration:underline;">trang hồ sơ khách hàng</a>.
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <?php if((int)$cod_on_off === 1): ?>
                                        <form action="<?php echo BASE_URL; ?>payment/cod/init.php" method="post" id="cod_form" style="display:none;">
                                            <input type="hidden" name="amount" value="<?php echo $final_total; ?>">
                                            <input type="submit" class="btn checkout-place-btn" value="Đặt hàng" name="form_cod">
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </aside>
                </div>
                

                <?php endif; ?>

            </div>
        </div>
    </div>
</div>


<?php require_once('footer.php'); ?>