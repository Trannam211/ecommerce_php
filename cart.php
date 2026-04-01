<?php
ob_start();
require_once('header.php');
?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
    $banner_cart = $row['banner_cart'];
}
?>

<?php
$error_message = '';
if(isset($_POST['form1']) || isset($_POST['go_home']) || isset($_POST['go_checkout']) || isset($_POST['auto_update'])) {

    $is_auto_update = isset($_POST['auto_update']) && $_POST['auto_update'] === '1';

    $posted_product_ids = isset($_POST['product_id']) && is_array($_POST['product_id']) ? array_values($_POST['product_id']) : array();
    $posted_quantities = isset($_POST['quantity']) && is_array($_POST['quantity']) ? array_values($_POST['quantity']) : array();
    $posted_names = isset($_POST['product_name']) && is_array($_POST['product_name']) ? array_values($_POST['product_name']) : array();
    $posted_item_keys = isset($_POST['item_key']) && is_array($_POST['item_key']) ? array_values($_POST['item_key']) : array();
    $posted_selected_keys = isset($_POST['selected_item_keys']) && is_array($_POST['selected_item_keys']) ? $_POST['selected_item_keys'] : array();

    $cart_keys = isset($_SESSION['cart_p_id']) ? array_values(array_keys($_SESSION['cart_p_id'])) : array();
    $item_count = min(count($posted_product_ids), count($posted_quantities), count($cart_keys));

    $product_ids_for_stock = array();
    for($i=0;$i<$item_count;$i++) {
        $product_ids_for_stock[] = (int)$posted_product_ids[$i];
    }

    $stock_map = array();
    $variant_stock_map = array();
    $variant_table_exists = false;
    if(count($product_ids_for_stock) > 0) {
        $placeholders = implode(',', array_fill(0, count($product_ids_for_stock), '?'));
        $statement = $pdo->prepare("SELECT p_id, p_qty FROM tbl_product WHERE p_id IN ($placeholders)");
        $statement->execute($product_ids_for_stock);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $stock_map[(int)$row['p_id']] = (int)$row['p_qty'];
        }

        $statement = $pdo->prepare("SHOW TABLES LIKE 'tbl_product_variant'");
        $statement->execute();
        $variant_table_exists = $statement->rowCount() > 0;
        if($variant_table_exists) {
            $statement = $pdo->prepare("SELECT p_id, size_id, color_id, pv_qty FROM tbl_product_variant WHERE p_id IN ($placeholders)");
            $statement->execute($product_ids_for_stock);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach($result as $row) {
                $vkey = ((int)$row['p_id']).'_'.((int)$row['size_id']).'_'.((int)$row['color_id']);
                $variant_stock_map[$vkey] = (int)$row['pv_qty'];
            }
        }
    }

    $allow_update = 1;
    for($i=0;$i<$item_count;$i++) {
        $session_key = isset($posted_item_keys[$i]) ? (int)$posted_item_keys[$i] : $cart_keys[$i];
        if(!in_array($session_key, $cart_keys, true)) {
            $session_key = $cart_keys[$i];
        }
        $product_id = (int)$posted_product_ids[$i];
        $requested_qty = (int)$posted_quantities[$i];
        $product_name = isset($posted_names[$i]) ? $posted_names[$i] : 'Sản phẩm';

        if($requested_qty < 1) {
            $requested_qty = 1;
        }

        if(!isset($stock_map[$product_id])) {
            $allow_update = 0;
            $error_message .= 'Sản phẩm "'.$product_name.'" không còn tồn tại.\n';
            continue;
        }

        $size_id = isset($_SESSION['cart_size_id'][$session_key]) ? (int)$_SESSION['cart_size_id'][$session_key] : 0;
        $color_id = isset($_SESSION['cart_color_id'][$session_key]) ? (int)$_SESSION['cart_color_id'][$session_key] : 0;
        $variant_key = $product_id.'_'.$size_id.'_'.$color_id;
        $available_stock = isset($stock_map[$product_id]) ? (int)$stock_map[$product_id] : 0;
        if($variant_table_exists && $size_id > 0 && $color_id > 0 && isset($variant_stock_map[$variant_key])) {
            $available_stock = (int)$variant_stock_map[$variant_key];
        }

        if($available_stock < $requested_qty) {
            $allow_update = 0;
            $error_message .= 'Sản phẩm "'.$product_name.'" chỉ còn "'.$available_stock.'" sản phẩm trong kho.\n';
        } else {
            $_SESSION['cart_p_qty'][$session_key] = $requested_qty;
        }
    }

    $selected_keys = array();
    foreach($posted_selected_keys as $selected_key_raw) {
        $selected_key = (int)$selected_key_raw;
        if(in_array($selected_key, $cart_keys, true) && !in_array($selected_key, $selected_keys, true)) {
            $selected_keys[] = $selected_key;
        }
    }
    if(isset($_POST['cart_has_selection_field']) && $_POST['cart_has_selection_field'] === '1') {
        $_SESSION['checkout_selected_item_keys'] = $selected_keys;
    }

    $error_message .= '\nSố lượng các sản phẩm khác đã được cập nhật thành công!';

    if($allow_update == 1 && isset($_POST['go_home'])) {
        safe_redirect('index.php');
    }

    if($allow_update == 1 && isset($_POST['go_checkout'])) {
        if(count($selected_keys) === 0) {
            $allow_update = 0;
            $error_message = 'Vui lòng chọn ít nhất 1 sản phẩm để thanh toán.';
        }
    }

    if($allow_update == 1 && isset($_POST['go_checkout'])) {
        safe_redirect('checkout.php');
    }

    if($allow_update == 1 && $is_auto_update) {
        safe_redirect('cart.php');
    }
    ?>

    <?php if($allow_update == 0): ?>
        <script>alert('<?php echo $error_message; ?>');</script>
    <?php else: ?>
        <script>alert('Cập nhật số lượng sản phẩm thành công!');</script>
    <?php endif; ?>
    <?php

}
?>

<div class="page-banner" style="background-image: url(assets/uploads/<?php echo $banner_cart; ?>)">
    <div class="overlay"></div>
    <div class="page-banner-inner">
        <h1>Giỏ hàng</h1>
    </div>
</div>

<style>
    .cart-head {
        display: grid;
        grid-template-columns: minmax(320px, 1.5fr) 130px 130px 130px 90px;
        gap: 16px;
        align-items: center;
        border: 1px solid #ececec;
        background: #fff;
        padding: 14px 16px;
        margin-bottom: 12px;
        color: #666;
        font-size: 14px;
        font-weight: 500;
    }

    .cart-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .cart-item {
        display: grid;
        grid-template-columns: minmax(320px, 1.5fr) 130px 130px 130px 90px;
        gap: 16px;
        align-items: center;
        border: 1px solid #ececec;
        background: #fff;
        padding: 14px 16px;
    }

    .cart-item-product {
        display: grid;
        grid-template-columns: 30px 88px minmax(0, 1fr);
        gap: 12px;
        align-items: center;
    }

    .cart-item-check {
        text-align: center;
    }

    .cart-item-check input[type="checkbox"] {
        width: 16px;
        height: 16px;
        margin: 0;
    }

    .cart-item-image img {
        width: 88px;
        height: 88px;
        object-fit: cover;
        border: 1px solid #ececec;
    }

    .cart-item-name {
        margin: 0 0 4px;
        font-size: 16px;
        font-weight: 500;
        color: #222;
        line-height: 1.2;
    }

    .cart-item-meta {
        color: #777;
        font-size: 13px;
        line-height: 1.4;
    }

    .cart-item-price,
    .cart-item-total {
        color: #ee4d2d;
        font-size: 16px;
        font-weight: 500;
        text-align: center;
    }

    .cart-item-qty input[type="number"] {
        max-width: 88px;
        height: 36px;
        text-align: center;
        margin: 0 auto;
        border: 1px solid #d9d9d9;
        border-radius: 0;
    }

    .cart-item-actions {
        text-align: center;
    }

    .cart-item-actions .trash {
        color: #ee4d2d;
        font-size: 15px;
    }

    .cart-summary {
        margin-top: 12px;
        border: 1px solid #ececec;
        background: #fff;
        padding: 14px;
        display: flex;
        justify-content: flex-end;
        font-size: 18px;
        font-weight: 700;
        color: #ee4d2d;
    }

    @media (max-width: 991px) {
        .cart-head {
            display: none;
        }

        .cart-item {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .cart-item-product {
            grid-template-columns: 74px minmax(0, 1fr);
        }

        .cart-item-image img {
            width: 74px;
            height: 74px;
        }

        .cart-item-name {
            font-size: 15px;
        }

        .cart-item-price,
        .cart-item-total,
        .cart-item-actions,
        .cart-item-qty {
            text-align: left;
        }

        .cart-item-qty input[type="number"] {
            margin: 0;
        }

        .cart-summary {
            font-size: 16px;
        }
    }
</style>

<div class="page">
	<div class="container">
		<div class="row">
			<div class="col-md-12">

                <?php if(!isset($_SESSION['cart_p_id'])): ?>
                    <?php echo '<h2 class="text-center">Giỏ hàng đang trống!</h2></br>'; ?>
                    <?php echo '<h4 class="text-center">Hãy thêm sản phẩm vào giỏ hàng để xem tại đây.</h4>'; ?>
                <?php else: ?>
                <form action="" method="post" id="cartForm">
                    <?php $csrf->echoInputField(); ?>
                    <input type="hidden" name="auto_update" id="autoUpdateFlag" value="0">
                    <input type="hidden" name="cart_has_selection_field" value="1">
				<div class="cart">
                    <?php
                    $table_total_price = 0;

                    $i=0;
                    foreach($_SESSION['cart_p_id'] as $key => $value)
                    {
                        $i++;
                        $arr_cart_p_id[$i] = $value;
                    }

                    $i=0;
                    foreach($_SESSION['cart_size_id'] as $key => $value)
                    {
                        $i++;
                        $arr_cart_size_id[$i] = $value;
                    }

                    $i=0;
                    foreach($_SESSION['cart_size_name'] as $key => $value)
                    {
                        $i++;
                        $arr_cart_size_name[$i] = $value;
                    }

                    $i=0;
                    foreach($_SESSION['cart_color_id'] as $key => $value)
                    {
                        $i++;
                        $arr_cart_color_id[$i] = $value;
                    }

                    $i=0;
                    foreach($_SESSION['cart_color_name'] as $key => $value)
                    {
                        $i++;
                        $arr_cart_color_name[$i] = $value;
                    }

                    $i=0;
                    foreach($_SESSION['cart_p_qty'] as $key => $value)
                    {
                        $i++;
                        $arr_cart_p_qty[$i] = $value;
                    }

                    $i=0;
                    foreach($_SESSION['cart_p_current_price'] as $key => $value)
                    {
                        $i++;
                        $arr_cart_p_current_price[$i] = $value;
                    }

                    $i=0;
                    foreach($_SESSION['cart_p_name'] as $key => $value)
                    {
                        $i++;
                        $arr_cart_p_name[$i] = $value;
                    }

                    $i=0;
                    foreach($_SESSION['cart_p_featured_photo'] as $key => $value)
                    {
                        $i++;
                        $arr_cart_p_featured_photo[$i] = $value;
                    }

                    $selected_checkout_keys = array();
                    if(isset($_SESSION['checkout_selected_item_keys']) && is_array($_SESSION['checkout_selected_item_keys'])) {
                        foreach($_SESSION['checkout_selected_item_keys'] as $selected_key_raw) {
                            $selected_checkout_keys[] = (int)$selected_key_raw;
                        }
                    }
                    ?>

                    <div class="cart-head">
                        <div>Sản Phẩm</div>
                        <div class="text-center">Đơn Giá</div>
                        <div class="text-center">Số lượng</div>
                        <div class="text-center">Số Tiền</div>
                        <div class="text-center">Thao Tác</div>
                    </div>

                    <div class="cart-list">
                        <?php for($i=1;$i<=count($arr_cart_p_id);$i++): ?>
                            <?php
                            $session_item_key = array_keys($_SESSION['cart_p_id'])[$i-1];
                            $is_checked = count($selected_checkout_keys) === 0 || in_array((int)$session_item_key, $selected_checkout_keys, true);
                            $row_total_price = $arr_cart_p_current_price[$i]*$arr_cart_p_qty[$i];
                            $table_total_price = $table_total_price + $row_total_price;
                            ?>
                            <div class="cart-item">
                                <div class="cart-item-product">
                                    <div class="cart-item-check">
                                        <input type="checkbox" name="selected_item_keys[]" value="<?php echo (int)$session_item_key; ?>" <?php echo $is_checked ? 'checked' : ''; ?> aria-label="Chọn sản phẩm để thanh toán">
                                    </div>
                                    <div class="cart-item-image">
                                        <img src="assets/uploads/<?php echo $arr_cart_p_featured_photo[$i]; ?>" alt="">
                                    </div>
                                    <div class="cart-item-info">
                                        <p class="cart-item-name"><?php echo $arr_cart_p_name[$i]; ?></p>
                                        <div class="cart-item-meta">Phân loại: Size <strong><?php echo $arr_cart_size_name[$i]; ?></strong> | Màu <strong><?php echo $arr_cart_color_name[$i]; ?></strong></div>
                                    </div>
                                </div>
                                <div class="cart-item-price"><?php echo format_price_vnd($arr_cart_p_current_price[$i]); ?></div>
                                <div class="cart-item-qty text-center">
                                    <input type="hidden" name="item_key[]" value="<?php echo (int)$session_item_key; ?>">
                                    <input type="hidden" name="product_id[]" value="<?php echo $arr_cart_p_id[$i]; ?>">
                                    <input type="hidden" name="product_name[]" value="<?php echo $arr_cart_p_name[$i]; ?>">
                                    <input type="number" class="input-text qty text" step="1" min="1" max="" name="quantity[]" value="<?php echo $arr_cart_p_qty[$i]; ?>" title="Qty" size="4" pattern="[0-9]*" inputmode="numeric">
                                </div>
                                <div class="cart-item-total">
                                    <?php echo format_price_vnd($row_total_price); ?>
                                </div>
                                <div class="cart-item-actions">
                                    <a onclick="return confirmDelete();" href="cart-item-delete.php?id=<?php echo $arr_cart_p_id[$i]; ?>&size=<?php echo $arr_cart_size_id[$i]; ?>&color=<?php echo $arr_cart_color_id[$i]; ?>" class="trash">Xóa</a>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <div class="cart-summary">
                        Tổng cộng: <?php echo format_price_vnd($table_total_price); ?>
                    </div>
                </div>

                <div class="cart-buttons">
                    <ul>
                        <li><button type="submit" class="btn btn-primary" name="go_home">Tiếp tục mua sắm</button></li>
                        <li><button type="submit" class="btn btn-primary" name="go_checkout">Tiến hành thanh toán</button></li>
                    </ul>
                </div>
                </form>
                <?php endif; ?>

                

			</div>
		</div>
	</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var cartForm = document.getElementById('cartForm');
        var autoUpdateFlag = document.getElementById('autoUpdateFlag');
        if (!cartForm || !autoUpdateFlag) {
            return;
        }

        var navButtons = cartForm.querySelectorAll('button[name="go_home"], button[name="go_checkout"]');
        navButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                autoUpdateFlag.value = '0';
            });
        });

        var qtyInputs = cartForm.querySelectorAll('input[name="quantity[]"]');
        qtyInputs.forEach(function (input) {
            input.addEventListener('change', function () {
                autoUpdateFlag.value = '1';
                cartForm.submit();
            });
        });
    });
</script>


<?php require_once('footer.php'); ?>